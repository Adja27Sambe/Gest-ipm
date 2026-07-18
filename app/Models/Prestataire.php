<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prestataire extends Model
{
    protected $table = 'prestataire';
    protected $primaryKey = 'id_prestataire';
    protected $guarded = [];

    public function typePrestataire()
    {
        return $this->belongsTo(TypePrestataire::class, 'id_type');
    }

    /**
     * Scope : Filtre les prestataires ayant une convention active
     */
    public function scopeConventionActive($query)
    {
        $now = \Carbon\Carbon::now()->toDateString();
        
        return $query->whereHas('conventions', function($q) use ($now) {
            $q->where('statut', 'active')
              ->whereDate('date_debut', '<=', $now)
              ->whereDate('date_fin', '>=', $now);
        });
    }


    public function conventions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Convention::class, 'id_prestataire', 'id_prestataire');
    }

    public function factures(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Facture::class, 'id_prestataire', 'id_prestataire');
    }

    public function prestations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Prestation::class, 'id_prestataire', 'id_prestataire');
    }

    public function devis(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Devis::class, 'id_prestataire', 'id_prestataire');
    }

    public function type(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(TypePrestataire::class, 'id_type', 'id_type');
    }

    public function historiqueMedicals(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(HistoriqueMedical::class, 'id_prestataire', 'id_prestataire');
    }
}
