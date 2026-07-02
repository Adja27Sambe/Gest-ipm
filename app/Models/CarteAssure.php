<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarteAssure extends Model
{
    protected $table = 'carte_assure';
    protected $primaryKey = 'id_carte';
    protected $guarded = [];

    public function salarie()
    {
        return $this->belongsTo(Salarie::class, 'id_salarie');
    }

}
