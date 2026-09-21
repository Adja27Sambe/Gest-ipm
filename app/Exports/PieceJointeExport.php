<?php

namespace App\Exports;

use App\Models\PieceJointe;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PieceJointeExport implements FromQuery, WithHeadings, WithMapping
{
    protected $id_categorie;

    public function __construct($id_categorie = null)
    {
        $this->id_categorie = $id_categorie;
    }

    public function query()
    {
        $query = PieceJointe::query()->with(['categorie', 'utilisateur', 'attachable'])->latest('date_ajout');

        if ($this->id_categorie) {
            $query->where('id_categorie', $this->id_categorie);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Nom du Fichier',
            'Type de Fichier',
            'Catégorie',
            'Date d\'Ajout',
            'Ajouté Par',
            'Lié À (Type)',
        ];
    }

    public function map($piece): array
    {
        $typeAssocie = 'Inconnu';
        if ($piece->attachable) {
            $classBaseName = class_basename($piece->attachable_type);
            $typeAssocie = $classBaseName;
        }

        return [
            $piece->nom_fichier,
            $piece->type_fichier,
            $piece->categorie->libelle ?? 'Non classé',
            \Carbon\Carbon::parse($piece->date_ajout)->format('d/m/Y H:i'),
            $piece->utilisateur->nom ?? 'Système',
            $typeAssocie,
        ];
    }
}
