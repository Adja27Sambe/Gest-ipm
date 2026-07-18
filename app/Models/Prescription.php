<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    protected $table = 'prescription';
    protected $primaryKey = 'id_prescription';
    protected $guarded = [];

    public function historiqueMedical()
    {
        return $this->belongsTo(HistoriqueMedical::class, 'id_historique_medical', 'id_historique_medical');
    }
}
