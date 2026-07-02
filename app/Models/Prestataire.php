<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prestataire extends Model
{
    protected $table = 'prestataire';
    protected $primaryKey = 'id_prestataire';
    protected $guarded = [];

    public function typePrestataire()
    {
        return $this->belongsTo(TypePrestataire::class, 'id_type');
    }

}
