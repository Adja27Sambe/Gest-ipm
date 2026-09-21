<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\TypePrestation;
use App\Models\ParametreCouverture;
use App\Models\Demande;
use App\Models\TypeDemande;
use App\Models\Entreprise;
use App\Models\Salarie;
use App\Models\Praticien;
use App\Models\Pharmacie;
use App\Services\PlafondService;
use App\Services\DemandeService;
use App\Exceptions\PlafondDepasseException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DemoSeeder;

class ActesEtTauxCouvertureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoSeeder::class);
    }

    public function test_all_official_actes_and_taux_are_correctly_configured()
    {
        $expected = [
            'Médecine Générale' => ['taux' => '75', 'ticket' => '25.00'],
            'Spécialité Médicale' => ['taux' => '70', 'ticket' => '30.00'],
            'Consultation + Soins Dentaires' => ['taux' => '50', 'ticket' => '50.00'],
            'Analyses Médicales' => ['taux' => '65', 'ticket' => '35.00'],
            'Radiologie' => ['taux' => '65', 'ticket' => '35.00'],
            'Pharmacie' => ['taux' => '75', 'ticket' => '25.00'],
            'Hospitalisation' => ['taux' => '70', 'plafond_acte' => 20000.00],
            'Optique Médicale' => ['plafond_acte' => 30000.00],
            'Maternité / Accouchement' => ['plafond_acte' => 50000.00],
        ];

        foreach ($expected as $libelle => $rules) {
            $type = TypePrestation::where('libelle', $libelle)->first();
            $this->assertNotNull($type, "Le type de prestation '{$libelle}' doit exister.");

            $param = ParametreCouverture::where('id_type_prestation', $type->id_type_prestation)->first();
            $this->assertNotNull($param, "Le paramètre de couverture pour '{$libelle}' doit exister.");

            if (isset($rules['taux'])) {
                $this->assertEquals($rules['taux'], (string)$param->taux_prise_charge);
            }
            if (isset($rules['ticket'])) {
                $this->assertEquals($rules['ticket'], (string)$param->ticket_moderateur);
            }
            if (isset($rules['plafond_acte'])) {
                $this->assertEquals($rules['plafond_acte'], (float)$param->plafond_par_acte);
            }
        }
    }

    public function test_only_hospitalisation_optique_and_maternite_have_ceilings()
    {
        $allParams = ParametreCouverture::with('typePrestation')->get();

        foreach ($allParams as $param) {
            $libelle = $param->typePrestation->libelle;

            if (str_contains(strtolower($libelle), 'hospitalisation')) {
                $this->assertEquals(20000.00, (float)$param->plafond_par_acte, "Hospitalisation doit avoir un plafond de 20 000 FCFA.");
            } elseif (str_contains(strtolower($libelle), 'optique')) {
                $this->assertEquals(30000.00, (float)$param->plafond_par_acte, "Optique doit avoir un plafond de 30 000 FCFA.");
            } elseif (str_contains(strtolower($libelle), 'maternité') || str_contains(strtolower($libelle), 'accouchement')) {
                $this->assertEquals(50000.00, (float)$param->plafond_par_acte, "Maternité doit avoir un plafond de 50 000 FCFA.");
            } else {
                $this->assertNull($param->plafond_par_acte, "L'acte '{$libelle}' ne doit pas avoir de plafond par acte (doit être null).");
                $this->assertNull($param->plafond_annuel, "L'acte '{$libelle}' ne doit pas avoir de plafond annuel (doit être null).");
            }
        }
    }

    public function test_acts_without_ceilings_allow_any_amount()
    {
        $typeMedGen = TypePrestation::where('libelle', 'like', '%Médecine Générale%')->first();
        $entreprise = Entreprise::create(['raison_sociale' => 'Entreprise Test']);
        $salarie = Salarie::create(['id_entreprise' => $entreprise->id_entreprise, 'nom' => 'Diallo', 'prenom' => 'Amadou', 'statut' => 'actif']);
        $praticien = Praticien::create(['nom' => 'Dr. Ndiaye']);
        $demande = Demande::create([
            'numero_demande' => 'FM-TEST-MED',
            'id_salarie' => $salarie->id_salarie,
            'id_type_demande' => 2,
            'id_praticien' => $praticien->id_praticien,
            'date_demande' => now(),
            'statut' => 'Approuvée',
        ]);

        $plafondService = app(PlafondService::class);

        // Médecine Générale n'a aucun plafond : un montant élevé doit passer sans exception
        $plafondService->checkPlafonds(500000, $typeMedGen->id_type_prestation, $demande->id_demande);
        $this->assertTrue(true);
    }

    public function test_hospitalisation_plafond_20000_is_enforced()
    {
        $typeHos = TypePrestation::where('libelle', 'like', '%Hospitalisation%')->first();
        $entreprise = Entreprise::create(['raison_sociale' => 'Entreprise Test']);
        $salarie = Salarie::create(['id_entreprise' => $entreprise->id_entreprise, 'nom' => 'Kane', 'prenom' => 'Awa', 'statut' => 'actif']);
        $praticien = Praticien::create(['nom' => 'Clinique Principale']);
        $demande = Demande::create([
            'numero_demande' => 'LG-TEST-HOS',
            'id_salarie' => $salarie->id_salarie,
            'id_type_demande' => 3,
            'id_praticien' => $praticien->id_praticien,
            'date_demande' => now(),
            'statut' => 'Approuvée',
        ]);

        $plafondService = app(PlafondService::class);

        // 20 000 FCFA doit être accepté (égal au plafond)
        $plafondService->checkPlafonds(20000, $typeHos->id_type_prestation, $demande->id_demande);
        $this->assertTrue(true);

        // 20 001 FCFA doit déclencher une exception PlafondDepasseException
        $this->expectException(PlafondDepasseException::class);
        $plafondService->checkPlafonds(25000, $typeHos->id_type_prestation, $demande->id_demande);
    }

    public function test_optique_plafond_30000_is_enforced()
    {
        $typeOpt = TypePrestation::where('libelle', 'like', '%Optique%')->first();
        $entreprise = Entreprise::create(['raison_sociale' => 'Entreprise Test']);
        $salarie = Salarie::create(['id_entreprise' => $entreprise->id_entreprise, 'nom' => 'Fall', 'prenom' => 'Cheikh', 'statut' => 'actif']);
        $demande = Demande::create([
            'numero_demande' => 'BC-TEST-OPT',
            'id_salarie' => $salarie->id_salarie,
            'id_type_demande' => 1,
            'date_demande' => now(),
            'statut' => 'Approuvée',
        ]);

        $plafondService = app(PlafondService::class);

        // 30 000 FCFA accepté
        $plafondService->checkPlafonds(30000, $typeOpt->id_type_prestation, $demande->id_demande);
        $this->assertTrue(true);

        // 35 000 FCFA refusé
        $this->expectException(PlafondDepasseException::class);
        $plafondService->checkPlafonds(35000, $typeOpt->id_type_prestation, $demande->id_demande);
    }

    public function test_maternite_plafond_50000_is_enforced()
    {
        $typeMat = TypePrestation::where('libelle', 'like', '%Maternité%')->first();
        $entreprise = Entreprise::create(['raison_sociale' => 'Entreprise Test']);
        $salarie = Salarie::create(['id_entreprise' => $entreprise->id_entreprise, 'nom' => 'Ba', 'prenom' => 'Mariama', 'statut' => 'actif']);
        $demande = Demande::create([
            'numero_demande' => 'LG-TEST-MAT',
            'id_salarie' => $salarie->id_salarie,
            'id_type_demande' => 3,
            'date_demande' => now(),
            'statut' => 'Approuvée',
        ]);

        $plafondService = app(PlafondService::class);

        // 50 000 FCFA accepté
        $plafondService->checkPlafonds(50000, $typeMat->id_type_prestation, $demande->id_demande);
        $this->assertTrue(true);

        // 55 000 FCFA refusé
        $this->expectException(PlafondDepasseException::class);
        $plafondService->checkPlafonds(55000, $typeMat->id_type_prestation, $demande->id_demande);
    }

    public function test_bon_de_commande_applies_pharmacie_75_percent_rate()
    {
        $entreprise = Entreprise::create(['raison_sociale' => 'Entreprise Test']);
        $salarie = Salarie::create(['id_entreprise' => $entreprise->id_entreprise, 'nom' => 'Sarr', 'prenom' => 'Ibra', 'statut' => 'actif']);
        $pharmacie = Pharmacie::create(['nom' => 'Pharmacie Almadies']);

        $service = app(DemandeService::class);
        $result = $service->traiterDemande([
            'id_type_demande' => 1,
            'id_salarie' => $salarie->id_salarie,
            'id_pharmacie' => $pharmacie->id_pharmacie,
            'date_ordonnance' => now()->toDateString(),
            'nombre_articles' => 2,
        ]);

        $this->assertEquals(75.00, (float)$result['document']->taux_prise_charge);
    }
}
