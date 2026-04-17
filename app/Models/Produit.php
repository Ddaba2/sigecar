<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle pour les produits (types de carburant)
 * Représente les différents types de carburant gérés dans le système
 */
class Produit extends Model
{
    use HasFactory;

    /**
     * Champs remplissables lors de la création/mise à jour
     */
    protected $fillable = [
        'nom', 'code', 'type', 'density', 'unit', 'status'
    ];

    /**
     * Relation avec les depotages de ce produit
     */
    public function depotages()
    {
        return $this->hasMany(Depotage::class);
    }

    /**
     * Relation avec les chargements de ce produit
     */
    public function chargements()
    {
        return $this->hasMany(Chargement::class);
    }

    /**
     * Relation avec les stocks de ce produit
     */
    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    /**
     * Relation avec les cuves contenant ce produit
     */
    public function cuves()
    {
        return $this->hasMany(Cuve::class);
    }

    /**
     * Alias lecture pour les vues utilisant `name` (colonne SQL : `nom`).
     */
    public function getNameAttribute(): ?string
    {
        return $this->attributes['nom'] ?? null;
    }
}
