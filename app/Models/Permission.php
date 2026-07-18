<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Permission extends Model
{
    protected $table = 'permission';
    protected $primaryKey = 'id_permission';
    protected $guarded = [];


    public function rolePermissions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RolePermission::class, 'id_permission', 'id_permission');
    }

    public function roles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permission', 'id_permission', 'id_role');
    }
}
