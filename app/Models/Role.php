<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $table = 'role';
    protected $primaryKey = 'id_role';
    protected $guarded = [];


    public function utilisateurs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Utilisateur::class, 'id_role', 'id_role');
    }

    public function rolePermissions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RolePermission::class, 'id_role', 'id_role');
    }

    public function permissions(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission', 'id_role', 'id_permission');
    }

}
