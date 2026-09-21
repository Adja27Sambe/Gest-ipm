<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\MapsIntersecColumns;

class Entreprise extends Model
{
    use \App\Traits\Auditable;
    use MapsIntersecColumns;

    protected $table = 'ADHERANT';
    protected $primaryKey = 'IDADHERANT';
    public $timestamps = false;
    protected $guarded = [];
    
    // Force le type de clé à string car INTERSEC utilise decimal(19,0)
    protected $keyType = 'int';

    /**
     * Mapping : attribut Laravel -> colonne INTERSEC
     */
    protected $columnMap = [
        'id'                  => 'IDADHERANT',
        'id_entreprise'       => 'IDADHERANT',
        'raison_sociale'      => 'ADHERANT',
        'code_adherent'       => 'CODEADHERANT',
        'adresse'             => 'Adresse',
        'telephone'           => 'TEL',
        'fax'                 => 'FAX',
        'email'               => 'Email',
        'nbr_participants'    => 'NBR_PARTICIPANT',
        'contact'             => 'CONTACT',
        'fonction_contact'    => 'FONCTION1',
        'tel_contact'         => 'TELContact',
        'contact2'            => 'CONTACT2',
        'fonction2'           => 'FONCTION2',
        'tel2'                => 'TEL2',
        'encours'             => 'ENCOURS',
        'encours2'            => 'ENCOURS2',
        'actif'               => 'ADACTIF',
        'statut'              => 'ADACTIF',
        'code_comptable'      => 'COMPTE_COMPTABLE',
        'nb_cotise'           => 'NB_COTISE',
        'mt_cotise_employeur' => 'MT_COTISE_EMPLOYEUR',
        'mt_cotise_staff'     => 'MT_COTISE_STAFF',
        'email_contact'       => 'Email_Contact',
        'id_fda'              => 'IDFDA',
    ];

    /**
     * Accesseur / Mutateur pour Statut (actif/inactif <-> 1/0).
     */
    public function getStatutAttribute(): string
    {
        $actif = $this->attributes['ADACTIF'] ?? null;
        if ($actif === null) {
            $actif = $this->getAttribute('ADACTIF');
        }
        if ($actif == 1 || $actif === true) {
            return 'actif';
        }
        if ($actif == 2) {
            return 'suspendu';
        }
        return 'inactif';
    }

    public function setStatutAttribute($value): void
    {
        if (is_numeric($value)) {
            $this->attributes['ADACTIF'] = (int) $value;
        } else {
            $val = strtolower((string)$value);
            if ($val === 'actif') {
                $this->attributes['ADACTIF'] = 1;
            } elseif ($val === 'suspendu') {
                $this->attributes['ADACTIF'] = 2;
            } else {
                $this->attributes['ADACTIF'] = 0;
            }
        }
    }

    public function salaries(): HasMany
    {
        return $this->hasMany(Salarie::class, 'IDADHERANT', 'IDADHERANT');
    }

    public function cotisations(): HasMany
    {
        return $this->hasMany(Cotisation::class, 'ADCLEUNIK', 'IDADHERANT');
    }

    public function relances(): HasMany
    {
        return $this->hasMany(Relance::class, 'id_entreprise', 'IDADHERANT');
    }
}
