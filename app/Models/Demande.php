<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Demande extends Model
{
    use \App\Traits\Auditable;
    use \App\Traits\HasPiecesJointes;

    protected $table = 'demande';
    protected $primaryKey = 'id_demande';
    protected $guarded = [];

    // Constantes pour les types de demandes (à adapter si vous utilisez id_type_demande ou un slug)
    public const TYPE_BON_COMMANDE = 'bon_commande';
    public const TYPE_FEUILLE_MALADIE = 'feuille_maladie';
    public const TYPE_LETTRE_GARANTIE = 'lettre_garantie';

    public function typeDemande()
    {
        return $this->belongsTo(TypeDemande::class, 'id_type_demande', 'id_type_demande');
    }

    public function salarie()
    {
        return $this->belongsTo(Salarie::class, 'id_salarie', 'id_salarie');
    }

    public function ayantDroit()
    {
        return $this->belongsTo(AyantDroit::class, 'id_ayant_droit', 'id_ayant_droit');
    }

    public function bonCommande()
    {
        return $this->hasOne(BonCommande::class, 'id_demande', 'id_demande');
    }

    public function feuilleMaladie()
    {
        return $this->hasOne(FeuilleMaladie::class, 'id_demande', 'id_demande');
    }

    public function lettreGarantie()
    {
        return $this->hasOne(LettreGarantie::class, 'id_demande', 'id_demande');
    }

    public function praticien()
    {
        return $this->belongsTo(Praticien::class, 'id_praticien', 'id_praticien');
    }

    public function pharmacie()
    {
        return $this->belongsTo(Pharmacie::class, 'id_pharmacie', 'id_pharmacie');
    }

    public function typePrestation()
    {
        return $this->belongsTo(TypePrestation::class, 'id_type_prestation', 'id_type_prestation');
    }

    /**
     * Génère un numéro de demande unique (ex: BC2026070001).
     *
     * @param string $prefix
     * @param string $column
     * @return string
     */
    public static function generateNumber($prefix = 'DEM', $column = 'numero_demande')
    {
        $yearMonth = date('Ym');
        $lastDemande = self::where($column, 'like', $prefix . $yearMonth . '%')
                           ->orderBy('id_demande', 'desc')
                           ->first();
                           
        if (!$lastDemande || empty($lastDemande->{$column})) {
            $sequence = 1;
        } else {
            $lastNumber = str_replace($prefix . $yearMonth, '', $lastDemande->{$column});
            $sequence = intval($lastNumber) + 1;
        }

        return $prefix . $yearMonth . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
