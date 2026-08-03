<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Facture extends Model
{
    protected $table = 'facture';
    protected $primaryKey = 'id_facture';
    protected $guarded = [];

    public function praticien()
    {
        return $this->belongsTo(Praticien::class, 'id_praticien', 'id_praticien');
    }

    public function pharmacie()
    {
        return $this->belongsTo(Pharmacie::class, 'id_pharmacie', 'id_pharmacie');
    }

    public function getPartenaireAttribute()
    {
        return $this->praticien ?? $this->pharmacie;
    }


    public function paiementPrestataires(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PaiementPrestataire::class, 'id_facture', 'id_facture');
    }

    public function prestations()
    {
        return $this->belongsToMany(Prestation::class, 'facture_prestation', 'id_facture', 'id_prestation')->withTimestamps();
    }

    public function getSoldeRestantAttribute()
    {
        $montantTotal = $this->montant ?? 0;
        
        // Optimisation N+1: si l'agrégat a été chargé avec withSum('paiementPrestataires', 'montant'), on l'utilise
        if (array_key_exists('paiement_prestataires_sum_montant', $this->attributes)) {
            $totalPaye = $this->attributes['paiement_prestataires_sum_montant'] ?? 0;
        } else {
            // Fallback (N+1 potentiel)
            $totalPaye = $this->paiementPrestataires()->sum('montant');
        }
        
        return $montantTotal - $totalPaye;
    }
}
