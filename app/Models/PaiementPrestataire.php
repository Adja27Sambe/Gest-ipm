<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaiementPrestataire extends Model
{
    protected $table = 'paiement_prestataire';
    protected $primaryKey = 'id_paiement';
    protected $guarded = [];

    public function facture()
    {
        return $this->belongsTo(Facture::class, 'id_facture');
    }

}
