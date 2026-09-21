<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Facture extends Model
{
    use \App\Traits\Auditable;

    protected $table = 'facture';
    protected $primaryKey = 'id_facture';
    protected $guarded = [];

    protected $casts = [
        'date_facture' => 'date',
        'montant' => 'decimal:2',
    ];

    public function praticien(): BelongsTo
    {
        return $this->belongsTo(Praticien::class, 'id_praticien', 'id_praticien');
    }

    public function pharmacie(): BelongsTo
    {
        return $this->belongsTo(Pharmacie::class, 'id_pharmacie', 'id_pharmacie');
    }

    public function getPartenaireAttribute()
    {
        return $this->praticien ?? $this->pharmacie;
    }

    public function paiementPrestataires(): HasMany
    {
        return $this->hasMany(PaiementPrestataire::class, 'id_facture', 'id_facture');
    }

    public function prestations(): BelongsToMany
    {
        return $this->belongsToMany(Prestation::class, 'facture_prestation', 'id_facture', 'id_prestation')->withTimestamps();
    }

    public function getSoldeRestantAttribute()
    {
        $montantTotal = floatval($this->montant ?? 0);
        
        // Optimisation N+1: si l'agrégat a été chargé avec withSum('paiementPrestataires', 'montant'), on l'utilise
        if (array_key_exists('paiement_prestataires_sum_montant', $this->attributes)) {
            $totalPaye = floatval($this->attributes['paiement_prestataires_sum_montant'] ?? 0);
        } else {
            $totalPaye = floatval($this->paiementPrestataires()->sum('montant'));
        }
        
        return max(0, $montantTotal - $totalPaye);
    }
}
