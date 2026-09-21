<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\CarteAssure;
use App\Models\AyantDroit;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class IntersecSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('fr_FR');
        $mysql = DB::connection('mysql');
        
        $mysql->statement('SET SESSION sql_mode=""');
        $mysql->statement('SET FOREIGN_KEY_CHECKS=0');

        $this->command->info('Truncating INTERSEC tables...');
        $mysql->table('FACT_PHA')->truncate();
        $mysql->table('FACT_PRA')->truncate();
        $mysql->table('COT_PAR')->truncate();
        $mysql->table('COTISE')->truncate();
        $mysql->table('CMDPHARM')->truncate();
        $mysql->table('FEUIL_MA')->truncate();
        $mysql->table('PARTICIPANT')->truncate();
        $mysql->table('ADHERANT')->truncate();
        $mysql->table('PRATICIE')->truncate();
        $mysql->table('PHARMACI')->truncate();
        $mysql->table('SPECIALI')->truncate();
        $mysql->table('carte_assure')->truncate();
        $mysql->table('ayant_droit')->truncate();

        $mysql->statement('SET FOREIGN_KEY_CHECKS=1');

        $this->command->info('Seeding Spécialités (SPECIALI)...');
        $specialites = [
            1 => ['libelle' => 'Médecine Générale', 'code' => 'GEN'],
            2 => ['libelle' => 'Pédiatrie', 'code' => 'PED'],
            3 => ['libelle' => 'Gynécologie', 'code' => 'GYN'],
            4 => ['libelle' => 'Chirurgie Dentaire', 'code' => 'DEN'],
            5 => ['libelle' => 'Ophtalmologie', 'code' => 'OPH'],
        ];
        foreach ($specialites as $id => $spec) {
            $mysql->table('SPECIALI')->insert([
                'SPCLEUNIK' => $id,
                'Specialite' => $spec['libelle'],
                'CodeSpecialite' => $spec['code'],
                'COMPTE_COMPTABLE' => '60100' . $id,
            ]);
        }

        $this->command->info('Seeding Praticiens (PRATICIE)...');
        $praticienIds = [];
        $praticiensData = [
            ['nom' => 'Dr. Diallo Mamadou', 'specialite' => 1, 'tel' => '771234567'],
            ['nom' => 'Dr. Ndiaye Fatou', 'specialite' => 2, 'tel' => '772345678'],
            ['nom' => 'Dr. Sow Ibrahima', 'specialite' => 3, 'tel' => '773456789'],
            ['nom' => 'Dr. Kane Awa', 'specialite' => 4, 'tel' => '774567890'],
        ];
        foreach ($praticiensData as $i => $prat) {
            $id = $i + 1;
            $mysql->table('PRATICIE')->insert([
                'PRCLEUNIK' => $id,
                'CODEPRAT' => $id,
                'NOMPRAT' => $prat['nom'],
                'ADRPRAT' => substr($faker->address(), 0, 100),
                'TELPRAT' => $prat['tel'],
                'SPCLEUNIK' => $prat['specialite'],
                'SPECIALITE' => $prat['specialite'],
                'COMPTE_COMPTABLE' => '40100' . $id,
            ]);
            $praticienIds[] = $id;
        }

        $this->command->info('Seeding Pharmacies (PHARMACI)...');
        $pharmacieIds = [];
        $pharmaciesData = [
            ['nom' => 'Pharmacie Guigon', 'code' => 'PH-01', 'tel' => '338210001'],
            ['nom' => 'Pharmacie de la Nation', 'code' => 'PH-02', 'tel' => '338210002'],
            ['nom' => 'Pharmacie Almadies', 'code' => 'PH-03', 'tel' => '338210003'],
        ];
        foreach ($pharmaciesData as $i => $ph) {
            $id = $i + 1;
            $mysql->table('PHARMACI')->insert([
                'PHCLEUNIK' => $id,
                'CODEPHARM' => $ph['code'],
                'NOMPHARM' => $ph['nom'],
                'ADRPHARM' => substr($faker->address(), 0, 100),
                'TELPHARM' => $ph['tel'],
                'COMPTE_COMPTABLE' => '40200' . $id,
            ]);
            $pharmacieIds[] = $id;
        }

        $this->command->info('Seeding Entreprises (ADHERANT)...');
        $entrepriseIds = [];
        $entreprisesData = [
            ['nom' => 'SONATEL SA', 'code' => 'ENT-001', 'email' => 'contact@sonatel.sn'],
            ['nom' => 'TOTAL Energies Sénégal', 'code' => 'ENT-002', 'email' => 'rh@totalenergies.sn'],
            ['nom' => 'SDE Sénégalaise des Eaux', 'code' => 'ENT-003', 'email' => 'contact@sde.sn'],
            ['nom' => 'SENELEC', 'code' => 'ENT-004', 'email' => 'info@senelec.sn'],
            ['nom' => 'BICIS Groupe BNP Paribas', 'code' => 'ENT-005', 'email' => 'rh@bicis.sn'],
        ];
        foreach ($entreprisesData as $i => $ent) {
            $id = $i + 1;
            $mysql->table('ADHERANT')->insert([
                'IDADHERANT' => $id,
                'CODEADHERANT' => $ent['code'],
                'ADHERANT' => $ent['nom'],
                'Adresse' => substr($faker->address(), 0, 50),
                'TEL' => '338' . rand(100000, 999999),
                'Email' => $ent['email'],
                'ADACTIF' => 1,
                'COMPTE_COMPTABLE' => '41100' . $id,
            ]);
            $entrepriseIds[] = $id;
        }

        $this->command->info('Seeding Salaries (PARTICIPANT) + Cartes + Ayants Droit...');
        $salarieIds = [];
        $participantIdCounter = 1;
        $ayantDroitCounter = 1;
        
        foreach ($entrepriseIds as $idEntreprise) {
            $nbSalaries = rand(3, 5);
            for ($j = 1; $j <= $nbSalaries; $j++) {
                $idParticipant = $participantIdCounter++;
                $nom = $faker->lastName();
                $prenom = $faker->firstName();
                $matricule = 'MAT-' . str_pad($idParticipant, 4, '0', STR_PAD_LEFT);
                $sexe = $faker->randomElement([1, 2]);

                $mysql->table('PARTICIPANT')->insert([
                    'IDPARTICIPANT' => $idParticipant,
                    'CODE_PARTICIPANT' => $idParticipant,
                    'IDADHERANT' => $idEntreprise,
                    'NOM' => substr($nom, 0, 60),
                    'PRENOM' => substr($prenom, 0, 40),
                    'NOMPREN' => substr($prenom . ' ' . $nom, 0, 50),
                    'MATRICULE' => $matricule,
                    'Date_Naissance' => $faker->dateTimeBetween('-50 years', '-22 years')->format('Y-m-d'),
                    'TEL' => '77' . rand(1000000, 9999999),
                    'Email' => strtolower($prenom . '.' . $nom . '@example.sn'),
                    'PARACTIF' => 1,
                    'SEXE' => $sexe,
                    'SITUATION_MATRIMONIALE' => $faker->randomElement([1, 2]),
                    'SALAIRE' => rand(250000, 850000),
                    'DATE_IMMATRICULATION' => $faker->dateTimeBetween('-4 years', 'now')->format('Y-m-d'),
                ]);
                $salarieIds[] = $idParticipant;

                // Création Carte Assuré avec QR Code
                $numCarte = sprintf("IPM-%s-%04d", date('Y'), $idParticipant);
                $qrData = json_encode([
                    'numero' => $numCarte,
                    'matricule' => $matricule,
                    'id_salarie' => $idParticipant,
                ]);
                $qrCodeSvg = (string) QrCode::size(200)->generate($qrData);

                $mysql->table('carte_assure')->insert([
                    'id_salarie' => $idParticipant,
                    'numero_carte' => $numCarte,
                    'matricule' => $matricule,
                    'date_emission' => now(),
                    'qr_code' => $qrCodeSvg,
                    'statut' => CarteAssure::STATUT_ACTIF,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Création de 1 ou 2 ayants droit
                $nbAyants = rand(1, 2);
                for ($a = 1; $a <= $nbAyants; $a++) {
                    $mysql->table('ayant_droit')->insert([
                        'id_ayant_droit' => $ayantDroitCounter++,
                        'id_salarie' => $idParticipant,
                        'nom' => $nom,
                        'prenom' => $faker->firstName(),
                        'lien_parente' => $a === 1 ? 'Conjoint' : 'Enfant',
                        'date_naissance' => $faker->dateTimeBetween('-20 years', '-2 years')->format('Y-m-d'),
                        'sexe' => $faker->randomElement(['M', 'F']),
                        'statut' => 'actif',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        $this->command->info('Seeding Cotisations (COTISE et COT_PAR)...');
        $cotisationIdCounter = 1;
        foreach ($entrepriseIds as $idEntreprise) {
            for ($c = 1; $c <= 2; $c++) {
                $idCotisation = $cotisationIdCounter++;
                $mois = $faker->dateTimeBetween('-4 months', 'now')->format('m');
                $annee = date('Y');
                
                $montantCotise = rand(100000, 350000);
                $montantRegle = $faker->randomElement([$montantCotise, $montantCotise, 0]);
                
                $mysql->table('COTISE')->insert([
                    'COCLEUNIK' => $idCotisation,
                    'ADCLEUNIK' => $idEntreprise,
                    'MOISCOTISE' => (int)$mois,
                    'ANNEECOTISE' => (int)$annee,
                    'MTCOTISE' => $montantCotise,
                    'MTREGLE' => $montantRegle,
                    'DATECOTISE' => $annee . '-' . $mois . '-05'
                ]);

                $salaries = $mysql->table('PARTICIPANT')->where('IDADHERANT', $idEntreprise)->get();
                $partParSalarie = count($salaries) > 0 ? $montantCotise / count($salaries) : $montantCotise;
                foreach ($salaries as $salarie) {
                    $mysql->table('COT_PAR')->insert([
                        'C0CLEUNIK' => rand(10000, 99999) . $salarie->IDPARTICIPANT,
                        'COCLEUNIK' => $idCotisation,
                        'PACLEUNIK' => $salarie->IDPARTICIPANT,
                        'MTCOTISE' => $partParSalarie,
                        'DATECOTISE' => $annee . '-' . $mois . '-05',
                    ]);
                }
            }
        }

        $this->command->info('Seeding Factures (FACT_PRA et FACT_PHA)...');
        $facturaPraCounter = 1;
        foreach ($praticienIds as $praticien) {
            for ($f = 1; $f <= 2; $f++) {
                $idFacture = $facturaPraCounter++;
                $montant = rand(15000, 45000);
                $regle = $f === 1 ? $montant : 0;
                $mysql->table('FACT_PRA')->insert([
                    'F0CLEUNIK' => $idFacture,
                    'PRCLEUNIK' => $praticien,
                    'DATE_FACTURE' => $faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
                    'NUMERO_FACTURE' => 'FPR-' . str_pad($idFacture, 5, '0', STR_PAD_LEFT),
                    'MONTANT_FACTURE' => $montant,
                    'MONTANT_REGLE' => $regle,
                    'MONTANT_RESTANT' => $montant - $regle,
                ]);
            }
        }
        
        $facturaPhaCounter = 1;
        foreach ($pharmacieIds as $pharmacie) {
            for ($f = 1; $f <= 2; $f++) {
                $idFacture = $facturaPhaCounter++;
                $montant = rand(25000, 80000);
                $regle = $f === 1 ? $montant : 0;
                $mysql->table('FACT_PHA')->insert([
                    'FACLEUNIK' => $idFacture,
                    'PHCLEUNIK' => $pharmacie,
                    'DATE_FACTURE' => $faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
                    'NUMERO_FACTURE' => 'FPH-' . str_pad($idFacture, 5, '0', STR_PAD_LEFT),
                    'MONTANT_FACTURE' => $montant,
                    'MONTANT_REGLE' => $regle,
                    'MONTANT_RESTANT' => $montant - $regle,
                ]);
            }
        }

        $this->command->info('Seeding Feuilles de Maladie (FEUIL_MA)...');
        for ($fm = 1; $fm <= 8; $fm++) {
            $randomSalarieId = $faker->randomElement($salarieIds);
            $randomPraticienId = $faker->randomElement($praticienIds);
            $salarie = $mysql->table('PARTICIPANT')->where('IDPARTICIPANT', $randomSalarieId)->first();
            $prat = $mysql->table('PRATICIE')->where('PRCLEUNIK', $randomPraticienId)->first();

            $mysql->table('FEUIL_MA')->insert([
                'FECLEUNIK' => $fm,
                'Numero_feuille' => 'FM-' . str_pad($fm, 5, '0', STR_PAD_LEFT),
                'PACLEUNIK' => $randomSalarieId,
                'ADCLEUNIK' => $salarie ? $salarie->IDADHERANT : 1,
                'NOMPARTICIPANT' => $salarie ? $salarie->NOMPREN : 'Participant',
                'PRCLEUNIK' => $randomPraticienId,
                'NOMPRATICIEN' => $prat ? $prat->NOMPRAT : 'Praticien',
                'DateFeuille' => $faker->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
                'MONTANT' => rand(10000, 30000),
                'PARTIPM' => rand(8000, 24000),
                'PARTPARTICIPANT' => rand(2000, 6000),
                'FACTURE' => 0,
            ]);
        }

        $this->command->info('INTERSEC database seeded successfully with complete relational integrity!');
    }
}
