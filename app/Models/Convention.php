<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Convention extends Model
{
    use \App\Traits\Auditable;
    use \App\Traits\HasPiecesJointes;

    protected $table = 'convention';
    protected $primaryKey = 'id_convention';
    protected $guarded = [];

    public function prestataire()
    {
        return $this->belongsTo(Prestataire::class, 'id_prestataire');
    }

    public function praticien()
    {
        return $this->belongsTo(Praticien::class, 'id_praticien', 'PRCLEUNIK');
    }

    public function pharmacie()
    {
        return $this->belongsTo(Pharmacie::class, 'id_pharmacie', 'PHCLEUNIK');
    }
}
