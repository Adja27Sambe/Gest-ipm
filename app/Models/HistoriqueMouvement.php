<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoriqueMouvement extends Model
{
    protected $table = 'historique_mouvement';
    protected $primaryKey = 'id_historique';
    protected $guarded = [];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur');
    }

}
