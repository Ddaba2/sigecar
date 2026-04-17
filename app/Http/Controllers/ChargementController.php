<?php

namespace App\Http\Controllers;

use App\Models\Chargement;
use App\Models\Stock;
use Illuminate\Http\Request;

/**
 * Contrôleur API pour la gestion des chargements
 * Fournit les endpoints REST pour les opérations CRUD sur les chargements
 */
class ChargementController extends Controller
{
    /**
     * Liste tous les chargements
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function index()
    {
        return Chargement::with(['produit', 'cuve', 'marqueteur'])->get();
    }

    /**
     * Crée un nouveau chargement
     * @param Request $request
     * @return \App\Models\Chargement
     */
    public function store(Request $request)
    {
        $chargement = Chargement::create($request->all());

        // Mise à jour du stock (diminution)
        Stock::create([
            'produit_id' => $request->produit_id,
            'cuve_id' => $request->cuve_id,
            'quantite' => -$request->quantite_delivree,
            'statut_douanier' => 'acquitte',
            'type_operation' => 'chargement',
            'source_id' => $chargement->id
        ]);

        return $chargement;
    }

    /**
     * Affiche un chargement spécifique
     * @param int $id
     * @return \App\Models\Chargement
     */
    public function show($id)
    {
        return Chargement::with(['produit', 'cuve', 'marqueteur'])->findOrFail($id);
    }

    /**
     * Met à jour un chargement
     * @param Request $request
     * @param int $id
     * @return \App\Models\Chargement
     */
    public function update(Request $request, $id)
    {
        $chargement = Chargement::findOrFail($id);
        $chargement->update($request->all());
        return $chargement;
    }

    /**
     * Supprime un chargement
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        Chargement::destroy($id);
        return response()->json(['message' => 'Chargement supprimé']);
    }
}
