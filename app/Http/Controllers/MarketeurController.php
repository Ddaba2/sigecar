<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cession;
use App\Models\Depotage;
use App\Models\Chargement;
use App\Models\Cuve;
use App\Models\User;
use App\Models\Produit;
use App\Services\MarketeurStockService;
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
        $user = Auth::user();
        $stockService = app(MarketeurStockService::class);

        $marketeurStocks = $stockService->forUser($user->id);
        $stockByProduct = $marketeurStocks->mapWithKeys(fn ($s) => [$s->produit->name ?? '—' => $s->quantite]);
        $totalLitresDisponibles = (int) $marketeurStocks->sum('quantite');
        $maxStockProduct = $stockByProduct->max() ?: 1;

        $totalDepotages = (int) Depotage::where('user_id', $user->id)->sum('volume_corrige');
        $totalChargements = (int) Chargement::where('user_id', $user->id)->sum('volume_corrige');

        $recentDepotages = Depotage::where('user_id', $user->id)->with('produit')->latest()->take(8)->get();
        $recentChargements = Chargement::where('user_id', $user->id)->with('produit')->latest()->take(8)->get();

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

        $totalDepotagesMois = (int) Depotage::where('user_id', $user->id)
            ->whereMonth('date_operation', now()->month)
            ->whereYear('date_operation', now()->year)
            ->sum('volume_corrige');

        $totalChargementsMois = (int) Chargement::where('user_id', $user->id)
            ->whereMonth('date_operation', now()->month)
            ->whereYear('date_operation', now()->year)
            ->sum('volume_corrige');

        $sousDouaneActuel = (int) Depotage::where('user_id', $user->id)
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
        $user = Auth::user();

        $query = Cession::where('cedant_id', $user->id)
            ->with(['beneficiaire', 'produit']);

        if ($request->filled('produit_id')) {
            $query->where('produit_id', $request->produit_id);
        }

        $this->applyDateFilter($query, 'date_cession', $request->string('periode')->toString());

        $cessions = $query->latest()->paginate(15)->withQueryString();

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
            $volumeCorrige = (int) round($validated['volume'] * (1 + (15 - $temp) * 0.0008));

            app(MarketeurStockService::class)->transfer(
                $user->id,
                $validated['beneficiaire_id'],
                $validated['produit_id'],
                $volumeCorrige
            );

            Cession::create([
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
                'status' => 'confirmed',
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return redirect()->route('marketeur.cessions')->with('success', 'Cession enregistrée avec succès.');
        } catch (\RuntimeException $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
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
