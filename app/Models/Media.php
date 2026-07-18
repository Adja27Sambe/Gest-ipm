<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Media extends Model
{
    use Auditable;

    protected $table = 'media';
    protected $primaryKey = 'id_media';
    protected $guarded = [];

    /**
     * Obtenir l'utilisateur qui a uploadé ce média.
     */
    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur', 'id_utilisateur');
    }

    /**
     * Génère l'URL publique pour accéder au média.
     */
    public function getUrlAttribute()
    {
        return asset('storage/' . $this->chemin_fichier);
    }
}
