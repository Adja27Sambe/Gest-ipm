<?php

namespace App\Imports;

use App\Models\Salarie;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class SalarieImport implements ToModel, WithHeadingRow
{
    protected $idEntreprise;

    public function __construct($idEntreprise)
    {
        $this->idEntreprise = $idEntreprise;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Skip empty rows
        if (!isset($row['nom']) || !isset($row['prenom'])) {
            return null;
        }

        return new Salarie([
            'id_entreprise' => $this->idEntreprise,
            'matricule' => $row['matricule'] ?? null,
            'prenom' => $row['prenom'],
            'nom' => $row['nom'],
            'date_naissance' => $this->parseDate($row['date_naissance'] ?? null),
            'lieu_naissance' => $row['lieu_naissance'] ?? null,
            'sexe' => $row['sexe'] ?? null,
            'telephone' => $row['telephone'] ?? null,
            'adresse' => $row['adresse'] ?? null,
            'situation_matrimoniale' => $row['situation_matrimoniale'] ?? null,
            'salaire' => isset($row['salaire']) ? (float) $row['salaire'] : 0,
            'actif' => 1,
            'date_embauche' => $this->parseDate($row['date_embauche'] ?? null),
        ]);
    }

    private function parseDate($value)
    {
        if (!$value) return null;
        try {
            // Excel dates are often numeric
            if (is_numeric($value)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
