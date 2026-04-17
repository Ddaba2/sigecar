<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle pour les documents générés (PDF, etc.)
 * Stocke les références aux fichiers générés pour les opérations
 */
class Document extends Model
{
    use HasFactory;

    /**
     * Champs remplissables lors de la création/mise à jour
     */
    protected $fillable = [
        'reference_type', 'reference_id', 'type_document', 'file_path', 'file_name', 'generated_by'
    ];

    /**
     * Relation avec l'utilisateur qui a généré le document
     */
    public function generateur()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
