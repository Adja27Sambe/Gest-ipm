<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class HistoriqueMedical extends Model
{
    protected $table = 'historique_medical';
    protected $primaryKey = 'id_historique_medical';
    protected $guarded = [];

    protected $casts = [
        'date_consultation' => 'date',
    ];

    public function beneficiaire(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'beneficiaire_type', 'id_beneficiaire');
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'id_historique_medical', 'id_historique_medical');
    }

    public function prestataire()
    {
        return $this->belongsTo(Prestataire::class, 'id_prestataire', 'id_prestataire');
    }

    public function pathologie()
    {
        return $this->belongsTo(Pathologie::class, 'id_pathologie', 'id_pathologie');
    }
}
