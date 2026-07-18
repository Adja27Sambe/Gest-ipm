<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Cotisation extends Model
{
    protected $table = 'cotisation';
    protected $primaryKey = 'id_cotisation';
    protected $guarded = [];

    public function salarie()
    {
        return $this->belongsTo(Salarie::class, 'id_salarie');
    }

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class, 'id_entreprise');
    }

    /**
     * Accessor & Mutator pour calculer le montant automatiquement
     */
    protected function montant(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                $base = floatval($attributes['masse_salariale'] ?? $attributes['salaire_base'] ?? 0);
                return ($base * floatval($attributes['taux'] ?? 0)) / 100;
            },
            set: function (mixed $value, array $attributes) {
                $base = floatval($attributes['masse_salariale'] ?? $attributes['salaire_base'] ?? 0);
                return ($base * floatval($attributes['taux'] ?? 0)) / 100;
            }
        );
    }
}
