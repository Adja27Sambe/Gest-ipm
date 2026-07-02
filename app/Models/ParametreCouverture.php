<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParametreCouverture extends Model
{
    protected $table = 'parametre_couverture';
    protected $primaryKey = 'id_parametre';
    protected $guarded = [];

    public function typePrestation()
    {
        return $this->belongsTo(TypePrestation::class, 'id_type_prestation');
    }

}
