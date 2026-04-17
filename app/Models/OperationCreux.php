<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle pour les opérations creux (camions-citernes)
 * Représente les compartiments des camions utilisés pour le transport
 */
class OperationCreux extends Model
{
    use HasFactory;

    /**
     * Nom de la table associée
     */
    protected $table = 'operations_creux';

    /**
     * Champs remplissables lors de la création/mise à jour
     */
    protected $fillable = [
        'depotage_id', 'chargement_id', 'numero_creux', 'produit_id',
        'capacite', 'volume', 'temperature'
    ];

    /**
     * Casts pour les types de données
     */
    protected $casts = [
        'capacite' => 'integer',
        'volume' => 'integer',
        'temperature' => 'float',
    ];

    /**
     * Relation avec le depotage associé
     */
    public function depotage()
    {
        return $this->belongsTo(Depotage::class);
    }

    /**
     * Relation avec le chargement associé
     */
    public function chargement()
    {
        return $this->belongsTo(Chargement::class);
    }

    /**
     * Relation avec le produit transporté
     */
    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }
}
