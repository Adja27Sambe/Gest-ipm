<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LettreGarantie extends Model
{
    protected $table = 'lettre_garantie';
    protected $primaryKey = 'id_lettre';
    protected $guarded = [];

    protected $casts = [
        'date_emission' => 'date',
        'date_validite' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($lettre) {
            if (empty($lettre->date_emission)) {
                $lettre->date_emission = \Carbon\Carbon::now()->toDateString();
            }
            if (empty($lettre->date_validite)) {
                $lettre->date_validite = \Carbon\Carbon::now()->endOfMonth()->toDateString();
            }
        });
    }

    public function demande()
    {
        return $this->belongsTo(Demande::class, 'id_demande', 'id_demande');
    }
}
