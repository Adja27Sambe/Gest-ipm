<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypePrestataire extends Model
{
    protected $table = 'type_prestataire';
    protected $primaryKey = 'id_type';
    protected $guarded = [];


    public function prestataires(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Prestataire::class, 'id_type', 'id_type');
    }
}
