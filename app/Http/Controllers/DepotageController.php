<?php

namespace App\Http\Controllers;

use App\Models\Depotage;
use App\Models\Stock;
use Illuminate\Http\Request;

/**
 * Contrôleur API pour la gestion des depotages
 * Fournit les endpoints REST pour les opérations CRUD sur les depotages
 */
class DepotageController extends Controller
{
    /**
     * Liste tous les depotages
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function index()
    {
        return Depotage::with(['produit', 'cuve', 'marqueteur'])->get();
    }

    /**
     * Crée un nouveau depotage
     * @param Request $request
     * @return \App\Models\Depotage
     */
    public function store(Request $request)
    {
        $depotage = Depotage::create($request->all());

        // Mise à jour du stock (ajout)
        Stock::create([
            'produit_id' => $request->produit_id,
            'cuve_id' => $request->cuve_id,
            'quantite' => $request->quantite_corrigee ?? $request->quantite_brute,
            'statut_douanier' => $request->statut_douanier,
            'type_operation' => 'depotage',
            'source_id' => $depotage->id
        ]);

        return $depotage;
    }

    /**
     * Affiche un depotage spécifique
     * @param int $id
     * @return \App\Models\Depotage
     */
    public function show($id)
    {
        return Depotage::with(['produit', 'cuve', 'marqueteur'])->findOrFail($id);
    }

    /**
     * Met à jour un depotage
     * @param Request $request
     * @param int $id
     * @return \App\Models\Depotage
     */
    public function update(Request $request, $id)
    {
        $depotage = Depotage::findOrFail($id);
        $depotage->update($request->all());
        return $depotage;
    }

    /**
     * Supprime un depotage
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        Depotage::destroy($id);
        return response()->json(['message' => 'Depotage supprimé']);
    }
}
