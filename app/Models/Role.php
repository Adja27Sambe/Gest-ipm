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

    /**
     * Vérifie si le rôle dispose d'une permission spécifique (par code ou libellé).
     */
    public function hasPermission(string $permissionCode): bool
    {
        if ($this->libelle === 'Administrateur') {
            return true;
        }

        return $this->permissions->contains(function ($perm) use ($permissionCode) {
            return $perm->code === $permissionCode || $perm->libelle === $permissionCode;
        });
    }

    /**
     * Vérifie si le rôle est en lecture seule (ex: Superviseur, Lecteur, Consultation).
     */
    public function isReadOnly(): bool
    {
        $libelle = strtolower($this->libelle ?? '');
        $code = strtolower($this->code ?? '');

        return str_contains($libelle, 'supervis') || str_contains($code, 'supervis')
            || str_contains($libelle, 'lecteur') || str_contains($code, 'lecteur')
            || str_contains($libelle, 'consultation') || str_contains($code, 'consultation');
    }

    /**
     * Vérifie si le rôle dispose des droits d'édition.
     */
    public function canEdit(): bool
    {
        return !$this->isReadOnly();
    }

    /**
     * Vérifie si le rôle a accès aux statistiques financières de facturation
     * (Réservé aux services de facturation, au superviseur et à l'administrateur).
     */
    public function canViewFacturationStats(): bool
    {
        $libelle = strtolower($this->libelle ?? '');
        $code = strtolower($this->code ?? '');

        if ($libelle === 'administrateur' || $code === 'administrateur') {
            return true;
        }

        if (str_contains($libelle, 'supervis') || str_contains($code, 'supervis')) {
            return true;
        }

        if (str_contains($libelle, 'facturation') || str_contains($code, 'facturation')) {
            return true;
        }

        return $this->hasPermission('gerer_facturation') || $this->hasPermission('Gérer la facturation');
    }
}
