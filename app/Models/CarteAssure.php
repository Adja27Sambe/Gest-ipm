<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarteAssure extends Model
{
    protected $table = 'carte_assure';
    protected $primaryKey = 'id_carte';
    protected $guarded = [];

    protected $casts = [
        'date_emission' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($carte) {
            if (empty($carte->date_emission)) {
                $carte->date_emission = \Carbon\Carbon::now()->toDateString();
            }
        });
    }

    // Constantes de statut
    public const STATUT_ACTIF = 'actif';
    public const STATUT_ANNULEE = 'annulée';

    /**
     * Scope a query to only include active cards.
     */
    public function scopeActives($query)
    {
        return $query->where('statut', self::STATUT_ACTIF);
    }

    public function salarie(): BelongsTo
    {
        return $this->belongsTo(Salarie::class, 'id_salarie', 'IDPARTICIPANT');
    }

}
