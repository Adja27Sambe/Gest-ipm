<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Demande extends Model
{
    protected $table = 'demande';
    protected $primaryKey = 'id_demande';
    protected $guarded = [];

    public function typeDemande()
    {
        return $this->belongsTo(TypeDemande::class, 'id_type_demande');
    }

    public function salarie()
    {
        return $this->belongsTo(Salarie::class, 'id_salarie');
    }

    public function ayantDroit()
    {
        return $this->belongsTo(AyantDroit::class, 'id_ayant_droit');
    }

}
