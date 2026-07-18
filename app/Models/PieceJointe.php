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
