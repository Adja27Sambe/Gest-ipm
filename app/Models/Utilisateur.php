<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Utilisateur extends Model
{
    protected $table = 'utilisateur';
    protected $primaryKey = 'id_utilisateur';
    protected $guarded = [];

    public function role()
    {
        return $this->belongsTo(Role::class, 'id_role');
    }

}
