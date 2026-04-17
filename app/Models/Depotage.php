<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle pour les depotages de carburant
 * Représente l'arrivée de carburant au dépôt depuis les fournisseurs
 */
class Depotage extends Model
{
    use HasFactory;

    /**
     * Champs remplissables lors de la création/mise à jour
     */
    protected $fillable = [
        'numero_depotage', 'date_operation', 'produit_id', 'cuve_destination_id',
        'volume_brut', 'temperature', 'volume_corrige', 'fournisseur', 'provenance',
        'numero_bon_chargement', 'plaque_imm', 'chauffeur_nom', 'chauffeur_permis',
        'chauffeur_tel', 'declaration_douane', 'bureau_douane', 'status', 'created_by',
        'document_pdf'
    ];

    /**
     * Casts pour les types de données
     */
    protected $casts = [
        'date_operation' => 'datetime',
        'volume_brut' => 'integer',
        'temperature' => 'float',
        'volume_corrige' => 'integer',
    ];

    /**
     * Relation avec le produit dépoté
     */
    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    /**
     * Relation avec la cuve de destination
     */
    public function cuve()
    {
        return $this->belongsTo(Cuve::class, 'cuve_destination_id');
    }

    /**
     * Relation avec l'utilisateur qui a créé le depotage
     */
    public function createur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relation avec les opérations creux liées
     */
    public function operationsCreux()
    {
        return $this->hasMany(OperationCreux::class);
    }

    /**
     * Génère un numéro unique pour le depotage
     * Format: DEP-YYYYMMDD-XXXX
     * @return string
     */
    public function generateNumero()
    {
        $this->numero_depotage = 'DEP-' . date('Ymd') . '-' . str_pad(static::count() + 1, 4, '0', STR_PAD_LEFT);
        return $this->numero_depotage;
    }
}
