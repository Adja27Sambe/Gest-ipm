<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PieceJointe extends Model
{
    protected $table = 'piece_jointe';
    protected $primaryKey = 'id_piece';
    protected $guarded = [];

    public function categorieDocument()
    {
        return $this->belongsTo(CategorieDocument::class, 'id_categorie');
    }

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur');
    }

}
