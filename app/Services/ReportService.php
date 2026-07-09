<?php

namespace App\Services;

use App\Models\Chargement;
use App\Models\Cuve;
use App\Models\Depotage;
use App\Models\Produit;
use Illuminate\Support\Carbon;

class ReportService
{
    /**
     * Construit le tableau des familles produits pour le rapport journalier.
     * Chaque entrée contient : entrees_jour, sorties_jour, stock_cuves, capacite_totale, pct_remplissage.
     */
    public function buildFamillesRapport(Carbon $day, iterable $cuves): array
    {
        $cuveCollection = collect($cuves);
        $produits = Produit::where('status', 'active')->get();

        $out = [];
        foreach ($produits as $produit) {
            $cuveList = $cuveCollection->filter(fn (Cuve $c) => $c->produit_id === $produit->id);

            $entrees = (int) Depotage::query()
                ->whereDate('date_operation', $day)
                ->where('produit_id', $produit->id)
                ->sum('volume_corrige');

            $sorties = (int) Chargement::query()
                ->whereDate('date_operation', $day)
                ->where('produit_id', $produit->id)
                ->sum('volume_corrige');

            $stockCuves = (int) $cuveList->sum('niveau_actuel');
            $capSum = (int) $cuveList->sum('capacite_totale');
            $pct = $cuveList->isEmpty() ? 0 : min(100, (int) round(($stockCuves / max(1, $capSum)) * 100));

            $out[] = [
                'title'           => $produit->nom,
                'badge'           => $produit->code ?? strtoupper($produit->type ?? substr($produit->nom, 0, 5)),
                'entrees_jour'    => $entrees,
                'sorties_jour'    => $sorties,
                'stock_cuves'     => $stockCuves,
                'capacite_totale' => $capSum,
                'pct_remplissage' => $pct,
            ];
        }

        return $out;
    }
}
