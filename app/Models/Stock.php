<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle pour le suivi des stocks
 * Enregistre les mouvements de stock pour l'inventaire
 */
class Stock extends Model
{
    use HasFactory;

    /**
     * Champs remplissables lors de la création/mise à jour
     */
    protected $fillable = [
        'produit_id', 'cuve_id', 'quantite', 'type_douane', 'date_mouvement', 'reference', 'reference_type'
    ];

    /**
     * Casts pour les types de données
     */
    protected $casts = [
        'quantite' => 'integer',
        'date_mouvement' => 'datetime',
    ];

    /**
     * Relation avec le produit en stock
     */
    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    /**
     * Relation avec la cuve contenant le stock
     */
    public function cuve()
    {
        return $this->belongsTo(Cuve::class);
    }
}
