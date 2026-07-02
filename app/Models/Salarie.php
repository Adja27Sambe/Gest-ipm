<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salarie extends Model
{
    protected $table = 'salarie';
    protected $primaryKey = 'id_salarie';
    protected $guarded = [];

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class, 'id_entreprise');
    }

}
