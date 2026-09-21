<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\MapsIntersecColumns;

class Praticien extends Model
{
    use MapsIntersecColumns;

    protected $table = 'PRATICIE';
    protected $primaryKey = 'PRCLEUNIK';
    public $timestamps = false;
    protected $guarded = [];
    protected $keyType = 'int';

    protected $columnMap = [
        'id'                => 'PRCLEUNIK',
        'id_praticien'      => 'PRCLEUNIK',
        'code_praticien'    => 'CODEPRAT',
        'nom'               => 'NOMPRAT',
        'adresse'           => 'ADRPRAT',
        'telephone'         => 'TELPRAT',
        'specialite'        => 'SPECIALITE',
        'contact'           => 'CONTACT',
        'tel_contact'       => 'TELCONTACT',
        'id_specialite'     => 'SPCLEUNIK',
        'montant_acte'      => 'MONTACT',
        'description_acte'  => 'DESACTE',
        'compte_comptable'  => 'COMPTE_COMPTABLE',
    ];

    public function specialiteRelation()
    {
        return $this->belongsTo(Specialite::class, 'SPCLEUNIK', 'SPCLEUNIK');
    }

    public function facturesPraticien()
    {
        return $this->hasMany(Facture::class, 'PRCLEUNIK', 'PRCLEUNIK');
    }

    public function factures()
    {
        return $this->hasMany(Facture::class, 'PRCLEUNIK', 'PRCLEUNIK');
    }

    public function feuillesMaladie()
    {
        return $this->hasMany(FeuilMa::class, 'PRCLEUNIK', 'PRCLEUNIK');
    }

    public function conventions()
    {
        return $this->hasMany(Convention::class, 'id_praticien', 'PRCLEUNIK');
    }
}
