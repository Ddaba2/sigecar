<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\Depotage;
use App\Models\Chargement;
use App\Models\Cession;
use App\Models\Cuve;
use App\Models\Produit;
use App\Models\User;
use App\Models\OperationCreux;
use App\Enums\OperationStatus;
use App\Services\CsvExporter;
use App\Services\MarketeurStockService;
use App\Services\ReportService;
use App\Services\VolumeCorrection;
use App\Http\Controllers\Concerns\FiltersOperations;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Contrôleur pour la gestion des fonctionnalités du gestionnaire
 * Gère les opérations de depotage, chargement, cessions, et génération de documents
 */
class GestionnaireController extends Controller
{
    use FiltersOperations;

    /**
     * Constructeur : applique les middlewares d'authentification et de rôle gestionnaire
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:gestionnaire');
    }

    /**
     * Affiche le dashboard du gestionnaire avec statistiques des opérations
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        return $this->stockSupervisionPage('Tableau de bord');
    }

    /**
     * Affiche la liste des opérations (dépotages et chargements)
     * @return \Illuminate\View\View
     */
    public function operations(Request $request)
    {
        $type      = $request->input('type', 'tous');
        $dateDebut = $request->input('date_debut');
        $dateFin   = $request->input('date_fin');

        $depotages   = collect();
        $chargements = collect();
        $cessions    = collect();

        if ($type === 'tous' || $type === 'depotage') {
            $q = Depotage::with(['produit', 'cuve'])->latest();
            $this->applyDateRangeFilter($q, 'date_operation', $dateDebut, $dateFin);
            $depotages = $q->paginate(10)->withQueryString();
        }

        if ($type === 'tous' || $type === 'chargement') {
            $q = Chargement::with(['produit', 'cuve'])->latest();
            $this->applyDateRangeFilter($q, 'date_operation', $dateDebut, $dateFin);
            $chargements = $q->paginate(10)->withQueryString();
        }

        if ($type === 'tous' || $type === 'cession') {
            $q = Cession::with(['cedant', 'beneficiaire', 'produit', 'cuve'])->latest();
            $this->applyDateRangeFilter($q, 'date_cession', $dateDebut, $dateFin);
            $cessions = $q->paginate(10)->withQueryString();
        }

        return view('gestionnaire.operations', compact(
            'depotages', 'chargements', 'cessions',
            'type', 'dateDebut', 'dateFin'
        ));
    }

    /**
     * Affiche le formulaire de création d'un depotage
     * @return \Illuminate\View\View
     */
    public function createDepotage()
    {
        $produits = Produit::where('status', 'active')->get();
        $cuves = Cuve::with('produit')->get();
        $marketeurs = User::marketeursActifs()->get();

        $recentDepotages = Depotage::with(['produit', 'cuve'])
            ->latest()
            ->take(12)
            ->get();

        return view('gestionnaire.depotage-create', compact('produits', 'cuves', 'marketeurs', 'recentDepotages'));
    }

    public function storeDepotage(Request $request)
    {
        $validated = $request->validate([
            'date_operation' => 'required|date',
            'produit_id' => 'required|exists:produits,id',
            'cuve_destination_id' => 'required|exists:cuves,id',
            'volume_brut' => 'required|integer|min:1',
            'temperature' => 'required|numeric|between:-20,60',
            'user_id' => 'required|exists:users,id',
            'provenance' => 'required|string',
            'numero_bon_chargement' => 'nullable|string',
            'plaque_imm' => 'required|string',
            'chauffeur_nom' => 'required|string',
            'chauffeur_permis' => 'required|string',
            'chauffeur_tel' => 'nullable|string',
            'declaration_douane' => 'nullable|string',
            'bureau_douane' => 'nullable|string',
            'creux' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $operator = User::where('role', 'marketeur')->findOrFail($validated['user_id']);
            $volumeCorrige = $this->calculerVolumeCorrige($validated['volume_brut'], $validated['temperature']);

            $depotage = Depotage::create([
                'numero_depotage' => 'DEP-' . date('YmdHis'),
                'date_operation' => $validated['date_operation'],
                'produit_id' => $validated['produit_id'],
                'cuve_destination_id' => $validated['cuve_destination_id'],
                'volume_brut' => $validated['volume_brut'],
                'temperature' => $validated['temperature'],
                'volume_corrige' => $volumeCorrige,
                'fournisseur' => $operator->operatorName(),
                'user_id' => $operator->id,
                'provenance' => $validated['provenance'],
                'numero_bon_chargement' => $validated['numero_bon_chargement'] ?? null,
                'plaque_imm' => $validated['plaque_imm'],
                'chauffeur_nom' => $validated['chauffeur_nom'],
                'chauffeur_permis' => $validated['chauffeur_permis'],
                'chauffeur_tel' => $validated['chauffeur_tel'] ?? null,
                'declaration_douane' => $validated['declaration_douane'] ?? null,
                'bureau_douane' => $validated['bureau_douane'] ?? null,
                'status' => OperationStatus::SousDouane->value,
                'created_by' => Auth::id(),
            ]);

            // Enregistrer les creux
            if (!empty($validated['creux'])) {
                foreach ($validated['creux'] as $creux) {
                    if (empty($creux['capacite'])) {
                        continue;
                    }
                    OperationCreux::create([
                        'depotage_id' => $depotage->id,
                        'numero_creux' => (int) $creux['numero'],
                        'produit_id' => !empty($creux['produit_id']) ? $creux['produit_id'] : $validated['produit_id'],
                        'capacite' => (int) $creux['capacite'],
                        'volume' => isset($creux['volume']) ? (int) $creux['volume'] : 0,
                    ]);
                }
            }

            $cuve = Cuve::find($validated['cuve_destination_id']);
            $cuve->niveau_actuel += $volumeCorrige;
            if ($depotage->status === OperationStatus::SousDouane->value) {
                $cuve->type_douane = OperationStatus::SousDouane->value;
            }
            if (! $cuve->user_id) {
                $cuve->user_id = $operator->id;
            }
            $cuve->save();

            app(MarketeurStockService::class)->creditFromDepotage($depotage);

            DB::commit();

            $pdfOk = $this->generateDepotagePDF($depotage);

            return view('gestionnaire.document-ready', [
                'operationType' => 'Dépotage',
                'reference' => $depotage->numero_depotage,
                'documentUrl' => route('gestionnaire.document.download', ['type' => 'depotage', 'id' => $depotage->id]),
                'message' => 'Dépotage enregistré avec succès.',
                'pdfWarning' => $pdfOk ? null : 'Le document PDF n\'a pas pu être généré. Contactez l\'administrateur.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Passe un dépotage de « sous douane » à « acquitté » et met à jour la cuve si plus aucun dépotage en attente.
     */
    public function acquitterDepotage(Depotage $depotage)
    {
        if ($depotage->status !== OperationStatus::SousDouane->value) {
            return back()->with('error', 'Ce dépotage n\'est pas en attente de douane.');
        }

        DB::transaction(function () use ($depotage) {
            $depotage->update(['status' => OperationStatus::Acquitte->value]);

            $cuveId = $depotage->cuve_destination_id;
            $encoreSousDouane = Depotage::query()
                ->where('cuve_destination_id', $cuveId)
                ->where('status', OperationStatus::SousDouane->value)
                ->exists();

            if (! $encoreSousDouane) {
                Cuve::whereKey($cuveId)->update(['type_douane' => OperationStatus::Acquitte->value]);
            }
        });

        return back()->with('success', 'Dépotage acquitté. Statut douanier mis à jour.');
    }

    /**
     * Liste complète des cuves (tout le stock).
     */
    public function stocksTous()
    {
        $cuves = Cuve::with('produit')->orderBy('nom')->orderBy('code')->get();
        extract($this->computeStockDouaneKpis());

        return view('gestionnaire.stocks-tous', compact(
            'cuves',
            'totalCapacite',
            'totalStock',
            'sousDouaneVol',
            'acquitteVol'
        ));
    }

    public function createChargement()
    {
        $produits = Produit::where('status', 'active')->get();
        $cuves = Cuve::with('produit')->get();
        $marketeurs = User::marketeursActifs()->get();

        $recentChargements = Chargement::with(['produit', 'cuve'])
            ->latest()
            ->take(12)
            ->get();

        return view('gestionnaire.chargement-create', compact('produits', 'cuves', 'marketeurs', 'recentChargements'));
    }

    public function storeChargement(Request $request)
    {
        $validated = $request->validate([
            'date_operation' => 'required|date',
            'produit_id' => 'required|exists:produits,id',
            'cuve_source_id' => 'required|exists:cuves,id',
            'volume_brut' => 'required|integer|min:1',
            'temperature' => 'required|numeric|between:-20,60',
            'user_id' => 'required|exists:users,id',
            'client_code' => 'nullable|string',
            'plaque_imm' => 'required|string',
            'chauffeur_nom' => 'required|string',
            'chauffeur_permis' => 'required|string',
            'capacite_camion' => 'required|integer',
        ]);

        DB::beginTransaction();
        try {
            $cuve = Cuve::find($validated['cuve_source_id']);

            if ($cuve->niveau_actuel < $validated['volume_brut']) {
                return back()->with('error', 'Stock insuffisant dans la cuve');
            }

            $operator = User::where('role', 'marketeur')->findOrFail($validated['user_id']);
            $volumeCorrige = $this->calculerVolumeCorrige($validated['volume_brut'], $validated['temperature']);

            $chargement = Chargement::create([
                'numero_chargement' => 'CHG-' . date('YmdHis'),
                'date_operation' => $validated['date_operation'],
                'produit_id' => $validated['produit_id'],
                'cuve_source_id' => $validated['cuve_source_id'],
                'volume_brut' => $validated['volume_brut'],
                'temperature' => $validated['temperature'],
                'volume_corrige' => $volumeCorrige,
                'client_nom' => $operator->operatorName(),
                'user_id' => $operator->id,
                'client_code' => $validated['client_code'] ?? null,
                'plaque_imm' => $validated['plaque_imm'],
                'chauffeur_nom' => $validated['chauffeur_nom'],
                'chauffeur_permis' => $validated['chauffeur_permis'],
                'capacite_camion' => $validated['capacite_camion'],
                'status' => OperationStatus::Acquitte->value,
                'created_by' => Auth::id(),
            ]);

            // Mettre à jour le stock cuve
            $cuve->niveau_actuel -= $volumeCorrige;
            $cuve->save();

            try {
                app(MarketeurStockService::class)->debitFromChargement($chargement);
            } catch (\RuntimeException $e) {
                DB::rollBack();
                return back()->with('error', $e->getMessage());
            }

            DB::commit();

            $pdfOk = $this->generateChargementPDF($chargement);

            return view('gestionnaire.document-ready', [
                'operationType' => 'Chargement',
                'reference' => $chargement->numero_chargement,
                'documentUrl' => route('gestionnaire.document.download', ['type' => 'chargement', 'id' => $chargement->id]),
                'message' => 'Chargement enregistré avec succès.',
                'pdfWarning' => $pdfOk ? null : 'Le document PDF n\'a pas pu être généré. Contactez l\'administrateur.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function downloadDocument(string $type, int $id)
    {
        $modelClass = match ($type) {
            'depotage' => Depotage::class,
            'chargement' => Chargement::class,
            'cession' => Cession::class,
            default => null,
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

    public function createCession()
    {
        $produits = Produit::where('status', 'active')->get();
        $cuves = Cuve::with('produit')->get();
        $marketeurs = User::marketeursActifs()->get();
        $recentCessions = Cession::with(['cedant', 'beneficiaire', 'produit', 'cuve'])
            ->latest()
            ->take(12)
            ->get();

        return view('gestionnaire.cession-create', compact('produits', 'cuves', 'marketeurs', 'recentCessions'));
    }

    public function storeCession(Request $request)
    {
        $validated = $request->validate([
            'date_cession' => 'required|date',
            'cedant_id' => 'required|exists:users,id',
            'beneficiaire_id' => 'required|exists:users,id|different:cedant_id',
            'produit_id' => 'required|exists:produits,id',
            'cuve_id' => 'required|exists:cuves,id',
            'volume' => 'required|integer|min:1',
            'temperature' => 'nullable|numeric',
            'prix_unitaire' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $volumeCorrige = $this->calculerVolumeCorrige($validated['volume'], $validated['temperature'] ?? 15);
            $montantTotal = ($validated['prix_unitaire'] ?? 0) * $validated['volume'];

            $stockService = app(MarketeurStockService::class);
            $stockService->transfer(
                $validated['cedant_id'],
                $validated['beneficiaire_id'],
                $validated['produit_id'],
                $volumeCorrige
            );

            $cession = Cession::create([
                'numero_cession' => 'CES-' . date('YmdHis'),
                'date_cession' => $validated['date_cession'],
                'cedant_id' => $validated['cedant_id'],
                'beneficiaire_id' => $validated['beneficiaire_id'],
                'produit_id' => $validated['produit_id'],
                'cuve_id' => $validated['cuve_id'],
                'volume' => $validated['volume'],
                'volume_corrige' => $volumeCorrige,
                'temperature' => $validated['temperature'] ?? 15,
                'prix_unitaire' => $validated['prix_unitaire'] ?? 0,
                'montant_total' => $montantTotal,
                'status' => OperationStatus::Confirmed->value,
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            $pdfOk = $this->generateCessionPDF($cession);

            $redirect = redirect()->route('gestionnaire.operations')->with('success', 'Cession enregistrée avec succès.');
            if (! $pdfOk) {
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

    public function stocks()
    {
        return $this->stockSupervisionPage('Stock & Douane');
    }

    /**
     * @return \Illuminate\View\View
     */
    protected function computeStockDouaneKpis(): array
    {
        $totalCapacite = (int) Cuve::sum('capacite_totale');
        $totalStock = (int) Cuve::sum('niveau_actuel');
        $sousDouaneVol = (int) Depotage::where('status', OperationStatus::SousDouane->value)->sum('volume_corrige');
        $acquitteVol = max(0, $totalStock - $sousDouaneVol);

        return compact('totalCapacite', 'totalStock', 'sousDouaneVol', 'acquitteVol');
    }

    protected function stockSupervisionPage(string $pageTitle)
    {
        $cuves = Cuve::with('produit')->get();
        extract($this->computeStockDouaneKpis());
        $acquitte = $acquitteVol;
        $alertes = Cuve::whereRaw('niveau_actuel <= seuil_alerte_bas OR niveau_actuel >= seuil_alerte_haut')->get();

        $recentDepotages = Depotage::with(['produit', 'cuve'])->latest()->take(8)->get();
        $operatorStocks = app(MarketeurStockService::class)->allGroupedByOperator();

        return view('gestionnaire.stocks', compact(
            'cuves',
            'totalCapacite',
            'totalStock',
            'sousDouaneVol',
            'acquitte',
            'alertes',
            'recentDepotages',
            'operatorStocks',
            'pageTitle'
        ));
    }

    public function settings()
    {
        return view('gestionnaire.settings');
    }

    public function rapports(Request $request)
    {
        $day = $request->filled('date')
            ? Carbon::parse($request->string('date'))->startOfDay()
            : now()->startOfDay();

        $depotagesJour = Depotage::with(['produit', 'cuve'])
            ->whereDate('date_operation', $day)
            ->orderBy('date_operation')
            ->get();

        $chargementsJour = Chargement::with(['produit', 'cuve'])
            ->whereDate('date_operation', $day)
            ->orderBy('date_operation')
            ->get();

        $cessionsJour = Cession::with(['produit', 'cedant', 'beneficiaire'])
            ->whereDate('date_cession', $day)
            ->orderBy('date_cession')
            ->get();

        $cuves = Cuve::with('produit')->get();

        $volDepotJour = (int) Depotage::whereDate('date_operation', $day)->sum('volume_brut');
        $volChargeJour = (int) Chargement::whereDate('date_operation', $day)->sum('volume_brut');
        $cessionsCount = (int) Cession::whereDate('date_cession', $day)->count();
        $volCessionJour = (int) Cession::whereDate('date_cession', $day)->sum('volume');

        $yesterday = $day->copy()->subDay();
        $volDepotHier = (int) Depotage::whereDate('date_operation', $yesterday)->sum('volume_brut');
        $volChargeHier = (int) Chargement::whereDate('date_operation', $yesterday)->sum('volume_brut');
        $cessionsCountHier = (int) Cession::whereDate('date_cession', $yesterday)->count();

        $pctDepotVsHier = $volDepotHier > 0
            ? (int) round((($volDepotJour - $volDepotHier) / $volDepotHier) * 100)
            : null;
        $pctChargeVsHier = $volChargeHier > 0
            ? (int) round((($volChargeJour - $volChargeHier) / $volChargeHier) * 100)
            : null;
        $cessionsDelta = $cessionsCount - $cessionsCountHier;

        $cessionsPending = (int) Cession::where('status', 'pending')->count();

        $famillesRapport = app(ReportService::class)->buildFamillesRapport($day, $cuves);

        return view('gestionnaire.rapports', compact(
            'depotagesJour',
            'chargementsJour',
            'cessionsJour',
            'cuves',
            'volDepotJour',
            'volChargeJour',
            'cessionsCount',
            'volCessionJour',
            'day',
            'volDepotHier',
            'volChargeHier',
            'pctDepotVsHier',
            'pctChargeVsHier',
            'cessionsDelta',
            'cessionsPending',
            'famillesRapport'
        ));
    }

    public function exportRapportCsv(Request $request): StreamedResponse
    {
        $day = $request->filled('date')
            ? Carbon::parse($request->string('date'))->startOfDay()
            : now()->startOfDay();

        $rows = (function () use ($day) {
            foreach (Depotage::with('produit')->whereDate('date_operation', $day)->orderBy('date_operation')->cursor() as $d) {
                yield ['Dépotage', $d->date_operation->format('Y-m-d H:i'), $d->numero_depotage, $d->produit->nom ?? '', $d->volume_brut, $d->fournisseur];
            }
            foreach (Chargement::with('produit')->whereDate('date_operation', $day)->orderBy('date_operation')->cursor() as $c) {
                yield ['Chargement', $c->date_operation->format('Y-m-d H:i'), $c->numero_chargement, $c->produit->nom ?? '', $c->volume_brut, $c->client_nom];
            }
            foreach (Cession::with('produit')->whereDate('date_cession', $day)->orderBy('date_cession')->cursor() as $ces) {
                yield ['Cession', $ces->date_cession->format('Y-m-d H:i'), $ces->numero_cession, $ces->produit->nom ?? '', $ces->volume, ''];
            }
        })();

        return CsvExporter::stream(
            'sigecar-rapport-' . $day->format('Y-m-d') . '.csv',
            ['Type', 'Date', 'Référence', 'Produit', 'Volume (L)', 'Détail'],
            $rows
        );
    }

    public function exportRapportPdf(Request $request)
    {
        $day = $request->filled('date')
            ? Carbon::parse($request->string('date'))->startOfDay()
            : now()->startOfDay();

        $depotagesJour = Depotage::with(['produit', 'cuve'])->whereDate('date_operation', $day)->orderBy('date_operation')->get();
        $chargementsJour = Chargement::with(['produit', 'cuve'])->whereDate('date_operation', $day)->orderBy('date_operation')->get();
        $cessionsJour = Cession::with(['produit', 'cedant', 'beneficiaire'])->whereDate('date_cession', $day)->orderBy('date_cession')->get();
        $cuves = Cuve::with('produit')->get();
        $famillesRapport = app(ReportService::class)->buildFamillesRapport($day, $cuves);

        $pdf = Pdf::loadView('pdf.rapport-journalier', compact(
            'day',
            'depotagesJour',
            'chargementsJour',
            'cessionsJour',
            'famillesRapport'
        ));

        return $pdf->download('sigecar-rapport-' . $day->format('Y-m-d') . '.pdf');
    }

    private function calculerVolumeCorrige($volumeBrut, $temperature): int
    {
        return VolumeCorrection::corriger($volumeBrut, $temperature);
    }

    // Point 1 — stock logique de l'opérateur par produit (utilisé par le formulaire cession)
    public function operatorStock(int $userId, int $produitId): \Illuminate\Http\JsonResponse
    {
        $qty = app(MarketeurStockService::class)->getQuantite($userId, $produitId);
        return response()->json(['quantite' => $qty]);
    }

    // Point 9 — PDF avec logging d'erreur
    private function generateDepotagePDF($depotage): bool
    {
        try {
            $depotage->load(['produit', 'cuve', 'user', 'operationsCreux.produit']);
            $pdf = PDF::loadView('pdf.bon-depotage', compact('depotage'));
            $filename = 'BD-' . date('Ymd') . '-' . $depotage->id . '.pdf';
            $path = storage_path('app/public/documents/' . $filename);
            File::ensureDirectoryExists(dirname($path));
            $pdf->save($path);
            $depotage->update(['document_pdf' => 'documents/' . $filename]);
            return true;
        } catch (\Exception $e) {
            Log::error('Échec génération PDF dépotage #' . $depotage->id . ' : ' . $e->getMessage());
            return false;
        }
    }

    private function generateChargementPDF($chargement): bool
    {
        try {
            $chargement->load(['produit', 'cuve']);
            $pdf = PDF::loadView('pdf.bon-chargement', compact('chargement'));
            $filename = 'BC-' . date('Ymd') . '-' . $chargement->id . '.pdf';
            $path = storage_path('app/public/documents/' . $filename);
            File::ensureDirectoryExists(dirname($path));
            $pdf->save($path);
            $chargement->update(['document_pdf' => 'documents/' . $filename]);
            return true;
        } catch (\Exception $e) {
            Log::error('Échec génération PDF chargement #' . $chargement->id . ' : ' . $e->getMessage());
            return false;
        }
    }

    private function generateCessionPDF($cession): bool
    {
        try {
            $cession->load(['produit', 'cuve', 'cedant', 'beneficiaire']);
            $pdf = PDF::loadView('pdf.bon-cession', compact('cession'));
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
}
