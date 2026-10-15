<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BonCommande extends Model
{
    protected $table = 'bon_commande';
    protected $primaryKey = 'id_bon';
    protected $guarded = [];

    protected $casts = [
        'date_emission' => 'date',
        'date_ordonnance' => 'date',
        'date_validite' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($bon) {
            if (empty($bon->date_emission)) {
                $bon->date_emission = \Carbon\Carbon::now()->toDateString();
            }
            if (empty($bon->date_validite)) {
                $bon->date_validite = \Carbon\Carbon::now()->endOfMonth()->toDateString();
            }
        });
    }

    public function demande()
    {
        return $this->belongsTo(Demande::class, 'id_demande', 'id_demande');
    }
}
