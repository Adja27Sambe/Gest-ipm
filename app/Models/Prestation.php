<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prestation extends Model
{
    protected $table = 'prestation';
    protected $primaryKey = 'id_prestation';
    protected $guarded = [];

    public function typePrestation()
    {
        return $this->belongsTo(TypePrestation::class, 'id_type_prestation');
    }

    public function prestataire()
    {
        return $this->belongsTo(Prestataire::class, 'id_prestataire');
    }

    public function demande()
    {
        return $this->belongsTo(Demande::class, 'id_demande');
    }

}
