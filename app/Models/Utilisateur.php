<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Utilisateur extends Authenticatable
{
    use HasApiTokens, Notifiable, \App\Traits\Auditable;

    protected $table = 'utilisateur';
    protected $primaryKey = 'id_utilisateur';
    protected $guarded = [];

    /**
     * Override pour spécifier le champ du mot de passe.
     */
    public function getAuthPassword()
    {
        return $this->mot_de_passe;
    }

    /**
     * Override pour spécifier la colonne du mot de passe.
     */
    public function getAuthPasswordName()
    {
        return 'mot_de_passe';
    }

    /**
     * Vérifie si l'utilisateur possède une permission spécifique.
     */
    public function hasPermission(string $permissionName): bool
    {
        if (!$this->role) {
            return false;
        }

        return $this->role->permissions->contains('libelle', $permissionName);
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'id_role');
    }


    public function historiqueMouvements(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(HistoriqueMouvement::class, 'id_utilisateur', 'id_utilisateur');
    }

    public function pieceJointes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PieceJointe::class, 'id_utilisateur', 'id_utilisateur');
    }

    public function notifications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Notification::class, 'id_utilisateur', 'id_utilisateur');
    }

    public function validationDevis(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ValidationDevis::class, 'id_utilisateur', 'id_utilisateur');
    }
}
