<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\MapsIntersecColumns;

class CommandePharmacie extends Model
{
    use MapsIntersecColumns;

    protected $table = 'CMDPHARM';
    protected $primaryKey = 'CMCLEUNIK';
    public $timestamps = false;
    protected $guarded = [];
    protected $keyType = 'int';

    protected $columnMap = [
        'numero_commande'   => 'NUMCOMMANDE',
        'date_commande'     => 'DATECMD',
        'nb_articles'       => 'NBARTICLES',
        'id_pharmacie'      => 'PHCLEUNIK',
        'nom_pharmacie'     => 'NOMPHARMACIE',
        'id_participant'    => 'PACLEUNIK',
        'id_entreprise'     => 'ADCLEUNIK',
        'nom_participant'   => 'NOMPARTICIPANT',
        'num_malade'        => 'NumMalade',
        'nom_malade'        => 'NOMMALADE',
        'medecin'           => 'MEDECIN',
        'date_ordonnance'   => 'DATE_ORD',
        'type_commande'     => 'TYPE_COMMANDE',
        'id_facture'        => 'FACLEUNIK',
        'numero_facture'    => 'NUMERO_FACTURE',
        'facture'           => 'FACTURE',
        'observation'       => 'OBSERVATION',
        'date_prestation'   => 'Date_Prestation',
        'id_operateur'      => 'IDOPERATEUR',
        'montant'           => 'MONTANT',
        'part_ipm'          => 'PARTIPM',
        'part_participant'  => 'PARTPARTICIPANT',
        'id_specialite'     => 'IDSpecialite',
        'id_praticien'      => 'PRCLEUNIK',
        'malade_bis'        => 'MALADEBIS',
    ];

    public function salarie()
    {
        return $this->belongsTo(Salarie::class, 'PACLEUNIK', 'IDPARTICIPANT');
    }

    public function pharmacie()
    {
        return $this->belongsTo(Pharmacie::class, 'PHCLEUNIK', 'PHCLEUNIK');
    }

    public function lignes()
    {
        return $this->hasMany(LigneCommande::class, 'CMCLEUNIK', 'CMCLEUNIK');
    }

    public function getDateDemandeAttribute()
    {
        return $this->getAttribute('date_commande');
    }
}
