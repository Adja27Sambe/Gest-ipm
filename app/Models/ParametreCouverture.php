<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParametreCouverture extends Model
{
    use \App\Traits\Auditable;
    protected $table = 'parametre_couverture';
    protected $primaryKey = 'id_parametre';
    protected $guarded = [];

    protected $casts = [
        'montant_referentiel' => 'decimal:2',
        'part_ipm_reference' => 'decimal:2',
        'ticket_moderateur' => 'decimal:2',
        'montant_plafond' => 'decimal:2',
        'montant_forfait' => 'decimal:2',
        'tarif_journalier' => 'decimal:2',
        'taux_participant' => 'decimal:2',
        'est_exclusion' => 'boolean',
    ];

    public function typePrestation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(TypePrestation::class, 'id_type_prestation', 'id_type_prestation');
    }

    public function typeActe(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(TypeActe::class, 'id_type_acte', 'id_type_acte');
    }

    /**
     * Récupère la part IPM de référence paramétrée ou la calcule automatiquement
     */
    public function getPartIpmReferenceCalculeeAttribute(): float
    {
        if ($this->type_calcul !== 'MONTANT_REFERENTIEL') {
            return 0.0;
        }

        if ($this->part_ipm_reference !== null && (float)$this->part_ipm_reference > 0) {
            return (float)$this->part_ipm_reference;
        }

        if ($this->montant_referentiel && $this->taux_prise_charge) {
            return round((float)$this->montant_referentiel * ((float)$this->taux_prise_charge / 100), 2);
        }

        return 0.0;
    }
}
