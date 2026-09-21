<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\MapsIntersecColumns;

class FeuilMa extends Model
{
    use MapsIntersecColumns;

    protected $table = 'FEUIL_MA';
    protected $primaryKey = 'FECLEUNIK';
    public $timestamps = false;
    protected $guarded = [];
    protected $keyType = 'int';

    protected $columnMap = [
        'numero_feuille'      => 'Numero_feuille',
        'id_participant'      => 'PACLEUNIK',
        'id_entreprise'       => 'ADCLEUNIK',
        'nom_participant'     => 'NOMPARTICIPANT',
        'num_malade'          => 'NumMalade',
        'nom_malade'          => 'NOMMALADE',
        'type_malade'         => 'TypeMalade',
        'date_feuille'        => 'DateFeuille',
        'created_at'          => 'DateFeuille',
        'id_praticien'        => 'PRCLEUNIK',
        'nom_praticien'       => 'NOMPRATICIEN',
        'type_feuille'        => 'TYPE_FEUILLE',
        'id_facture'          => 'F0CLEUNIK',
        'numero_facture'      => 'NUMERO_FACTURE',
        'facture'             => 'FACTURE',
        'observation'         => 'OBSERVATION',
        'id_fact_adh'         => 'F1CLEUNIK',
        'id_decompte_par'     => 'IDDECOMPTE_PAR',
        'montant_pansement'   => 'Montant_Pansement',
        'date_ordonnance'     => 'DATE_ORD',
        'id_operateur'        => 'IDOPERATEUR',
        'id_specialite'       => 'IDSpecialite',
        'date_prestation'     => 'Date_Prestation',
        'heure_saisie'        => 'Heure_Saisie',
        'montant'             => 'MONTANT',
        'part_ipm'            => 'PARTIPM',
        'part_participant'    => 'PARTPARTICIPANT',
        'num_feuille_legacy'  => 'NUMFEUILLE',
        'malade_bis'          => 'MALADEBIS',
        'lettre_garantie'     => 'LG',
    ];

    public function salarie()
    {
        return $this->belongsTo(Salarie::class, 'PACLEUNIK', 'IDPARTICIPANT');
    }

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class, 'ADCLEUNIK', 'IDADHERANT');
    }

    public function praticien()
    {
        return $this->belongsTo(Praticien::class, 'PRCLEUNIK', 'PRCLEUNIK');
    }

    public function actes()
    {
        return $this->hasMany(ActeFeuille::class, 'FECLEUNIK', 'FECLEUNIK');
    }

    public function getDateDemandeAttribute()
    {
        return $this->getAttribute('date_feuille');
    }

    public function getStatutAttribute(): string
    {
        if ($this->getAttribute('facture') == 1) return 'facturee';
        return 'en_attente';
    }
}
