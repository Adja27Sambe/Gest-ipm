<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Traits\MapsIntersecColumns;

class Salarie extends Authenticatable
{
    use \App\Traits\Auditable;
    use \App\Traits\HasPiecesJointes;
    use MapsIntersecColumns;

    protected $table = 'PARTICIPANT';
    protected $primaryKey = 'IDPARTICIPANT';
    public $timestamps = false;
    protected $guarded = [];
    protected $keyType = 'int';

    protected $hidden = [
        'code_securite',
    ];

    /**
     * Mapping : attribut Laravel -> colonne INTERSEC
     */
    protected $columnMap = [
        'id'                      => 'IDPARTICIPANT',
        'id_salarie'              => 'IDPARTICIPANT',
        'id_entreprise'           => 'IDADHERANT',
        'code_participant'        => 'CODE_PARTICIPANT',
        'matricule'               => 'MATRICULE',
        'nom'                     => 'NOM',
        'prenom'                  => 'PRENOM',
        'nom_complet'             => 'NOMPREN',
        'adresse'                 => 'Adresse',
        'telephone'               => 'TEL',
        'date_naissance'          => 'Date_Naissance',
        'lieu_naissance'          => 'Lieu_Naissance',
        'sexe'                    => 'SEXE',
        'numero_piece_identite'   => 'NUMERO_PIECE_IDENTITE_PARTICIP',
        'situation_matrimoniale'  => 'SITUATION_MATRIMONIALE',
        'conjoints'               => 'CONJOINTS',
        'enfants'                 => 'ENFANTS',
        'fonction'                => 'FONCTION_PARTICIPANT',
        'salaire'                 => 'SALAIRE',
        'actif'                   => 'PARACTIF',
        'statut'                  => 'PARACTIF',
        'type_salaire'            => 'TYPESALAIRE',
        'date_depot'              => 'DATEDEPOT',
        'date_immatriculation'    => 'DATE_IMMATRICULATION',
        'base_sal'                => 'BASESAL',
        'photo'                   => 'PHOTO',
        'nom_jeune_fille'         => 'NOM_JEUNEFILLE',
        'id_agence'               => 'IDAGENCE',
        'solde'                   => 'SOLDE',
        'num_compte'              => 'NUMCOMPTE',
        'conge'                   => 'CONGE',
        'depart'                  => 'DEPART',
        'solde_prec'              => 'SOLDEPREC',
        'id_client'               => 'IDClient',
        'id_site'                 => 'IDSite',
        'date_affectation'        => 'Date_Affectation',
        'date_entree'             => 'Date_Entree',
        'date_embauche'           => 'Date_Entree',
        'email'                   => 'Email',
        'en_veille'               => 'Enveille',
        'nature_contrat'          => 'Nature_Contrat',
    ];

    /**
     * Accesseur / Mutateur pour Sexe (M/F <-> 1/2).
     */
    public function getSexeAttribute($value): ?string
    {
        $raw = $this->attributes['SEXE'] ?? $value;
        if ($raw == 1 || $raw === '1' || strtoupper((string)$raw) === 'M') {
            return 'M';
        }
        if ($raw == 2 || $raw === '2' || strtoupper((string)$raw) === 'F') {
            return 'F';
        }
        return null;
    }

    public function setSexeAttribute($value): void
    {
        if (is_numeric($value)) {
            $this->attributes['SEXE'] = (int) $value;
        } elseif (strtoupper((string)$value) === 'M') {
            $this->attributes['SEXE'] = 1;
        } elseif (strtoupper((string)$value) === 'F') {
            $this->attributes['SEXE'] = 2;
        } else {
            $this->attributes['SEXE'] = 0;
        }
    }

    /**
     * Accesseur / Mutateur pour Statut (actif/radie <-> 1/0).
     */
    public function getStatutAttribute(): string
    {
        $actif = $this->attributes['PARACTIF'] ?? null;
        if ($actif === null) {
            $actif = $this->getAttribute('PARACTIF');
        }
        return ($actif == 1 || $actif === true) ? 'actif' : 'radie';
    }

    public function setStatutAttribute($value): void
    {
        if (is_numeric($value)) {
            $this->attributes['PARACTIF'] = (int) $value;
        } else {
            $this->attributes['PARACTIF'] = strtolower((string)$value) === 'actif' ? 1 : 0;
        }
    }

    /**
     * Accesseur pour Photo (permet d'accéder à $salarie->photo->url ou (string) $salarie->photo).
     */
    public function getPhotoAttribute($value)
    {
        $raw = $this->attributes['PHOTO'] ?? $value;
        if (empty($raw)) {
            return null;
        }

        $url = (str_starts_with((string)$raw, 'http://') || str_starts_with((string)$raw, 'https://'))
            ? (string)$raw
            : asset('storage/' . ltrim((string)$raw, '/'));

        return new class($raw, $url) implements \Stringable {
            public string $path;
            public string $url;

            public function __construct(string $path, string $url)
            {
                $this->path = $path;
                $this->url = $url;
            }

            public function __toString(): string
            {
                return $this->path;
            }
        };
    }

    public function setPhotoAttribute($value): void
    {
        $this->attributes['PHOTO'] = is_null($value) ? null : (string) $value;
    }

    /**
     * Accesseur pour URL de la photo de profil.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        $photo = $this->photo;
        return $photo ? $photo->url : null;
    }

    /**
     * Retourne la valeur hashée du mot de passe (code sécurité).
     */
    public function getAuthPassword()
    {
        return $this->code_securite;
    }

    public function getAuthPasswordName(): string
    {
        return 'code_securite';
    }

    public function getAuthIdentifierName(): string
    {
        return $this->primaryKey;
    }

    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class, 'IDADHERANT', 'IDADHERANT');
    }

    public function ayantsDroit(): HasMany
    {
        return $this->hasMany(AyantDroit::class, 'id_salarie', 'IDPARTICIPANT');
    }

    public function cotisations(): HasMany
    {
        return $this->hasMany(CotisationParticipant::class, 'PACLEUNIK', 'IDPARTICIPANT');
    }

    public function demandes(): HasMany
    {
        return $this->hasMany(Demande::class, 'id_salarie', 'IDPARTICIPANT');
    }

    public function feuillesMaladie(): HasMany
    {
        return $this->hasMany(FeuilMa::class, 'PACLEUNIK', 'IDPARTICIPANT');
    }

    public function carteAssure(): HasOne
    {
        return $this->hasOne(CarteAssure::class, 'id_salarie', 'IDPARTICIPANT');
    }

    /**
     * Nom complet généré automatiquement.
     */
    public function getNomCompletAttribute(): string
    {
        return trim(($this->getAttribute('prenom') ?? '') . ' ' . ($this->getAttribute('nom') ?? ''));
    }
}
