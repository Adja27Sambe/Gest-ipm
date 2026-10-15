<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaiementPrestataire extends Model
{
    protected $table = 'paiement_prestataire';
    protected $primaryKey = 'id_paiement';
    protected $guarded = [];

    protected $casts = [
        'date_paiement' => 'date',
        'montant' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($paiement) {
            if (empty($paiement->date_paiement)) {
                $paiement->date_paiement = \Carbon\Carbon::now()->toDateString();
            }
        });
    }

    public function facture()
    {
        return $this->belongsTo(Facture::class, 'id_facture');
    }

}
