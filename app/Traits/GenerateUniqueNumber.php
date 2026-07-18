<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait GenerateUniqueNumber
{
    /**
     * Génère un numéro unique avec un préfixe et l'année courante.
     * Exemple : BC-2026-0001
     *
     * @param string $prefix Le préfixe (ex: 'BC', 'FM', 'LG')
     * @param string $tableName Le nom de la table
     * @param string $columnName Le nom de la colonne
     * @return string
     */
    public function generateUniqueNumber(string $prefix, string $tableName, string $columnName): string
    {
        $year = date('Y');
        
        // Trouver le dernier numéro de l'année en cours
        $lastRecord = DB::table($tableName)
            ->where($columnName, 'LIKE', "{$prefix}-{$year}-%")
            ->orderBy($columnName, 'desc')
            ->first();

        if (!$lastRecord) {
            $number = 1;
        } else {
            // Extraire le numéro (les 4 derniers chiffres)
            $parts = explode('-', $lastRecord->$columnName);
            $number = (int) end($parts) + 1;
        }

        // Formater sur 4 chiffres : ex: 0001, 0012
        return sprintf("%s-%s-%04d", $prefix, $year, $number);
    }
}
