<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypePrestation extends Model
{
    protected $table = 'type_prestation';
    protected $primaryKey = 'id_type_prestation';
    protected $guarded = [];


    public function prestations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Prestation::class, 'id_type_prestation', 'id_type_prestation');
    }

    public function parametreCouvertures(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ParametreCouverture::class, 'id_type_prestation', 'id_type_prestation');
    }
}
