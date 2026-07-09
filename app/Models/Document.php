<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @deprecated Non utilisé. Les PDFs sont stockés directement sur chaque modèle d'opération
 *             via le champ `document_pdf` (Depotage, Chargement, Cession).
 *             Ce modèle peut être supprimé si la migration documents n'est plus nécessaire.
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
