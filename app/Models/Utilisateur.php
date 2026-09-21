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
     * Vérifie si l'utilisateur possède une permission spécifique (par code ou libellé).
     */
    public function hasPermission(string $permissionName): bool
    {
        if (!$this->role) {
            return false;
        }

        // L'administrateur global possède tous les accès
        if ($this->role->libelle === 'Administrateur') {
            return true;
        }

        return $this->role->hasPermission($permissionName);
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

    /**
     * Vérifie si l'utilisateur possède un profil en lecture seule (Lecteur / Non-éditeur).
     */
    public function isReadOnly(): bool
    {
        return $this->role ? $this->role->isReadOnly() : false;
    }

    /**
     * Vérifie si l'utilisateur peut modifier / éditer des données.
     */
    public function canEdit(): bool
    {
        return !$this->isReadOnly();
    }

    /**
     * Détermine si l'utilisateur peut visualiser les statistiques de facturation (Total Facturé et Reste à Payer).
     * Réservé aux services de facturation, au superviseur et à l'administrateur.
     */
    public function canViewFacturationStats(): bool
    {
        return $this->role ? $this->role->canViewFacturationStats() : false;
    }
}
