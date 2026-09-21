<?php

namespace Database\Seeders;

use App\Models\CategorieDocument;
use Illuminate\Database\Seeder;

class CategorieDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Carte Nationale d\'Identité (CNI)',
            'Passeport',
            'Extrait de Naissance',
            'Certificat de Mariage',
            'Photo d\'identité',
            'Certificat de Scolarité',
            'Certificat de Vie et d\'Entretien',
            'Contrat de Travail',
            'Bulletin de Salaire',
            'Ordonnance Médicale',
            'Facture de Soins',
            'Billet d\'Hôpital',
            'Autre Document'
        ];

        foreach ($categories as $categorie) {
            CategorieDocument::firstOrCreate([
                'libelle' => $categorie
            ]);
        }
    }
}
