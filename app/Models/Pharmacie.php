<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\PartenaireSante;

class Pharmacie extends Model
{
    use PartenaireSante;

    protected $table = 'pharmacie';
    protected $primaryKey = 'id_pharmacie';
    protected $guarded = [];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->code_pharmacie)) {
                $model->code_pharmacie = 'PHAR-' . date('YmdHis') . '-' . strtoupper(\Illuminate\Support\Str::random(4));
            }
        });
    }

    public function demandes()
    {
        return $this->hasMany(Demande::class, 'id_pharmacie', 'id_pharmacie');
    }

    public function conventions()
    {
        return $this->hasMany(Convention::class, 'id_pharmacie', 'id_pharmacie');
    }
}
