<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cotisation extends Model
{
    protected $table = 'cotisation';
    protected $primaryKey = 'id_cotisation';
    protected $guarded = [];

    public function salarie()
    {
        return $this->belongsTo(Salarie::class, 'id_salarie');
    }

}
