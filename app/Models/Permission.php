<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'permission';
    protected $primaryKey = 'id_permission';
    protected $guarded = [];

}
