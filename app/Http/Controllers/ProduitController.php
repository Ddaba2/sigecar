<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use Illuminate\Http\Request;

/**
 * Contrôleur API pour la gestion des produits
 * Fournit les endpoints REST pour les opérations CRUD sur les produits
 */
class ProduitController extends Controller
{
    /**
     * Liste tous les produits
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function index()
    {
        return Produit::all();
    }

    /**
     * Crée un nouveau produit
     * @param Request $request
     * @return \App\Models\Produit
     */
    public function store(Request $request)
    {
        return Produit::create($request->all());
    }

    /**
     * Affiche un produit spécifique
     * @param int $id
     * @return \App\Models\Produit
     */
    public function show($id)
    {
        return Produit::findOrFail($id);
    }

    /**
     * Met à jour un produit
     * @param Request $request
     * @param int $id
     * @return \App\Models\Produit
     */
    public function update(Request $request, $id)
    {
        $produit = Produit::findOrFail($id);
        $produit->update($request->all());
        return $produit;
    }

    /**
     * Supprime un produit
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        Produit::destroy($id);
        return response()->json(['message' => 'Produit supprimé']);
    }
}
