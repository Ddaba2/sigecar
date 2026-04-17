<?php

namespace App\Http\Controllers;

use App\Models\Cession;
use App\Models\Stock;
use Illuminate\Http\Request;

/**
 * Contrôleur API pour la gestion des cessions
 * Fournit les endpoints REST pour les opérations CRUD sur les cessions
 */
class CessionController extends Controller
{
    /**
     * Liste toutes les cessions
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function index()
    {
        return Cession::with(['produit', 'cuve'])->get();
    }

    /**
     * Crée une nouvelle cession
     * @param Request $request
     * @return \App\Models\Cession
     */
    public function store(Request $request)
    {
        $cession = Cession::create($request->all());

        // Mise à jour du stock (sortie)
        Stock::create([
            'produit_id' => $request->produit_id,
            'cuve_id' => $request->cuve_id,
            'quantite' => -$request->quantite,
            'statut_douanier' => 'acquitte',
            'type_operation' => 'cession',
            'source_id' => $cession->id
        ]);

        return $cession;
    }

    /**
     * Affiche une cession spécifique
     * @param int $id
     * @return \App\Models\Cession
     */
    public function show($id)
    {
        return Cession::with(['produit', 'cuve'])->findOrFail($id);
    }

    /**
     * Met à jour une cession
     * @param Request $request
     * @param int $id
     * @return \App\Models\Cession
     */
    public function update(Request $request, $id)
    {
        $cession = Cession::findOrFail($id);
        $cession->update($request->all());
        return $cession;
    }

    /**
     * Supprime une cession
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        Cession::destroy($id);
        return response()->json(['message' => 'Cession supprimée']);
    }
}
