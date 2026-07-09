<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cession;
use App\Models\Depotage;
use App\Models\Chargement;
use App\Models\Cuve;
use App\Models\User;
use App\Models\Produit;
use App\Enums\OperationStatus;
use App\Services\CsvExporter;
use App\Services\MarketeurStockService;
use App\Services\VolumeCorrection;
use App\Http\Controllers\Concerns\FiltersOperations;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Contrôleur pour la gestion des fonctionnalités du marketeur
 */
class MarketeurController extends Controller
{
    use FiltersOperations;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:marketeur');
    }

    public function dashboard()
    {
        $user = Auth::user();
        $stockService = app(MarketeurStockService::class);

        $marketeurStocks = $stockService->forUser($user->id);
        $stockByProduct = $marketeurStocks->mapWithKeys(fn ($s) => [$s->produit->nom ?? '—' => $s->quantite]);
        $totalLitresDisponibles = (int) $marketeurStocks->sum('quantite');
        $maxStockProduct = $stockByProduct->max() ?: 1;

        $totalDepotages = (int) Depotage::where('user_id', $user->id)->sum('volume_corrige');
        $totalChargements = (int) Chargement::where('user_id', $user->id)->sum('volume_corrige');

        $recentDepotages = Depotage::where('user_id', $user->id)->with('produit')->latest()->take(8)->get();
        $recentChargements = Chargement::where('user_id', $user->id)->with('produit')->latest()->take(8)->get();
        $recentCessions = Cession::where(function ($q) use ($user) {
            $q->where('cedant_id', $user->id)->orWhere('beneficiaire_id', $user->id);
        })->with('produit')->latest()->take(8)->get();

        $recentOperations = $recentDepotages->map(fn ($d) => [
            'date' => $d->date_operation,
            'type' => 'Dépotage',
            'type_icon' => 'fa-download',
            'produit' => $d->produit->nom ?? '—',
            'volume' => $d->volume_brut,
            'status' => $d->status,
        ])->concat($recentChargements->map(fn ($c) => [
            'date' => $c->date_operation,
            'type' => 'Chargement',
            'type_icon' => 'fa-upload',
            'produit' => $c->produit->nom ?? '—',
            'volume' => $c->volume_brut,
            'status' => $c->status,
        ]))->concat($recentCessions->map(fn ($ces) => [
            'date' => $ces->date_cession,
            'type' => $ces->cedant_id === $user->id ? 'Cession émise' : 'Cession reçue',
            'type_icon' => $ces->cedant_id === $user->id ? 'fa-share-from-square' : 'fa-inbox',
            'produit' => $ces->produit->nom ?? '—',
            'volume' => $ces->volume,
            'status' => $ces->status,
        ]))->sortByDesc('date')->take(6)->values();

        return view('marketeur.dashboard', compact(
            'totalLitresDisponibles',
            'totalDepotages',
            'totalChargements',
            'recentOperations',
            'stockByProduct',
            'maxStockProduct',
            'marketeurStocks'
        ));
    }

    public function operations(Request $request)
    {
        $user = Auth::user();

        $depotagesQ = Depotage::where('user_id', $user->id)->with(['produit', 'cuve']);
        $chargementsQ = Chargement::where('user_id', $user->id)->with(['produit', 'cuve']);
        $cessionsQ = Cession::where(function ($q) use ($user) {
            $q->where('cedant_id', $user->id)->orWhere('beneficiaire_id', $user->id);
        })->with(['produit', 'cuve']);

        $this->applyDateFilter($depotagesQ, 'date_operation', $request->string('periode')->toString());
        $this->applyDateFilter($chargementsQ, 'date_operation', $request->string('periode')->toString());
        $this->applyDateFilter($cessionsQ, 'date_cession', $request->string('periode')->toString());

        if ($request->filled('produit_id')) {
            $depotagesQ->where('produit_id', $request->produit_id);
            $chargementsQ->where('produit_id', $request->produit_id);
            $cessionsQ->where('produit_id', $request->produit_id);
        }

        $depotages = $depotagesQ->latest()->get();
        $chargements = $chargementsQ->latest()->get();
        $cessions = $cessionsQ->latest()->get();

        $operations = $depotages->map(fn ($d) => [
            'date' => $d->date_operation,
            'type' => 'Dépotage',
            'type_icon' => 'fa-download',
            'produit' => $d->produit->nom ?? '—',
            'volume_brut' => $d->volume_brut,
            'volume_corrige' => $d->volume_corrige,
            'status' => $d->status,
            'has_pdf' => (bool) $d->document_pdf,
            'doc_type' => 'depotage',
            'doc_id' => $d->id,
        ])->concat($chargements->map(fn ($c) => [
            'date' => $c->date_operation,
            'type' => 'Chargement',
            'type_icon' => 'fa-upload',
            'produit' => $c->produit->nom ?? '—',
            'volume_brut' => $c->volume_brut,
            'volume_corrige' => $c->volume_corrige,
            'status' => $c->status,
            'has_pdf' => (bool) $c->document_pdf,
            'doc_type' => 'chargement',
            'doc_id' => $c->id,
        ]))->concat($cessions->map(fn ($ces) => [
            'date' => $ces->date_cession,
            'type' => $ces->cedant_id === $user->id ? 'Cession émise' : 'Cession reçue',
            'type_icon' => $ces->cedant_id === $user->id ? 'fa-share-from-square' : 'fa-inbox',
            'produit' => $ces->produit->nom ?? '—',
            'volume_brut' => $ces->volume,
            'volume_corrige' => $ces->volume_corrige,
            'status' => $ces->status,
            'has_pdf' => (bool) $ces->document_pdf,
            'doc_type' => 'cession',
            'doc_id' => $ces->id,
        ]))->sortByDesc('date')->values();

        if ($request->filled('type') && $request->type !== 'tous') {
            $typeLabels = match ($request->type) {
                'depotage'   => ['Dépotage'],
                'chargement' => ['Chargement'],
                'cession'    => ['Cession émise', 'Cession reçue'],
                default      => [],
            };
            if ($typeLabels) {
                $operations = $operations->filter(fn ($o) => in_array($o['type'], $typeLabels))->values();
            }
        }

        $totalDepotagesMois = (int) Depotage::where('user_id', $user->id)
            ->whereMonth('date_operation', now()->month)
            ->whereYear('date_operation', now()->year)
            ->sum('volume_corrige');

        $totalChargementsMois = (int) Chargement::where('user_id', $user->id)
            ->whereMonth('date_operation', now()->month)
            ->whereYear('date_operation', now()->year)
            ->sum('volume_corrige');

        $sousDouaneActuel = (int) Depotage::where('user_id', $user->id)
            ->where('status', OperationStatus::SousDouane->value)
            ->sum('volume_corrige');

        // Stock total opérateur pour le calcul du pourcentage sous douane
        $stockTotal = (int) app(MarketeurStockService::class)->forUser($user->id)->sum('quantite');

        $produits = Produit::where('status', 'active')->get();

        return view('marketeur.operations', compact(
            'operations',
            'totalDepotagesMois',
            'totalChargementsMois',
            'sousDouaneActuel',
            'stockTotal',
            'produits'
        ));
    }

    public function cessions(Request $request)
    {
        $user = Auth::user();

        // Cessions émises
        $query = Cession::where('cedant_id', $user->id)
            ->with(['beneficiaire', 'produit']);

        if ($request->filled('produit_id')) {
            $query->where('produit_id', $request->produit_id);
        }

        $this->applyDateFilter($query, 'date_cession', $request->string('periode')->toString());

        $cessions = $query->latest()->paginate(15)->withQueryString();

        // Cessions reçues (point 4)
        $queryRecues = Cession::where('beneficiaire_id', $user->id)
            ->with(['cedant', 'produit']);

        if ($request->filled('produit_id')) {
            $queryRecues->where('produit_id', $request->produit_id);
        }

        $this->applyDateFilter($queryRecues, 'date_cession', $request->string('periode')->toString());

        $cessionsRecues = $queryRecues->latest()->paginate(15, ['*'], 'page_recues')->withQueryString();

        $totalTransfereMois = (int) Cession::where('cedant_id', $user->id)
            ->whereMonth('date_cession', now()->month)
            ->whereYear('date_cession', now()->year)
            ->sum('volume');

        $totalRecuMois = (int) Cession::where('beneficiaire_id', $user->id)
            ->whereMonth('date_cession', now()->month)
            ->whereYear('date_cession', now()->year)
            ->sum('volume');

        $produits = Produit::where('status', 'active')->get();

        return view('marketeur.cessions', compact(
            'cessions',
            'cessionsRecues',
            'totalTransfereMois',
            'totalRecuMois',
            'produits'
        ));
    }

    public function showCession($id)
    {
        $user = Auth::user();
        $cession = Cession::with(['cedant', 'beneficiaire', 'produit', 'cuve'])->findOrFail($id);

        if ($cession->cedant_id != $user->id && $cession->beneficiaire_id != $user->id) {
            abort(403);
        }

        return view('marketeur.cession-detail', compact('cession'));
    }

    public function createCession()
    {
        $produits = Produit::where('status', 'active')->get();
        $cuves = Cuve::with('produit')->get();
        $marketeurs = User::marketeursActifs()->get();

        return view('marketeur.cession-create', compact('produits', 'cuves', 'marketeurs'));
    }

    public function storeCession(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'date_cession' => 'required|date',
            'beneficiaire_id' => 'required|exists:users,id',
            'produit_id' => 'required|exists:produits,id',
            'cuve_id' => 'required|exists:cuves,id',
            'volume' => 'required|integer|min:1',
            'temperature' => 'nullable|numeric',
        ]);

        if ($validated['beneficiaire_id'] == $user->id) {
            return back()->with('error', 'Le bénéficiaire doit être différent de votre société.');
        }

        DB::beginTransaction();
        try {
            $temp = $validated['temperature'] ?? 15;
            $volumeCorrige = VolumeCorrection::corriger($validated['volume'], $temp);

            app(MarketeurStockService::class)->transfer(
                $user->id,
                $validated['beneficiaire_id'],
                $validated['produit_id'],
                $volumeCorrige
            );

            $cession = Cession::create([
                'numero_cession' => 'CES-' . date('YmdHis'),
                'date_cession' => $validated['date_cession'],
                'cedant_id' => $user->id,
                'beneficiaire_id' => $validated['beneficiaire_id'],
                'produit_id' => $validated['produit_id'],
                'cuve_id' => $validated['cuve_id'],
                'volume' => $validated['volume'],
                'volume_corrige' => $volumeCorrige,
                'temperature' => $temp,
                'prix_unitaire' => 0,
                'montant_total' => 0,
                'status' => OperationStatus::Confirmed->value,
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            $pdfOk = $this->generateCessionPDF($cession);

            $redirect = redirect()->route('marketeur.cessions')->with('success', 'Cession enregistrée avec succès.');

            if (!$pdfOk) {
                $redirect = $redirect->with('warning', 'Le document PDF n\'a pas pu être généré. Vous pouvez le régénérer depuis la liste.');
            }

            return $redirect;

        } catch (\RuntimeException $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function downloadDocument(string $type, int $id)
    {
        $modelClass = match ($type) {
            'depotage'   => Depotage::class,
            'chargement' => Chargement::class,
            'cession'    => Cession::class,
            default      => null,
        };

        if (! $modelClass) {
            abort(404);
        }

        $record = $modelClass::findOrFail($id);
        $pdfPath = storage_path('app/public/' . ($record->document_pdf ?? ''));

        if (! File::exists($pdfPath)) {
            abort(404);
        }

        return response()->download($pdfPath, basename($pdfPath));
    }

    // Point 12 — régénération du PDF d'une cession
    public function regenerateDocument(string $type, int $id)
    {
        if ($type !== 'cession') {
            abort(403, 'La régénération n\'est disponible que pour les cessions.');
        }

        $cession = Cession::findOrFail($id);
        $user = Auth::user();

        if ($cession->cedant_id != $user->id && $cession->beneficiaire_id != $user->id) {
            abort(403);
        }

        $ok = $this->generateCessionPDF($cession);

        return back()->with(
            $ok ? 'success' : 'error',
            $ok ? 'Document PDF régénéré avec succès.' : 'La génération du PDF a échoué. Vérifiez les logs.'
        );
    }

    public function settings()
    {
        return view('marketeur.settings');
    }

    public function exportCessionsCsv(Request $request): StreamedResponse
    {
        $user = Auth::user();

        $query = Cession::where('cedant_id', $user->id)->with(['produit', 'beneficiaire']);
        $this->applyDateFilter($query, 'date_cession', $request->string('periode')->toString());
        if ($request->filled('produit_id')) {
            $query->where('produit_id', $request->produit_id);
        }

        $rows = (static function () use ($query) {
            foreach ($query->cursor() as $c) {
                yield [
                    $c->date_cession->format('Y-m-d H:i'),
                    $c->numero_cession,
                    $c->produit->nom ?? '—',
                    $c->volume,
                    $c->volume_corrige,
                    $c->beneficiaire ? $c->beneficiaire->operatorName() : '—',
                    $c->status,
                ];
            }
        })();

        return CsvExporter::stream(
            'cessions-' . now()->format('Y-m-d') . '.csv',
            ['Date', 'Référence', 'Produit', 'Volume (L)', 'Vol. Corrigé (L)', 'Bénéficiaire', 'Statut'],
            $rows
        );
    }

    public function exportOperationsCsv(Request $request): StreamedResponse
    {
        $user = Auth::user();
        $periode   = $request->string('periode')->toString();
        $produitId = $request->produit_id;
        $type      = $request->input('type', 'tous');
        $userId    = $user->id;

        $depotagesQ  = Depotage::where('user_id', $userId)->with('produit');
        $chargementsQ = Chargement::where('user_id', $userId)->with('produit');
        $cessionsQ   = Cession::where(function ($q) use ($userId) {
            $q->where('cedant_id', $userId)->orWhere('beneficiaire_id', $userId);
        })->with(['produit', 'cedant', 'beneficiaire']);

        $this->applyDateFilter($depotagesQ, 'date_operation', $periode);
        $this->applyDateFilter($chargementsQ, 'date_operation', $periode);
        $this->applyDateFilter($cessionsQ, 'date_cession', $periode);

        if ($produitId) {
            $depotagesQ->where('produit_id', $produitId);
            $chargementsQ->where('produit_id', $produitId);
            $cessionsQ->where('produit_id', $produitId);
        }

        $filename = 'operations-' . now()->format('Y-m-d') . '.csv';

        $rows = (static function () use ($userId, $depotagesQ, $chargementsQ, $cessionsQ, $type) {
            if (in_array($type, ['tous', 'depotage'])) {
                foreach ($depotagesQ->cursor() as $d) {
                    yield [$d->date_operation->format('Y-m-d H:i'), 'Dépotage', $d->numero_depotage, $d->produit->nom ?? '—', $d->volume_brut, $d->volume_corrige, $d->status];
                }
            }
            if (in_array($type, ['tous', 'chargement'])) {
                foreach ($chargementsQ->cursor() as $c) {
                    yield [$c->date_operation->format('Y-m-d H:i'), 'Chargement', $c->numero_chargement, $c->produit->nom ?? '—', $c->volume_brut, $c->volume_corrige, $c->status];
                }
            }
            if (in_array($type, ['tous', 'cession'])) {
                foreach ($cessionsQ->cursor() as $ces) {
                    yield [$ces->date_cession->format('Y-m-d H:i'), $ces->cedant_id === $userId ? 'Cession émise' : 'Cession reçue', $ces->numero_cession, $ces->produit->nom ?? '—', $ces->volume, $ces->volume_corrige, $ces->status];
                }
            }
        })();

        return CsvExporter::stream(
            $filename,
            ['Date', 'Type', 'Référence', 'Produit', 'Volume Brut (L)', 'Vol. Corrigé (L)', 'Statut'],
            $rows
        );
    }

    // Point 9 — log l'erreur et retourne bool pour informer l'appelant
    private function generateCessionPDF($cession): bool
    {
        try {
            $cession->load(['produit', 'cuve', 'cedant', 'beneficiaire']);
            $pdf = Pdf::loadView('pdf.bon-cession', compact('cession'));
            $filename = 'CESSION-' . date('Ymd') . '-' . $cession->id . '.pdf';
            $path = storage_path('app/public/documents/' . $filename);
            File::ensureDirectoryExists(dirname($path));
            $pdf->save($path);
            $cession->update(['document_pdf' => 'documents/' . $filename]);
            return true;
        } catch (\Exception $e) {
            Log::error('Échec génération PDF cession #' . $cession->id . ' : ' . $e->getMessage());
            return false;
        }
    }

    public function cuveStock(int $id)
    {
        $cuve = Cuve::findOrFail($id);
        $user = Auth::user();
        $operatorQty = app(MarketeurStockService::class)->getQuantite($user->id, $cuve->produit_id);

        return response()->json([
            'niveau_actuel' => $operatorQty,
            'capacite_totale' => $cuve->capacite_totale,
            'operator_stock' => $operatorQty,
        ]);
    }

    public function stockProduit(int $produitId)
    {
        $user = Auth::user();

        return response()->json([
            'quantite' => app(MarketeurStockService::class)->getQuantite($user->id, $produitId),
        ]);
    }
}
