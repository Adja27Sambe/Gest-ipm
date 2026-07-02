<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeuilleMaladie extends Model
{
    protected $table = 'feuille_maladie';
    protected $primaryKey = 'id_feuille';
    protected $guarded = [];

    public function demande()
    {
        return $this->belongsTo(Demande::class, 'id_demande');
    }

}
