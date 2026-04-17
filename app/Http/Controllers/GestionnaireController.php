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
use App\Models\Marketeur;
use App\Models\OperationCreux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Contrôleur pour la gestion des fonctionnalités du gestionnaire
 * Gère les opérations de depotage, chargement, cessions, et génération de documents
 */
class GestionnaireController extends Controller
{
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
    public function operations()
    {
        $depotages = Depotage::with(['produit', 'cuve'])->latest()->paginate(10);
        $chargements = Chargement::with(['produit', 'cuve'])->latest()->paginate(10);

        return view('gestionnaire.operations', compact('depotages', 'chargements'));
    }

    /**
     * Affiche le formulaire de création d'un depotage
     * @return \Illuminate\View\View
     */
    public function createDepotage()
    {
        $produits = Produit::where('status', 'active')->get();
        $cuves = Cuve::with('produit')->get();
        $marketeurs = Marketeur::where('status', 'active')->get();

        return view('gestionnaire.depotage-create', compact('produits', 'cuves', 'marketeurs'));
    }

    public function storeDepotage(Request $request)
    {
        $validated = $request->validate([
            'date_operation' => 'required|date',
            'produit_id' => 'required|exists:produits,id',
            'cuve_destination_id' => 'required|exists:cuves,id',
            'volume_brut' => 'required|integer|min:1',
            'temperature' => 'required|numeric|between:-20,60',
            'fournisseur' => 'required|string',
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
            // Calcul volume corrigé
            $volumeCorrige = $this->calculerVolumeCorrige($validated['volume_brut'], $validated['temperature']);

            $depotage = Depotage::create([
                'numero_depotage' => 'DEP-' . date('YmdHis'),
                'date_operation' => $validated['date_operation'],
                'produit_id' => $validated['produit_id'],
                'cuve_destination_id' => $validated['cuve_destination_id'],
                'volume_brut' => $validated['volume_brut'],
                'temperature' => $validated['temperature'],
                'volume_corrige' => $volumeCorrige,
                'fournisseur' => $validated['fournisseur'],
                'provenance' => $validated['provenance'],
                'numero_bon_chargement' => $validated['numero_bon_chargement'] ?? null,
                'plaque_imm' => $validated['plaque_imm'],
                'chauffeur_nom' => $validated['chauffeur_nom'],
                'chauffeur_permis' => $validated['chauffeur_permis'],
                'chauffeur_tel' => $validated['chauffeur_tel'] ?? null,
                'declaration_douane' => $validated['declaration_douane'] ?? null,
                'bureau_douane' => $validated['bureau_douane'] ?? null,
                'status' => 'sous_douane',
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

            // Mettre à jour le stock de la cuve (dépôt sous douane → cuve marquée sous douane)
            $cuve = Cuve::find($validated['cuve_destination_id']);
            $cuve->niveau_actuel += $volumeCorrige;
            if ($depotage->status === 'sous_douane') {
                $cuve->type_douane = 'sous_douane';
            }
            $cuve->save();

            DB::commit();

            // Générer PDF
            $this->generateDepotagePDF($depotage);

            return view('gestionnaire.document-ready', [
                'operationType' => 'Dépotage',
                'reference' => $depotage->numero_depotage,
                'documentUrl' => route('gestionnaire.document.download', ['type' => 'depotage', 'id' => $depotage->id]),
                'message' => 'Dépotage enregistré et rapport PDF généré.',
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
        if ($depotage->status !== 'sous_douane') {
            return back()->with('error', 'Ce dépotage n\'est pas en attente de douane.');
        }

        DB::transaction(function () use ($depotage) {
            $depotage->update(['status' => 'acquitte']);

            $cuveId = $depotage->cuve_destination_id;
            $encoreSousDouane = Depotage::query()
                ->where('cuve_destination_id', $cuveId)
                ->where('status', 'sous_douane')
                ->exists();

            if (! $encoreSousDouane) {
                Cuve::whereKey($cuveId)->update(['type_douane' => 'acquitte']);
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
        $totalCapacite = $cuves->sum('capacite_totale');
        $totalStock = $cuves->sum('niveau_actuel');
        $sousDouaneVol = Cuve::where('type_douane', 'sous_douane')->sum('niveau_actuel');
        $acquitteVol = Cuve::where('type_douane', 'acquitte')->sum('niveau_actuel');

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
        $marketeurs = Marketeur::where('status', 'active')->get();

        return view('gestionnaire.chargement-create', compact('produits', 'cuves', 'marketeurs'));
    }

    public function storeChargement(Request $request)
    {
        $validated = $request->validate([
            'date_operation' => 'required|date',
            'produit_id' => 'required|exists:produits,id',
            'cuve_source_id' => 'required|exists:cuves,id',
            'volume_brut' => 'required|integer|min:1',
            'temperature' => 'required|numeric|between:-20,60',
            'client_nom' => 'required|string',
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

            $volumeCorrige = $this->calculerVolumeCorrige($validated['volume_brut'], $validated['temperature']);

            $chargement = Chargement::create([
                'numero_chargement' => 'CHG-' . date('YmdHis'),
                'date_operation' => $validated['date_operation'],
                'produit_id' => $validated['produit_id'],
                'cuve_source_id' => $validated['cuve_source_id'],
                'volume_brut' => $validated['volume_brut'],
                'temperature' => $validated['temperature'],
                'volume_corrige' => $volumeCorrige,
                'client_nom' => $validated['client_nom'],
                'client_code' => $validated['client_code'] ?? null,
                'plaque_imm' => $validated['plaque_imm'],
                'chauffeur_nom' => $validated['chauffeur_nom'],
                'chauffeur_permis' => $validated['chauffeur_permis'],
                'capacite_camion' => $validated['capacite_camion'],
                'status' => 'acquitte',
                'created_by' => Auth::id(),
            ]);

            // Mettre à jour le stock
            $cuve->niveau_actuel -= $volumeCorrige;
            $cuve->save();

            DB::commit();

            $this->generateChargementPDF($chargement);

            return view('gestionnaire.document-ready', [
                'operationType' => 'Chargement',
                'reference' => $chargement->numero_chargement,
                'documentUrl' => route('gestionnaire.document.download', ['type' => 'chargement', 'id' => $chargement->id]),
                'message' => 'Chargement enregistré et rapport PDF généré.',
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
        $marketeurs = Marketeur::where('status', 'active')->get();
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
            'cedant_id' => 'required|exists:marqueteurs,id',
            'beneficiaire_id' => 'required|exists:marqueteurs,id|different:cedant_id',
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
                'status' => 'pending',
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            $this->generateCessionPDF($cession);

            return redirect()->route('gestionnaire.operations')->with('success', 'Cession enregistrée avec succès');

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
    protected function stockSupervisionPage(string $pageTitle)
    {
        $cuves = Cuve::with('produit')->get();
        $totalCapacite = $cuves->sum('capacite_totale');
        $totalStock = $cuves->sum('niveau_actuel');
        $sousDouaneVol = Cuve::where('type_douane', 'sous_douane')->sum('niveau_actuel');
        $acquitte = Cuve::where('type_douane', 'acquitte')->sum('niveau_actuel');
        $alertes = Cuve::whereRaw('niveau_actuel <= seuil_alerte_bas OR niveau_actuel >= seuil_alerte_haut')->get();

        $recentDepotages = Depotage::with(['produit', 'cuve'])->latest()->take(8)->get();

        return view('gestionnaire.stocks', compact(
            'cuves',
            'totalCapacite',
            'totalStock',
            'sousDouaneVol',
            'acquitte',
            'alertes',
            'recentDepotages',
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

        $famillesRapport = $this->buildRapportFamilles($day, $cuves);

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

    /**
     * Agrégats du rapport par famille de produit (types en base).
     *
     * @param  \Illuminate\Support\Collection<int, Cuve>  $cuves
     * @return list<array{title: string, badge: string, entrees_jour: int, sorties_jour: int, stock_cuves: int, capacite_totale: int, pct_remplissage: int}>
     */
    protected function buildRapportFamilles(Carbon $day, $cuves): array
    {
        $configs = [
            ['title' => 'Essence', 'badge' => 'SUPER', 'types' => ['essence']],
            ['title' => 'Gasoil', 'badge' => 'DIESEL', 'types' => ['gasoil', 'marine']],
            ['title' => 'Jet A1', 'badge' => 'AV-FUEL', 'types' => ['jet_a1']],
        ];

        $out = [];
        foreach ($configs as $cfg) {
            $cuveList = $cuves->filter(fn (Cuve $c) => in_array($c->produit->type ?? '', $cfg['types'], true));
            $produitIds = $cuveList->pluck('produit_id')->unique()->values();

            $entrees = $produitIds->isEmpty()
                ? 0
                : (int) Depotage::query()
                    ->whereDate('date_operation', $day)
                    ->whereIn('produit_id', $produitIds)
                    ->sum('volume_corrige');

            $sorties = $produitIds->isEmpty()
                ? 0
                : (int) Chargement::query()
                    ->whereDate('date_operation', $day)
                    ->whereIn('produit_id', $produitIds)
                    ->sum('volume_corrige');

            $stockCuves = (int) $cuveList->sum('niveau_actuel');
            $capSum = (int) $cuveList->sum('capacite_totale');
            $cap = max(1, $capSum);
            $pct = $cuveList->isEmpty() ? 0 : min(100, (int) round(($stockCuves / $cap) * 100));

            $out[] = [
                'title' => $cfg['title'],
                'badge' => $cfg['badge'],
                'entrees_jour' => $entrees,
                'sorties_jour' => $sorties,
                'stock_cuves' => $stockCuves,
                'capacite_totale' => $capSum,
                'pct_remplissage' => $pct,
            ];
        }

        return $out;
    }

    public function exportRapportCsv(Request $request): StreamedResponse
    {
        $day = $request->filled('date')
            ? Carbon::parse($request->string('date'))->startOfDay()
            : now()->startOfDay();

        $filename = 'sigecar-rapport-' . $day->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($day) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['Type', 'Date', 'Référence', 'Produit', 'Volume (L)', 'Détail'], ';');

            foreach (Depotage::with('produit')->whereDate('date_operation', $day)->orderBy('date_operation')->cursor() as $d) {
                fputcsv($out, [
                    'Dépotage',
                    $d->date_operation->format('Y-m-d H:i'),
                    $d->numero_depotage,
                    $d->produit->nom ?? '',
                    $d->volume_brut,
                    $d->fournisseur,
                ], ';');
            }
            foreach (Chargement::with('produit')->whereDate('date_operation', $day)->orderBy('date_operation')->cursor() as $c) {
                fputcsv($out, [
                    'Chargement',
                    $c->date_operation->format('Y-m-d H:i'),
                    $c->numero_chargement,
                    $c->produit->nom ?? '',
                    $c->volume_brut,
                    $c->client_nom,
                ], ';');
            }
            foreach (Cession::with('produit')->whereDate('date_cession', $day)->orderBy('date_cession')->cursor() as $ces) {
                fputcsv($out, [
                    'Cession',
                    $ces->date_cession->format('Y-m-d H:i'),
                    $ces->numero_cession,
                    $ces->produit->nom ?? '',
                    $ces->volume,
                    '',
                ], ';');
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
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
        $famillesRapport = $this->buildRapportFamilles($day, $cuves);

        $pdf = Pdf::loadView('pdf.rapport-journalier', compact(
            'day',
            'depotagesJour',
            'chargementsJour',
            'cessionsJour',
            'famillesRapport'
        ));

        return $pdf->download('sigecar-rapport-' . $day->format('Y-m-d') . '.pdf');
    }

    private function calculerVolumeCorrige($volumeBrut, $temperature)
    {
        // Facteur de correction simplifié (ASTM 54B)
        $temperatureReference = 15;
        $coefficientDilatation = 0.00095; // Pour les produits pétroliers légers

        $variation = $temperature - $temperatureReference;
        $correction = 1 + ($coefficientDilatation * $variation);

        return round($volumeBrut / $correction);
    }

    private function generateDepotagePDF($depotage)
    {
        $depotage->load(['produit', 'cuve', 'operationsCreux.produit']);
        $pdf = PDF::loadView('pdf.bon-depotage', compact('depotage'));
        $filename = 'BD-' . date('Ymd') . '-' . $depotage->id . '.pdf';
        $path = storage_path('app/public/documents/' . $filename);
        File::ensureDirectoryExists(dirname($path));
        $pdf->save($path);

        $depotage->update(['document_pdf' => 'documents/' . $filename]);
    }

    private function generateChargementPDF($chargement)
    {
        $chargement->load(['produit', 'cuve']);
        $pdf = PDF::loadView('pdf.bon-chargement', compact('chargement'));
        $filename = 'BC-' . date('Ymd') . '-' . $chargement->id . '.pdf';
        $path = storage_path('app/public/documents/' . $filename);
        File::ensureDirectoryExists(dirname($path));
        $pdf->save($path);

        $chargement->update(['document_pdf' => 'documents/' . $filename]);
    }

    private function generateCessionPDF($cession)
    {
        $cession->load(['produit', 'cuve', 'cedant', 'beneficiaire']);
        $pdf = PDF::loadView('pdf.bon-cession', compact('cession'));
        $filename = 'CESSION-' . date('Ymd') . '-' . $cession->id . '.pdf';
        $path = storage_path('app/public/documents/' . $filename);
        File::ensureDirectoryExists(dirname($path));
        $pdf->save($path);

        $cession->update(['document_pdf' => 'documents/' . $filename]);
    }
}
