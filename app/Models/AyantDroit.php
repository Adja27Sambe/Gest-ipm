<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AyantDroit extends Model
{
    protected $table = 'ayant_droit';
    protected $primaryKey = 'id_ayant_droit';
    protected $guarded = [];

    public function salarie(): BelongsTo
    {
        return $this->belongsTo(Salarie::class, 'id_salarie');
    }

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'id_photo_media', 'id_media');
    }


    public function demandes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Demande::class, 'id_ayant_droit', 'id_ayant_droit');
    }

    public function getAgeAttribute(): ?int
    {
        if ($this->date_naissance) {
            return \Carbon\Carbon::parse($this->date_naissance)->age;
        }
        return null;
    }

    public function isEligible(): bool
    {
        $age = $this->age;
        if ($age === null) {
            return true; // Si pas de date de naissance, on ne bloque pas par défaut, ou on pourrait retourner false. En général, il faut une date.
        }
        return $age < 21;
    }
}
