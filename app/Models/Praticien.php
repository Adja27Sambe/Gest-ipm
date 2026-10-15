<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\MapsIntersecColumns;

class Praticien extends Model
{
    use MapsIntersecColumns;
    use \App\Traits\Auditable;
    use \App\Traits\HasPiecesJointes;

    protected $table = 'PRATICIE';
    protected $primaryKey = 'PRCLEUNIK';
    public $timestamps = false;
    protected $guarded = [];
    protected $keyType = 'int';

    protected static function booted()
    {
        static::creating(function ($praticien) {
            if (empty($praticien->code_praticien) && empty($praticien->CODEPRAT)) {
                $praticien->CODEPRAT = static::generateNextCodePraticien();
            }
            if (empty($praticien->date_enregistrement) && empty($praticien->DATE_ENREGISTREMENT)) {
                $praticien->DATE_ENREGISTREMENT = now()->toDateString();
            }
        });
    }

    public static function generateNextCodePraticien(): string
    {
        $maxNum = static::all()
            ->pluck('code_praticien')
            ->map(function ($code) {
                return intval(preg_replace('/[^0-9]/', '', (string)$code));
            })
            ->max() ?? 0;

        $next = $maxNum + 1;
        while (static::where('CODEPRAT', $next)->orWhere('CODEPRAT', 'PRAT-' . str_pad($next, 4, '0', STR_PAD_LEFT))->exists()) {
            $next++;
        }
        return 'PRAT-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    protected $columnMap = [
        'id'                  => 'PRCLEUNIK',
        'id_praticien'        => 'PRCLEUNIK',
        'code_praticien'      => 'CODEPRAT',
        'nom'                 => 'NOMPRAT',
        'adresse'             => 'ADRPRAT',
        'telephone'           => 'TELPRAT',
        'email'               => 'EMAILPRAT',
        'specialite'          => 'SPECIALITE',
        'contact'             => 'CONTACT',
        'tel_contact'         => 'TELCONTACT',
        'id_specialite'       => 'SPCLEUNIK',
        'montant_acte'        => 'MONTACT',
        'description_acte'    => 'DESACTE',
        'compte_comptable'    => 'COMPTE_COMPTABLE',
        'code_comptable'      => 'COMPTE_COMPTABLE',
        'a_convention'        => 'A_CONVENTION',
        'date_enregistrement' => 'DATE_ENREGISTREMENT',
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
