<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoriqueMedical extends Model
{
    protected $table = 'historique_medical';
    protected $primaryKey = 'id_historique_medical';
    protected $guarded = [];

    public function prestataire()
    {
        return $this->belongsTo(Prestataire::class, 'id_prestataire');
    }

}
