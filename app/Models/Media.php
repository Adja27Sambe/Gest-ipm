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

    protected $casts = [
        'taille'           => 'integer',
        'largeur'          => 'integer',
        'hauteur'          => 'integer',
        'cloud_synced'     => 'boolean',
        'cloud_synced_at'  => 'datetime',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    /**
     * Accesseur pour la taille lisible
     */
    public function getTailleLisibleAttribute(): string
    {
        $bytes = (int) ($this->taille ?? 0);
        if ($bytes <= 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes, 1024));
        return round($bytes / pow(1024, $i), 1) . ' ' . ($units[$i] ?? 'B');
    }

    /**
     * Dimensions en pixels (ex: 1920x1080)
     */
    public function getDimensionsAttribute(): ?string
    {
        if ($this->largeur && $this->hauteur) {
            return "{$this->largeur} × {$this->hauteur} px";
        }
        return null;
    }

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
