<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @deprecated Non utilisé. Le stock logique est géré par MarketeurStock + MarketeurStockService.
 *             Le stock physique est géré via Cuve::niveau_actuel.
 *             Ce modèle peut être supprimé si la migration stocks n'est plus nécessaire.
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
