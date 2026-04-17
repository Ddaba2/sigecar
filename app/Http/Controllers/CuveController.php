<?php

namespace App\Http\Controllers;

use App\Models\Cuve;
use Illuminate\Http\Request;

/**
 * Contrôleur API pour la gestion des cuves
 * Fournit les endpoints REST pour les opérations CRUD sur les cuves
 */
class CuveController extends Controller
{
    /**
     * Liste toutes les cuves
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function index()
    {
        return Cuve::with('produit')->get();
    }

    /**
     * Crée une nouvelle cuve
     * @param Request $request
     * @return \App\Models\Cuve
     */
    public function store(Request $request)
    {
        return Cuve::create($request->all());
    }

    /**
     * Affiche une cuve spécifique
     * @param int $id
     * @return \App\Models\Cuve
     */
    public function show($id)
    {
        return Cuve::with('produit')->findOrFail($id);
    }

    /**
     * Met à jour une cuve
     * @param Request $request
     * @param int $id
     * @return \App\Models\Cuve
     */
    public function update(Request $request, $id)
    {
        $cuve = Cuve::findOrFail($id);
        $cuve->update($request->all());
        return $cuve;
    }

    /**
     * Supprime une cuve
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        Cuve::destroy($id);
        return response()->json(['message' => 'Cuve supprimée']);
    }
}
