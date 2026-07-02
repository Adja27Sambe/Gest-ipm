<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ValidationDevis extends Model
{
    protected $table = 'validation_devis';
    protected $primaryKey = 'id_validation';
    protected $guarded = [];

    public function devis()
    {
        return $this->belongsTo(Devis::class, 'id_devis');
    }

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur');
    }

}
