<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\MapsIntersecColumns;

class Cotisation extends Model
{
    use MapsIntersecColumns;

    protected $table = 'COTISE';
    protected $primaryKey = 'COCLEUNIK';
    public $timestamps = false;
    protected $guarded = [];
    protected $keyType = 'int';

    protected $columnMap = [
        'id'                => 'COCLEUNIK',
        'id_cotisation'     => 'COCLEUNIK',
        'id_entreprise'     => 'ADCLEUNIK',
        'mois'              => 'MOISCOTISE',
        'annee'             => 'ANNEECOTISE',
        'date_cotisation'   => 'DATECOTISE',
        'montant_salarie'   => 'MTCOTISESAL',
        'masse_salariale'   => 'MTCOTISESAL',
        'montant'           => 'MTCOTISE',
        'montant_regle'     => 'MTREGLE',
        'nb_plancher'       => 'NBPLANCHER',
        'nb_plafond'        => 'NBPLAFOND',
        'nb_total'          => 'NBTOTAL',
        'nb_intermed'       => 'NBINTERMED',
        'mt_plafond'        => 'MTPLAFOND',
        'mt_plancher'       => 'MTPLANCHER',
        'taux'              => 'TAUX',
        'mt_intermed'       => 'MTINTERMED',
        'nb_mois'           => 'NBMOIS',
        'mois_fin'          => 'MOISFINCOTSE',
        'annee_fin'         => 'ANNEEFINCOTISE',
        'type'              => 'TYPECOTISE',
        'commentaire'       => 'COMMENTAIRE',
    ];

    /**
     * Période formatée (accessor de compatibilité).
     */
    public function getPeriodeAttribute(): string
    {
        $mois = $this->getAttribute('mois');
        $annee = $this->getAttribute('annee');
        return str_pad($mois, 2, '0', STR_PAD_LEFT) . '/' . $annee;
    }

    /**
     * Statut calculé.
     */
    public function getStatutAttribute(): string
    {
        $montant = floatval($this->getAttribute('montant') ?? 0);
        $regle = floatval($this->getAttribute('montant_regle') ?? 0);
        return $regle >= $montant ? 'payee' : 'impayee';
    }

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class, 'ADCLEUNIK', 'IDADHERANT');
    }

    public function reglements()
    {
        return $this->hasMany(ReglementCotisation::class, 'COCLEUNIK', 'COCLEUNIK');
    }

    public function cotisationsParticipants()
    {
        return $this->hasMany(CotisationParticipant::class, 'COCLEUNIK', 'COCLEUNIK');
    }
}
