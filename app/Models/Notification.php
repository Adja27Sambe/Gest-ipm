<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notification';
    protected $primaryKey = 'id_notification';
    protected $guarded = [];

    protected $casts = [
        'date_envoi' => 'datetime',
        'lu' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($notification) {
            if (empty($notification->date_envoi)) {
                $notification->date_envoi = \Carbon\Carbon::now();
            }
        });
    }

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur');
    }

}
