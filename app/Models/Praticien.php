<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\PartenaireSante;

class Praticien extends Model
{
    use PartenaireSante;

    protected $table = 'praticien';
    protected $primaryKey = 'id_praticien';
    protected $guarded = [];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->code_praticien)) {
                $model->code_praticien = 'PRAT-' . date('YmdHis') . '-' . strtoupper(\Illuminate\Support\Str::random(4));
            }
        });
    }

    public function demandes()
    {
        return $this->hasMany(Demande::class, 'id_praticien', 'id_praticien');
    }

    public function conventions()
    {
        return $this->hasMany(Convention::class, 'id_praticien', 'id_praticien');
    }
}
