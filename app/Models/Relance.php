<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Relance extends Model
{
    protected $table = 'relance';
    protected $primaryKey = 'id_relance';
    protected $guarded = [];

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class, 'id_entreprise');
    }

}
