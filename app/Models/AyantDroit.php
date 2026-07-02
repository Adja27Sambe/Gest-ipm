<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AyantDroit extends Model
{
    protected $table = 'ayant_droit';
    protected $primaryKey = 'id_ayant_droit';
    protected $guarded = [];

    public function salarie()
    {
        return $this->belongsTo(Salarie::class, 'id_salarie');
    }

}
