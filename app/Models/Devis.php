<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Devis extends Model
{
    protected $table = 'devis';
    protected $primaryKey = 'id_devis';
    protected $guarded = [];

    public function prestataire()
    {
        return $this->belongsTo(Prestataire::class, 'id_prestataire');
    }

}
