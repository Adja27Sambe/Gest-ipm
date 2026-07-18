<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Observers\SalarieObserver;

#[ObservedBy(SalarieObserver::class)]
class Salarie extends Model
{
    use \App\Traits\Auditable;
    use \App\Traits\HasPiecesJointes;

    protected $table = 'salarie';
    protected $primaryKey = 'id_salarie';
    protected $guarded = [];

    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class, 'id_entreprise');
    }

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'id_photo_media', 'id_media');
    }

    public function ayantsDroit(): HasMany
    {
        return $this->hasMany(AyantDroit::class, 'id_salarie');
    }

    public function carteAssure(): HasOne
    {
        return $this->hasOne(CarteAssure::class, 'id_salarie')->where('statut', CarteAssure::STATUT_ACTIF);
    }


    public function ayantDroits(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AyantDroit::class, 'id_salarie', 'id_salarie');
    }

    public function historiqueCartes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CarteAssure::class, 'id_salarie', 'id_salarie')->orderBy('created_at', 'desc');
    }

    public function cotisations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Cotisation::class, 'id_salarie', 'id_salarie');
    }

    public function demandes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Demande::class, 'id_salarie', 'id_salarie');
    }
}
