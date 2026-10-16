<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Utilisateur;
use App\Models\TypePrestation;
use App\Models\TypeActe;
use App\Models\ParametreCouverture;
use App\Models\Demande;
use App\Models\TypeDemande;
use App\Models\Entreprise;
use App\Models\Salarie;
use App\Models\Praticien;
use App\Models\Prestation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DemoSeeder;

class PartIpmReferenceCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected Utilisateur $admin;
    protected Salarie $salarie;
    protected Praticien $praticien;
    protected Demande $demande;
    protected TypePrestation $typePrestation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoSeeder::class);

        $role = \App\Models\Role::firstOrCreate(['libelle' => 'Administrateur']);
        $this->admin = Utilisateur::firstOrCreate(
            ['email' => 'admin_calc_test@test.com'],
            [
                'login' => 'admin_calc_test',
                'nom' => 'Admin',
                'prenom' => 'Test',
                'mot_de_passe' => bcrypt('password'),
                'id_role' => $role->id_role,
            ]
        );

        $entreprise = Entreprise::first() ?? Entreprise::create([
            'code_adherent' => 'SOC001',
            'raison_sociale' => 'Société Test',
            'statut' => 'actif'
        ]);

        $this->salarie = Salarie::first() ?? Salarie::create([
            'id_entreprise' => $entreprise->id_entreprise,
            'matricule' => 'MAT001',
            'nom' => 'Fall',
            'prenom' => 'Fatou',
            'date_naissance' => '1990-01-01',
            'statut' => 'actif'
        ]);

        $typeDemande = TypeDemande::firstOrCreate(['libelle' => 'Feuille de Maladie']);
        $this->praticien = Praticien::first() ?? Praticien::create(['nom' => 'Dr. Ba']);

        $this->demande = Demande::create([
            'numero_demande' => 'FM-CALC-TEST-001',
            'id_salarie' => $this->salarie->id_salarie,
            'id_type_demande' => $typeDemande->id_type_demande,
            'id_praticien' => $this->praticien->id_praticien,
            'date_demande' => now()->toDateString(),
            'statut' => 'Approuvée',
        ]);

        $this->typePrestation = TypePrestation::create([
            'libelle' => 'Consultation Spécialisée Test',
        ]);
    }

    public function test_parametrage_couverture_auto_calculates_part_ipm_reference()
    {
        // Montant référentiel = 10 000 FCFA, Taux IPM = 70%
        // Part IPM de référence = 10 000 * 70% = 7 000 FCFA
        $typeActe = TypeActe::create([
            'id_type_prestation' => $this->typePrestation->id_type_prestation,
            'libelle' => 'Cardiologie Standard',
        ]);

        $response = $this->actingAs($this->admin)->post(route('parametres-couverture.store'), [
            'id_type_prestation' => $this->typePrestation->id_type_prestation,
            'id_type_acte' => $typeActe->id_type_acte,
            'type_calcul' => 'MONTANT_REFERENTIEL',
            'montant_referentiel' => 10000,
            'taux_prise_charge' => 70,
        ]);

        $response->assertRedirect(route('parametres-couverture.index'));

        $param = ParametreCouverture::where('id_type_prestation', $this->typePrestation->id_type_prestation)->first();
        $this->assertNotNull($param);
        $this->assertEquals(10000.00, (float)$param->montant_referentiel);
        $this->assertEquals(70.00, (float)$param->taux_prise_charge);
        $this->assertEquals(7000.00, (float)$param->part_ipm_reference);
        $this->assertEquals($typeActe->id_type_acte, $param->id_type_acte);
    }

    public function test_user_example_facture_10000_fcfa()
    {
        // Montant référentiel = 10 000 FCFA, Taux = 70%, Part IPM de référence = 7 000 FCFA
        ParametreCouverture::create([
            'id_type_prestation' => $this->typePrestation->id_type_prestation,
            'type_calcul' => 'MONTANT_REFERENTIEL',
            'montant_referentiel' => 10000.00,
            'taux_prise_charge' => 70.00,
            'part_ipm_reference' => 7000.00,
        ]);

        // Facture de 10 000 FCFA -> Part IPM = 7 000 FCFA, Part participant = 3 000 FCFA
        $prestation = Prestation::create([
            'date_prestation' => now()->toDateString(),
            'id_demande' => $this->demande->id_demande,
            'id_type_prestation' => $this->typePrestation->id_type_prestation,
            'id_praticien' => $this->praticien->id_praticien,
            'montant' => 10000.00,
            'taux_prise_charge' => 70.00,
            'details_articles' => [
                [
                    'nom' => 'Consultation Cardiologie',
                    'quantite' => 1,
                    'montant' => 10000.00,
                    'id_type_prestation' => $this->typePrestation->id_type_prestation,
                    'part_ipm_reference' => 7000.00,
                ]
            ],
        ]);

        $prestation->refresh();
        $this->assertEquals(10000.00, (float)$prestation->montant);
        $this->assertEquals(3000.00, (float)$prestation->reste_a_charge, "La part participant doit être de 3 000 FCFA pour une facture de 10 000 FCFA.");
        $partIpm = (float)$prestation->montant - (float)$prestation->reste_a_charge;
        $this->assertEquals(7000.00, $partIpm, "La part IPM doit rester fixe à 7 000 FCFA.");

        $article = $prestation->details_articles[0];
        $this->assertEquals(7000.00, (float)$article['part_ipm']);
        $this->assertEquals(3000.00, (float)$article['part_participant']);
    }

    public function test_user_example_facture_15000_fcfa()
    {
        ParametreCouverture::create([
            'id_type_prestation' => $this->typePrestation->id_type_prestation,
            'type_calcul' => 'MONTANT_REFERENTIEL',
            'montant_referentiel' => 10000.00,
            'taux_prise_charge' => 70.00,
            'part_ipm_reference' => 7000.00,
        ]);

        // Facture de 15 000 FCFA -> Part IPM = 7 000 FCFA, Part participant = 8 000 FCFA
        $prestation = Prestation::create([
            'date_prestation' => now()->toDateString(),
            'id_demande' => $this->demande->id_demande,
            'id_type_prestation' => $this->typePrestation->id_type_prestation,
            'id_praticien' => $this->praticien->id_praticien,
            'montant' => 15000.00,
            'taux_prise_charge' => 70.00,
            'details_articles' => [
                [
                    'nom' => 'Consultation Cardiologie',
                    'quantite' => 1,
                    'montant' => 15000.00,
                    'id_type_prestation' => $this->typePrestation->id_type_prestation,
                    'part_ipm_reference' => 7000.00,
                ]
            ],
        ]);

        $prestation->refresh();
        $this->assertEquals(15000.00, (float)$prestation->montant);
        $this->assertEquals(8000.00, (float)$prestation->reste_a_charge, "La part participant doit être de 8 000 FCFA pour une facture de 15 000 FCFA.");
        $partIpm = (float)$prestation->montant - (float)$prestation->reste_a_charge;
        $this->assertEquals(7000.00, $partIpm, "La part IPM doit rester fixe à 7 000 FCFA.");

        $article = $prestation->details_articles[0];
        $this->assertEquals(7000.00, (float)$article['part_ipm']);
        $this->assertEquals(8000.00, (float)$article['part_participant']);
    }

    public function test_user_example_facture_25000_fcfa()
    {
        ParametreCouverture::create([
            'id_type_prestation' => $this->typePrestation->id_type_prestation,
            'type_calcul' => 'MONTANT_REFERENTIEL',
            'montant_referentiel' => 10000.00,
            'taux_prise_charge' => 70.00,
            'part_ipm_reference' => 7000.00,
        ]);

        // Facture de 25 000 FCFA -> Part IPM = 7 000 FCFA, Part participant = 18 000 FCFA
        $prestation = Prestation::create([
            'date_prestation' => now()->toDateString(),
            'id_demande' => $this->demande->id_demande,
            'id_type_prestation' => $this->typePrestation->id_type_prestation,
            'id_praticien' => $this->praticien->id_praticien,
            'montant' => 25000.00,
            'taux_prise_charge' => 70.00,
            'details_articles' => [
                [
                    'nom' => 'Consultation Cardiologie',
                    'quantite' => 1,
                    'montant' => 25000.00,
                    'id_type_prestation' => $this->typePrestation->id_type_prestation,
                    'part_ipm_reference' => 7000.00,
                ]
            ],
        ]);

        $prestation->refresh();
        $this->assertEquals(25000.00, (float)$prestation->montant);
        $this->assertEquals(18000.00, (float)$prestation->reste_a_charge, "La part participant doit être de 18 000 FCFA pour une facture de 25 000 FCFA.");
        $partIpm = (float)$prestation->montant - (float)$prestation->reste_a_charge;
        $this->assertEquals(7000.00, $partIpm, "La part IPM doit rester fixe à 7 000 FCFA.");

        $article = $prestation->details_articles[0];
        $this->assertEquals(7000.00, (float)$article['part_ipm']);
        $this->assertEquals(18000.00, (float)$article['part_participant']);
    }

    public function test_user_security_rule_facture_lower_than_part_ipm_reference()
    {
        ParametreCouverture::create([
            'id_type_prestation' => $this->typePrestation->id_type_prestation,
            'type_calcul' => 'MONTANT_REFERENTIEL',
            'montant_referentiel' => 10000.00,
            'taux_prise_charge' => 70.00,
            'part_ipm_reference' => 7000.00,
        ]);

        // Facture de 5 000 FCFA (< 7 000 FCFA) -> Part IPM = 5 000 FCFA, Part participant = 0 FCFA
        $prestation = Prestation::create([
            'date_prestation' => now()->toDateString(),
            'id_demande' => $this->demande->id_demande,
            'id_type_prestation' => $this->typePrestation->id_type_prestation,
            'id_praticien' => $this->praticien->id_praticien,
            'montant' => 5000.00,
            'taux_prise_charge' => 70.00,
            'details_articles' => [
                [
                    'nom' => 'Consultation Rapide',
                    'quantite' => 1,
                    'montant' => 5000.00,
                    'id_type_prestation' => $this->typePrestation->id_type_prestation,
                    'part_ipm_reference' => 7000.00,
                ]
            ],
        ]);

        $prestation->refresh();
        $this->assertEquals(5000.00, (float)$prestation->montant);
        $this->assertEquals(0.00, (float)$prestation->reste_a_charge, "Par sécurité, la part participant doit être 0 quand le montant facturé < Part IPM réf.");
        $partIpm = (float)$prestation->montant - (float)$prestation->reste_a_charge;
        $this->assertEquals(5000.00, $partIpm, "Par sécurité, la Part IPM doit être égale au montant facturé (5 000 FCFA).");

        $article = $prestation->details_articles[0];
        $this->assertEquals(5000.00, (float)$article['part_ipm']);
        $this->assertEquals(0.00, (float)$article['part_participant']);
    }

    public function test_prestation_controller_store_via_http()
    {
        ParametreCouverture::create([
            'id_type_prestation' => $this->typePrestation->id_type_prestation,
            'type_calcul' => 'MONTANT_REFERENTIEL',
            'montant_referentiel' => 10000.00,
            'taux_prise_charge' => 70.00,
            'part_ipm_reference' => 7000.00,
        ]);

        $response = $this->actingAs($this->admin)->post(route('prestations.store'), [
            'id_demande' => $this->demande->id_demande,
            'id_praticien' => $this->praticien->id_praticien,
            'date_prestation' => now()->toDateString(),
            'montant' => 15000.00,
            'taux_prise_charge' => 70.00,
            'details_articles' => [
                [
                    'nom' => 'Consultation Spécialisée',
                    'quantite' => 1,
                    'montant' => 15000.00,
                    'id_type_prestation' => $this->typePrestation->id_type_prestation,
                    'part_ipm_reference' => 7000.00,
                    'montant_referentiel' => 10000.00,
                ]
            ],
        ]);

        $response->assertRedirect();

        $prestation = Prestation::where('id_demande', $this->demande->id_demande)->latest('id_prestation')->first();
        $this->assertNotNull($prestation);
        $this->assertEquals(15000.00, (float)$prestation->montant);
        $this->assertEquals(8000.00, (float)$prestation->reste_a_charge);
        $this->assertEquals(7000.00, (float)$prestation->montant - (float)$prestation->reste_a_charge);
    }
}
