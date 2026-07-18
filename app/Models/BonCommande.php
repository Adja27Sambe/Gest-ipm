<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BonCommande extends Model
{
    protected $table = 'bon_commande';
    protected $primaryKey = 'id_bon';
    protected $guarded = [];

    public function demande()
    {
        return $this->belongsTo(Demande::class, 'id_demande', 'id_demande');
    }
}
