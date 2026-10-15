<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TypeActe extends Model
{
    use \App\Traits\Auditable;

    protected $table = 'type_acte';
    protected $primaryKey = 'id_type_acte';
    protected $guarded = [];

    public function typePrestation(): BelongsTo
    {
        return $this->belongsTo(TypePrestation::class, 'id_type_prestation', 'id_type_prestation');
    }

    /**
     * Recherche ou création automatique d'un type d'acte pour un acte parent,
     * avec gestion stricte anti-doublon (insensible à la casse et aux espaces superflus).
     */
    public static function findOrCreateClean(int $idTypePrestation, string $libelle, ?string $description = null): self
    {
        $cleanLibelle = trim(preg_replace('/\s+/', ' ', $libelle));
        if (empty($cleanLibelle)) {
            throw new \InvalidArgumentException("Le libellé du type d'acte ne peut pas être vide.");
        }

        $existing = static::where('id_type_prestation', $idTypePrestation)
            ->whereRaw('LOWER(TRIM(libelle)) = ?', [mb_strtolower($cleanLibelle, 'UTF-8')])
            ->first();

        if ($existing) {
            return $existing;
        }

        return static::create([
            'id_type_prestation' => $idTypePrestation,
            'libelle' => $cleanLibelle,
            'description' => $description ?? $cleanLibelle,
            'statut' => 'actif',
        ]);
    }
}
