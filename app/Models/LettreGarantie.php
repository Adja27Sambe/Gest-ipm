<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LettreGarantie extends Model
{
    protected $table = 'lettre_garantie';
    protected $primaryKey = 'id_lettre';
    protected $guarded = [];

    public function demande()
    {
        return $this->belongsTo(Demande::class, 'id_demande');
    }

}
