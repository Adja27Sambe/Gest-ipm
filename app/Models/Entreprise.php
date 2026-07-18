<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Observers\EntrepriseObserver;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(EntrepriseObserver::class)]
class Entreprise extends Model
{
    use \App\Traits\Auditable;

    protected $table = 'entreprise';
    protected $primaryKey = 'id_entreprise';
    protected $guarded = [];

    public function salaries(): HasMany
    {
        return $this->hasMany(Salarie::class, 'id_entreprise');
    }

    public function relances(): HasMany
    {
        return $this->hasMany(Relance::class, 'id_entreprise');
    }

    public function cotisations(): HasMany
    {
        return $this->hasMany(Cotisation::class, 'id_entreprise');
    }
}
