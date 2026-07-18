<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategorieDocument extends Model
{
    protected $table = 'categorie_document';
    protected $primaryKey = 'id_categorie';
    protected $guarded = [];


    public function pieceJointes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PieceJointe::class, 'id_categorie', 'id_categorie');
    }
}
