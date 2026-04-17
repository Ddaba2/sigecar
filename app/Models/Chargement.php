<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle pour les chargements de carburant
 * Représente une opération de chargement depuis une cuve vers un camion
 */
class Chargement extends Model
{
    use HasFactory;

    /**
     * Champs remplissables lors de la création/mise à jour
     */
    protected $fillable = [
        'numero_chargement', 'date_operation', 'produit_id', 'cuve_source_id',
        'volume_brut', 'temperature', 'volume_corrige', 'client_nom', 'client_code',
        'plaque_imm', 'chauffeur_nom', 'chauffeur_permis', 'chauffeur_badge',
        'capacite_camion', 'status', 'created_by', 'document_pdf'
    ];

    /**
     * Casts pour les types de données
     */
    protected $casts = [
        'date_operation' => 'datetime',
        'volume_brut' => 'integer',
        'temperature' => 'float',
        'volume_corrige' => 'integer',
        'capacite_camion' => 'integer',
    ];

    /**
     * Relation avec le produit chargé
     */
    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    /**
     * Relation avec la cuve source du chargement
     */
    public function cuve()
    {
        return $this->belongsTo(Cuve::class, 'cuve_source_id');
    }

    /**
     * Relation avec l'utilisateur qui a créé le chargement
     */
    public function createur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Génère un numéro unique pour le chargement
     * Format: CHG-YYYYMMDD-XXXX
     * @return string
     */
    public function generateNumero()
    {
        $this->numero_chargement = 'CHG-' . date('Ymd') . '-' . str_pad(static::count() + 1, 4, '0', STR_PAD_LEFT);
        return $this->numero_chargement;
    }
}
