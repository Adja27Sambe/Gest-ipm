<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeuilleMaladie extends Model
{
    protected $table = 'feuille_maladie';
    protected $primaryKey = 'id_feuille';
    protected $guarded = [];

    protected $casts = [
        'date_emission' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($feuille) {
            if (empty($feuille->date_emission)) {
                $feuille->date_emission = \Carbon\Carbon::now()->toDateString();
            }
        });
    }

    public function demande()
    {
        return $this->belongsTo(Demande::class, 'id_demande', 'id_demande');
    }
}
