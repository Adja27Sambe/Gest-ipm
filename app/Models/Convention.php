<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Convention extends Model
{
    protected $table = 'convention';
    protected $primaryKey = 'id_convention';
    protected $guarded = [];

    public function prestataire()
    {
        return $this->belongsTo(Prestataire::class, 'id_prestataire');
    }

}
