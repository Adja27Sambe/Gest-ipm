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

    public function prestataire()
    {
        return $this->belongsTo(Prestataire::class, 'id_prestataire', 'id_prestataire');
    }
}
