<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle pour les cuves de stockage de carburant
 * Représente les réservoirs physiques dans le dépôt
 */
class Cuve extends Model
{
    use HasFactory;

    /**
     * Champs remplissables lors de la création/mise à jour
     */
    protected $fillable = [
        'code', 'nom', 'produit_id', 'capacite_totale', 'niveau_actuel', 'seuil_alerte_bas', 'seuil_alerte_haut', 'status', 'type_douane'
    ];

    /**
     * Casts pour les types de données
     */
    protected $casts = [
        'capacite_totale' => 'integer',
        'niveau_actuel' => 'integer',
        'seuil_alerte_bas' => 'integer',
        'seuil_alerte_haut' => 'integer',
    ];

    /**
     * Relation avec le produit stocké dans la cuve
     */
    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    /**
     * Relation avec les depotages depuis cette cuve
     */
    public function depotages()
    {
        return $this->hasMany(Depotage::class, 'cuve_destination_id');
    }

    /**
     * Relation avec les chargements depuis cette cuve
     */
    public function chargements()
    {
        return $this->hasMany(Chargement::class, 'cuve_source_id');
    }

    /**
     * Accesseur pour calculer le pourcentage de remplissage
     * @return float
     */
    public function getPourcentageRemplissageAttribute()
    {
        if ($this->capacite_totale == 0) return 0;
        return round(($this->niveau_actuel / $this->capacite_totale) * 100);
    }

    /**
     * Accesseur pour déterminer le statut d'alerte
     * @return string|null
     */
    public function getStatutAlerteAttribute()
    {
        if ($this->niveau_actuel <= $this->seuil_alerte_bas) {
            return 'bas';
        } elseif ($this->niveau_actuel >= $this->seuil_alerte_haut) {
            return 'haut';
        }
        return 'normal';
    }
}
