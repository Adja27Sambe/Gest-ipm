<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PieceJointe extends Model
{
    use \App\Traits\Auditable;
    protected $table = 'piece_jointe';
    protected $primaryKey = 'id_piece';
    protected $guarded = [];

    protected $casts = [
        'date_ajout'       => 'date',
        'cloud_synced'     => 'boolean',
        'cloud_synced_at'  => 'datetime',
        'taille'           => 'integer',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    /**
     * Accesseur pour la taille formatée lisible
     */
    public function getTailleLisibleAttribute(): string
    {
        $bytes = (int) ($this->taille ?? 0);
        if ($bytes <= 0 && \Illuminate\Support\Facades\Storage::exists($this->chemin_fichier)) {
            $bytes = \Illuminate\Support\Facades\Storage::size($this->chemin_fichier);
        }
        if ($bytes <= 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes, 1024));
        return round($bytes / pow(1024, $i), 1) . ' ' . ($units[$i] ?? 'B');
    }

    /**
     * Obtenir le modèle parent (polymorphe) auquel cette pièce jointe appartient
     */
    public function attachable()
    {
        return $this->morphTo();
    }

    public function categorieDocument()
    {
        return $this->belongsTo(CategorieDocument::class, 'id_categorie');
    }

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur');
    }


    public function categorie(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CategorieDocument::class, 'id_categorie', 'id_categorie');
    }
}
