<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prestation extends Model
{
    use \App\Traits\Auditable;

    protected $table = 'prestation';
    protected $primaryKey = 'id_prestation';
    
    // Pour l'observer, on s'assure qu'Eloquent lève bien les events même sans timestamps par défaut,
    // Mais on va ajouter les timestamps dans une migration.
    protected $guarded = [];

    protected $casts = [
        'date_prestation' => 'date',
        'montant' => 'decimal:2',
        'taux_prise_charge' => 'decimal:2',
        'reste_a_charge' => 'decimal:2',
    ];

    public function typePrestation()
    {
        return $this->belongsTo(TypePrestation::class, 'id_type_prestation', 'id_type_prestation');
    }

    public function praticien()
    {
        return $this->belongsTo(Praticien::class, 'id_praticien', 'id_praticien');
    }

    public function pharmacie()
    {
        return $this->belongsTo(Pharmacie::class, 'id_pharmacie', 'id_pharmacie');
    }

    public function getPartenaireAttribute()
    {
        return $this->praticien ?? $this->pharmacie;
    }

    public function demande()
    {
        return $this->belongsTo(Demande::class, 'id_demande', 'id_demande');
    }

    public function factures()
    {
        return $this->belongsToMany(Facture::class, 'facture_prestation', 'id_prestation', 'id_facture')->withTimestamps();
    }
}
