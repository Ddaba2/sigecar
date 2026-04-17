<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle pour les logs d'activité des utilisateurs
 * Enregistre les actions importantes pour l'audit
 */
class Log extends Model
{
    use HasFactory;

    /**
     * Champs remplissables lors de la création/mise à jour
     */
    protected $fillable = [
        'user_id', 'action', 'description', 'ip_address', 'user_agent'
    ];

    /**
     * Casts pour les types de données
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Relation avec l'utilisateur qui a effectué l'action
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
