<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Modèle User pour l'authentification et la gestion des utilisateurs
 * Étend le modèle Authenticatable de Laravel pour l'authentification
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Champs remplissables lors de la création/mise à jour
     */
    protected $fillable = [
        'name', 'email', 'password', 'role', 'telephone', 'status', 'company_name'
    ];

    /**
     * Champs cachés lors de la sérialisation
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Casts pour les types de données
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Vérifie si l'utilisateur est un administrateur
     * @return bool
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Vérifie si l'utilisateur est un gestionnaire
     * @return bool
     */
    public function isGestionnaire()
    {
        return $this->role === 'gestionnaire';
    }

    /**
     * Vérifie si l'utilisateur est un marketeur
     * @return bool
     */
    public function isMarketeur()
    {
        return $this->role === 'marketeur';
    }

    /**
     * Relation avec les depotages créés par l'utilisateur
     */
    public function depotages()
    {
        return $this->hasMany(Depotage::class, 'created_by');
    }

    /**
     * Relation avec les chargements créés par l'utilisateur
     */
    public function chargements()
    {
        return $this->hasMany(Chargement::class, 'created_by');
    }

    /**
     * Relation avec les cessions créées par l'utilisateur
     */
    public function cessions()
    {
        return $this->hasMany(Cession::class, 'created_by');
    }

    /**
     * Relation vers le marketeur associé à l'utilisateur
     */
    public function marketeur()
    {
        return $this->hasOne(Marketeur::class);
    }
}
