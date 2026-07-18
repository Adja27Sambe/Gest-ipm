<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoriqueMouvement extends Model
{
    protected $table = 'historique_mouvement';
    protected $primaryKey = 'id_historique';
    public $timestamps = false; // Le champ date_heure est géré manuellement ou par défaut SQL
    protected $guarded = [];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur', 'id_utilisateur');
    }
}
