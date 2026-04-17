<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle pour les marketeurs (commerçants de carburant)
 * Représente les entreprises autorisées à trader du carburant
 */
class Marketeur extends Model
{
    use HasFactory;

    /**
     * Nom de la table associée
     */
    protected $table = 'marqueteurs';

    /**
     * Champs remplissables lors de la création/mise à jour
     */
    protected $fillable = [
        'user_id', 'company_name', 'company_registration', 'address', 'telephone', 'contact_person', 'status'
    ];

    /**
     * Relation avec l'utilisateur associé
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec les cessions où le marketeur est cédant ou bénéficiaire
     */
    public function cessions()
    {
        return $this->hasMany(Cession::class, 'cedant_id')->orWhere('beneficiaire_id');
    }
}
