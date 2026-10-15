<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Relance extends Model
{
    protected $table = 'relance';
    protected $primaryKey = 'id_relance';
    protected $guarded = [];

    protected $casts = [
        'date_relance' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($relance) {
            if (empty($relance->date_relance)) {
                $relance->date_relance = \Carbon\Carbon::now()->toDateString();
            }
        });
    }

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class, 'id_entreprise');
    }

}
