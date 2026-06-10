<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cession;
use App\Models\Depotage;
use App\Models\Chargement;
use App\Models\Cuve;
use App\Models\Marketeur;
use App\Models\Produit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Contrôleur pour la gestion des fonctionnalités du marketeur
 */
class MarketeurController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:marketeur');
    }

    public function dashboard()
    {
        $marketeur = Auth::user()->marketeur;
        $company = $marketeur?->company_name ?? '';

        $totalVolumeCede = (int) Cession::where('cedant_id', $marketeur?->id ?? 0)->sum('volume');
        $totalVolumeRecu = (int) Cession::where('beneficiaire_id', $marketeur?->id ?? 0)->sum('volume');
        $totalDepotages = (int) Depotage::where('fournisseur', $company)->sum('volume_corrige');
        $totalChargements = (int) Chargement::where('client_nom', $company)->sum('volume_corrige');

        $totalLitresDisponibles = max(0, $totalVolumeRecu + $totalDepotages - $totalVolumeCede - $totalChargements);

        $recentDepotages = Depotage::where('fournisseur', $company)->with('produit')->latest()->take(8)->get();
        $recentChargements = Chargement::where('client_nom', $company)->with('produit')->latest()->take(8)->get();

        $recentOperations = $recentDepotages->map(fn ($d) => [
            'date' => $d->date_operation,
            'type' => 'Dépotage',
            'type_icon' => 'fa-download',
            'produit' => $d->produit->name ?? '—',
            'volume' => $d->volume_brut,
            'status' => $d->status,
        ])->concat($recentChargements->map(fn ($c) => [
            'date' => $c->date_operation,
            'type' => 'Chargement',
            'type_icon' => 'fa-upload',
            'produit' => $c->produit->name ?? '—',
            'volume' => $c->volume_brut,
            'status' => $c->status,
        ]))->sortByDesc('date')->take(6)->values();

        $stockByProduct = Depotage::where('fournisseur', $company)
            ->with('produit')
            ->get()
            ->groupBy(fn ($d) => $d->produit->name ?? 'Autre')
            ->map(fn ($group) => (int) $group->sum('volume_corrige'));

        $maxStockProduct = $stockByProduct->max() ?: 1;

        return view('marketeur.dashboard', compact(
            'totalLitresDisponibles',
            'totalDepotages',
            'totalChargements',
            'recentOperations',
            'stockByProduct',
            'maxStockProduct'
        ));
    }

    public function operations(Request $request)
    {
        $marketeur = Auth::user()->marketeur;
        $company = $marketeur?->company_name ?? '';

        $depotagesQ = Depotage::where('fournisseur', $company)->with(['produit', 'cuve']);
        $chargementsQ = Chargement::where('client_nom', $company)->with(['produit', 'cuve']);

        $this->applyDateFilter($depotagesQ, 'date_operation', $request->string('periode')->toString());
        $this->applyDateFilter($chargementsQ, 'date_operation', $request->string('periode')->toString());

        if ($request->filled('produit_id')) {
            $depotagesQ->where('produit_id', $request->produit_id);
            $chargementsQ->where('produit_id', $request->produit_id);
        }

        $depotages = $depotagesQ->latest()->get();
        $chargements = $chargementsQ->latest()->get();

        $operations = $depotages->map(fn ($d) => [
            'date' => $d->date_operation,
            'type' => 'Dépotage',
            'type_icon' => 'fa-download',
            'produit' => $d->produit->name ?? '—',
            'volume_brut' => $d->volume_brut,
            'volume_corrige' => $d->volume_corrige,
            'status' => $d->status,
        ])->concat($chargements->map(fn ($c) => [
            'date' => $c->date_operation,
            'type' => 'Chargement',
            'type_icon' => 'fa-upload',
            'produit' => $c->produit->name ?? '—',
            'volume_brut' => $c->volume_brut,
            'volume_corrige' => $c->volume_corrige,
            'status' => $c->status,
        ]))->sortByDesc('date')->values();

        if ($request->filled('type') && $request->type !== 'tous') {
            $typeLabel = $request->type === 'depotage' ? 'Dépotage' : 'Chargement';
            $operations = $operations->filter(fn ($o) => $o['type'] === $typeLabel)->values();
        }

        $totalDepotagesMois = (int) Depotage::where('fournisseur', $company)
            ->whereMonth('date_operation', now()->month)
            ->whereYear('date_operation', now()->year)
            ->sum('volume_corrige');

        $totalChargementsMois = (int) Chargement::where('client_nom', $company)
            ->whereMonth('date_operation', now()->month)
            ->whereYear('date_operation', now()->year)
            ->sum('volume_corrige');

        $sousDouaneActuel = (int) Depotage::where('fournisseur', $company)
            ->where('status', 'sous_douane')
            ->sum('volume_corrige');

        $produits = Produit::where('status', 'active')->get();

        return view('marketeur.operations', compact(
            'operations',
            'totalDepotagesMois',
            'totalChargementsMois',
            'sousDouaneActuel',
            'produits'
        ));
    }

    public function cessions(Request $request)
    {
        $marketeur = Auth::user()->marketeur;

        $query = Cession::where('cedant_id', $marketeur?->id ?? 0)
            ->with(['beneficiaire', 'produit']);

        if ($request->filled('produit_id')) {
            $query->where('produit_id', $request->produit_id);
        }

        $this->applyDateFilter($query, 'date_cession', $request->string('periode')->toString());

        $cessions = $query->latest()->paginate(15)->withQueryString();

        $totalTransfereMois = (int) Cession::where('cedant_id', $marketeur?->id ?? 0)
            ->whereMonth('date_cession', now()->month)
            ->whereYear('date_cession', now()->year)
            ->sum('volume');

        $totalRecuMois = (int) Cession::where('beneficiaire_id', $marketeur?->id ?? 0)
            ->whereMonth('date_cession', now()->month)
            ->whereYear('date_cession', now()->year)
            ->sum('volume');

        $produits = Produit::where('status', 'active')->get();

        return view('marketeur.cessions', compact(
            'cessions',
            'totalTransfereMois',
            'totalRecuMois',
            'produits'
        ));
    }

    public function showCession($id)
    {
        $marketeur = Auth::user()->marketeur;
        $cession = Cession::with(['cedant', 'beneficiaire', 'produit', 'cuve'])->findOrFail($id);

        if ($cession->cedant_id != $marketeur?->id && $cession->beneficiaire_id != $marketeur?->id) {
            abort(403);
        }

        return view('marketeur.cession-detail', compact('cession'));
    }

    public function createCession()
    {
        $produits = Produit::where('status', 'active')->get();
        $cuves = Cuve::with('produit')->get();
        $marketeurs = Marketeur::where('status', 'active')->get();

        return view('marketeur.cession-create', compact('produits', 'cuves', 'marketeurs'));
    }

    public function storeCession(Request $request)
    {
        $marketeur = Auth::user()->marketeur;

        $validated = $request->validate([
            'date_cession' => 'required|date',
            'beneficiaire_id' => 'required|exists:marqueteurs,id',
            'produit_id' => 'required|exists:produits,id',
            'cuve_id' => 'required|exists:cuves,id',
            'volume' => 'required|integer|min:1',
            'temperature' => 'nullable|numeric',
        ]);

        if ($validated['beneficiaire_id'] == $marketeur?->id) {
            return back()->with('error', 'Le bénéficiaire doit être différent de votre société.');
        }

        DB::beginTransaction();
        try {
            $temp = $validated['temperature'] ?? 15;
            $volumeCorrige = (int) round($validated['volume'] * (1 + (15 - $temp) * 0.0008));

            Cession::create([
                'numero_cession' => 'CES-' . date('YmdHis'),
                'date_cession' => $validated['date_cession'],
                'cedant_id' => $marketeur->id,
                'beneficiaire_id' => $validated['beneficiaire_id'],
                'produit_id' => $validated['produit_id'],
                'cuve_id' => $validated['cuve_id'],
                'volume' => $validated['volume'],
                'volume_corrige' => $volumeCorrige,
                'temperature' => $temp,
                'prix_unitaire' => 0,
                'montant_total' => 0,
                'status' => 'pending',
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return redirect()->route('marketeur.cessions')->with('success', 'Cession enregistrée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function settings()
    {
        return view('marketeur.settings');
    }

    public function cuveStock(int $id)
    {
        $cuve = Cuve::findOrFail($id);

        return response()->json([
            'niveau_actuel' => $cuve->niveau_actuel,
            'capacite_totale' => $cuve->capacite_totale,
        ]);
    }

    protected function applyDateFilter($query, string $column, string $periode): void
    {
        $days = match ($periode) {
            '7' => 7,
            '30' => 30,
            '90' => 90,
            default => null,
        };

        if ($days) {
            $query->where($column, '>=', now()->subDays($days));
        }
    }
}
