<?php

namespace App\Models;

use App\Enums\DevisStatut;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Devis extends Model
{
    use \App\Traits\Auditable;

    protected $table = 'devis';
    protected $primaryKey = 'id_devis';
    protected $guarded = [];

    protected $casts = [
        'statut' => DevisStatut::class,
        'date_devis' => 'date',
    ];

    /**
     * Relation polymorphe vers Salarie ou AyantDroit.
     * Note: On utilise 'id_beneficiaire' comme clé étrangère selon le schéma d'origine.
     */
    public function beneficiaire(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'beneficiaire_type', 'id_beneficiaire');
    }

    public function prestataire()
    {
        return $this->belongsTo(Prestataire::class, 'id_prestataire', 'id_prestataire');
    }

    public function validations()
    {
        return $this->hasMany(ValidationDevis::class, 'id_devis', 'id_devis');
    }
}
