<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle pour les cessions de carburant entre marketeurs
 * Représente le transfert de droits sur du carburant stocké
 */
class Cession extends Model
{
    use HasFactory;

    /**
     * Champs remplissables lors de la création/mise à jour
     */
    protected $fillable = [
        'numero_cession', 'date_cession', 'cedant_id', 'beneficiaire_id',
        'produit_id', 'cuve_id', 'volume', 'volume_corrige', 'temperature',
        'prix_unitaire', 'montant_total', 'status', 'created_by', 'document_pdf'
    ];

    /**
     * Casts pour les types de données
     */
    protected $casts = [
        'date_cession' => 'datetime',
        'volume' => 'integer',
        'volume_corrige' => 'integer',
        'temperature' => 'float',
        'prix_unitaire' => 'decimal:2',
        'montant_total' => 'decimal:2',
    ];

    /**
     * Relation avec le marketeur cédant (vendeur)
     */
    public function cedant()
    {
        return $this->belongsTo(Marketeur::class, 'cedant_id');
    }

    /**
     * Relation avec le marketeur bénéficiaire (acheteur)
     */
    public function beneficiaire()
    {
        return $this->belongsTo(Marketeur::class, 'beneficiaire_id');
    }

    /**
     * Relation avec le produit cédé
     */
    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    /**
     * Relation avec la cuve contenant le produit
     */
    public function cuve()
    {
        return $this->belongsTo(Cuve::class);
    }

    /**
     * Relation avec l'utilisateur qui a créé la cession
     */
    public function createur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function generateNumero()
    {
        $this->numero_cession = 'CES-' . date('Ymd') . '-' . str_pad(static::count() + 1, 4, '0', STR_PAD_LEFT);
        return $this->numero_cession;
    }
}
