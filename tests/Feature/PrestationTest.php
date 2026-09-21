<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Role;
use App\Models\Utilisateur;
use App\Models\Entreprise;
use App\Models\Salarie;
use App\Models\Demande;
use App\Models\TypeDemande;
use App\Models\TypePrestation;
use App\Models\ParametreCouverture;
use App\Models\Praticien;
use App\Models\Pharmacie;
use App\Models\Prestation;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PrestationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $prestation;
    protected $typePrestation;
    protected $demande;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create(['libelle' => 'Administrateur']);
        $this->admin = Utilisateur::create([
            'login' => 'admin_test',
            'nom' => 'Admin',
            'prenom' => 'Super',
            'email' => 'admin@test.com',
            'mot_de_passe' => bcrypt('password'),
            'id_role' => $role->id_role,
        ]);

        $entreprise = Entreprise::create([
            'raison_sociale' => 'Société Test',
        ]);

        $salarie = Salarie::create([
            'id_entreprise' => $entreprise->id_entreprise,
            'nom' => 'Sow',
            'prenom' => 'Fatou',
            'statut' => 'actif'
        ]);

        $typeDemande = TypeDemande::firstOrCreate(['libelle' => 'Feuille de Maladie']);

        $praticien = Praticien::create([
            'nom' => 'Dr. Ba',
        ]);

        $this->demande = Demande::create([
            'numero_demande' => 'FM20260001',
            'id_salarie' => $salarie->id_salarie,
            'id_type_demande' => $typeDemande->id_type_demande,
            'id_praticien' => $praticien->id_praticien,
            'date_demande' => now(),
            'statut' => 'Approuvée',
        ]);

        $this->typePrestation = TypePrestation::create([
            'libelle' => 'Consultation Généraliste',
        ]);

        ParametreCouverture::create([
            'id_type_prestation' => $this->typePrestation->id_type_prestation,
            'taux_prise_charge' => 80.00,
            'plafond_par_acte' => 50000.00,
            'plafond_annuel' => 300000.00,
        ]);

        $this->prestation = Prestation::create([
            'date_prestation' => now()->toDateString(),
            'montant' => 20000.00,
            'taux_prise_charge' => 80.00,
            'id_type_prestation' => $this->typePrestation->id_type_prestation,
            'id_demande' => $this->demande->id_demande,
            'id_praticien' => $praticien->id_praticien,
        ]);
    }

    public function test_can_view_prestations_index()
    {
        $response = $this->actingAs($this->admin)->get(route('prestations.index'));
        $response->assertStatus(200);
        $response->assertSee('Facturation des Prestations Médicales');
        $response->assertSee('20 000 FCFA');
    }

    public function test_can_render_prestation_edit_page()
    {
        $response = $this->actingAs($this->admin)->get(route('prestations.edit', $this->prestation));
        $response->assertStatus(200);
        $response->assertSee('Édition de la Prestation #' . $this->prestation->id_prestation);
        $response->assertSee('20000');
    }

    public function test_can_update_prestation_and_recalculates_reste_a_charge()
    {
        $response = $this->actingAs($this->admin)->put(route('prestations.update', $this->prestation), [
            'date_prestation' => now()->toDateString(),
            'montant' => 30000.00,
            'taux_prise_charge' => 70.00,
            'id_demande' => $this->demande->id_demande,
            'id_praticien' => $this->prestation->id_praticien,
        ]);

        $response->assertRedirect(route('prestations.index'));
        $response->assertSessionHas('success');

        $this->prestation->refresh();
        $this->assertEquals(30000.00, (float)$this->prestation->montant);
        $this->assertEquals(70.00, (float)$this->prestation->taux_prise_charge);
        // 30 000 * 70% = 21 000 prise en charge -> reste à charge = 9 000
        $this->assertEquals(9000.00, (float)$this->prestation->reste_a_charge);
    }

    public function test_can_delete_prestation()
    {
        $id = $this->prestation->id_prestation;
        $response = $this->actingAs($this->admin)->delete(route('prestations.destroy', $this->prestation));

        $response->assertRedirect(route('prestations.index'));
        $this->assertDatabaseMissing('prestation', ['id_prestation' => $id]);
    }

    public function test_can_update_prestation_with_bon_de_commande_articles()
    {
        $typeDemandeBon = TypeDemande::firstOrCreate(['libelle' => 'Bon de Commande']);
        $pharmacie = Pharmacie::create(['nom' => 'Pharmacie Nation']);
        $demandeBon = Demande::create([
            'numero_demande' => 'BC20260099',
            'id_salarie' => $this->demande->id_salarie,
            'id_type_demande' => $typeDemandeBon->id_type_demande,
            'id_pharmacie' => $pharmacie->id_pharmacie,
            'date_demande' => now(),
            'statut' => 'Approuvée',
        ]);
        \App\Models\BonCommande::create([
            'id_demande' => $demandeBon->id_demande,
            'numero_bon' => 'BC-99',
            'nombre_articles' => 2,
        ]);

        $articles = [
            [
                'nom' => 'Doliprane 1000mg',
                'quantite' => 2,
                'montant' => 2500, // total = 5 000
                'taux' => 80,
            ],
            [
                'nom' => 'Amoxicilline 500mg',
                'quantite' => 1,
                'montant' => 6000, // total = 6 000
                'taux' => 80,
            ],
        ];

        $response = $this->actingAs($this->admin)->put(route('prestations.update', $this->prestation), [
            'date_prestation' => now()->toDateString(),
            'montant' => 0, // Should be overwritten by sum of articles: 11 000
            'taux_prise_charge' => 80.00,
            'id_demande' => $demandeBon->id_demande,
            'id_pharmacie' => $pharmacie->id_pharmacie,
            'details_articles' => $articles,
        ]);

        $response->assertRedirect(route('prestations.index'));
        $this->prestation->refresh();

        // Vérification du montant recalculé automatiquement : 2*2500 + 1*6000 = 11 000
        $this->assertEquals(11000.00, (float)$this->prestation->montant);
        // Prise en charge 80% de 11 000 = 8 800 -> Reste à charge = 2 200
        $this->assertEquals(2200.00, (float)$this->prestation->reste_a_charge);
        // Vérification des détails des articles enregistrés
        $this->assertIsArray($this->prestation->details_articles);
        $this->assertCount(2, $this->prestation->details_articles);
        $this->assertEquals('Doliprane 1000mg', $this->prestation->details_articles[0]['nom']);
        $this->assertEquals(2, $this->prestation->details_articles[0]['quantite']);
        $this->assertEquals(2500, $this->prestation->details_articles[0]['montant']);
        $this->assertEquals(80, $this->prestation->details_articles[0]['taux']);
    }

    public function test_prestation_index_displays_modal_with_articles_detail_and_rates_per_act(): void
    {
        $response = $this->actingAs($this->admin)->get(route('prestations.index'));
        $response->assertStatus(200);
        $response->assertSee('createPrestationModal');
        $response->assertSee('modal_articles_container');
        $response->assertSee('modal_id_demande');
        $response->assertSee('Taux (%)');
        $response->assertSee('modal-art-acte-select');
        $response->assertSee('AVAILABLE_ACTES');
        $response->assertSee('handleModalActeSelect');
        $response->assertDontSee('id="modal_id_type_prestation"', false);
    }

    public function test_prestation_create_route_redirects_to_index_with_modal_flag(): void
    {
        $response = $this->actingAs($this->admin)->get(route('prestations.create'));
        $response->assertRedirect(route('prestations.index', ['create' => 1]));
    }

    public function test_only_valid_demandes_appear_in_prestation_modal_and_invalid_demandes_are_rejected(): void
    {
        $typeDemande = TypeDemande::first();
        $praticien = Praticien::first();

        $demandeEnAttente = Demande::create([
            'numero_demande' => 'ATTENTE-999',
            'id_salarie' => $this->demande->id_salarie,
            'id_type_demande' => $typeDemande->id_type_demande,
            'id_praticien' => $praticien->id_praticien,
            'date_demande' => now(),
            'statut' => 'en_attente',
        ]);

        $demandeRejetee = Demande::create([
            'numero_demande' => 'REJETEE-999',
            'id_salarie' => $this->demande->id_salarie,
            'id_type_demande' => $typeDemande->id_type_demande,
            'id_praticien' => $praticien->id_praticien,
            'date_demande' => now(),
            'statut' => 'rejetée',
        ]);

        $demandeValidee = Demande::create([
            'numero_demande' => 'VALIDEE-999',
            'id_salarie' => $this->demande->id_salarie,
            'id_type_demande' => $typeDemande->id_type_demande,
            'id_praticien' => $praticien->id_praticien,
            'date_demande' => now(),
            'statut' => 'validée',
        ]);

        // Vérifier dans la vue index : la demande validée est présente, les autres non
        $response = $this->actingAs($this->admin)->get(route('prestations.index'));
        $response->assertStatus(200);
        $response->assertSee('VALIDEE-999');
        $response->assertDontSee('ATTENTE-999');
        $response->assertDontSee('REJETEE-999');

        // Tenter de créer une prestation avec une demande en attente -> doit être bloqué
        $postResponse = $this->actingAs($this->admin)->post(route('prestations.store'), [
            'date_prestation' => now()->toDateString(),
            'montant' => 15000,
            'taux_prise_charge' => 80,
            'id_type_prestation' => $this->typePrestation->id_type_prestation,
            'id_demande' => $demandeEnAttente->id_demande,
            'id_praticien' => $praticien->id_praticien,
        ]);
        $postResponse->assertSessionHas('error');

        // Créer une prestation avec la demande validée -> doit réussir
        $postValidResponse = $this->actingAs($this->admin)->post(route('prestations.store'), [
            'date_prestation' => now()->toDateString(),
            'montant' => 15000,
            'taux_prise_charge' => 80,
            'id_type_prestation' => $this->typePrestation->id_type_prestation,
            'id_demande' => $demandeValidee->id_demande,
            'id_praticien' => $praticien->id_praticien,
        ]);
        $postValidResponse->assertSessionHas('success');
        $this->assertDatabaseHas('prestation', [
            'id_demande' => $demandeValidee->id_demande,
            'montant' => 15000,
        ]);

        // Vérifier que la table des prestations n'affiche que les prestations rattachées à des demandes valides
        $prestationInvalide = Prestation::create([
            'date_prestation' => now()->toDateString(),
            'montant' => 99999,
            'taux_prise_charge' => 80,
            'id_type_prestation' => $this->typePrestation->id_type_prestation,
            'id_demande' => $demandeEnAttente->id_demande,
            'id_praticien' => $praticien->id_praticien,
        ]);

        $listResponse = $this->actingAs($this->admin)->get(route('prestations.index'));
        $listResponse->assertStatus(200);
        $listResponse->assertSee('15 000 FCFA');
        $listResponse->assertDontSee('99 999 FCFA');
    }

    public function test_store_prestation_with_individual_rates_and_no_type_prestation(): void
    {
        $typeDemande = TypeDemande::where('libelle', 'like', '%Feuille%')->first() ?? TypeDemande::first();
        $praticien = Praticien::first();

        $demandeFeuille = Demande::create([
            'numero_demande' => 'FM-TEST-001',
            'id_salarie' => $this->demande->id_salarie,
            'id_type_demande' => $typeDemande->id_type_demande,
            'id_praticien' => $praticien->id_praticien,
            'date_demande' => now(),
            'statut' => 'validée',
        ]);

        $articles = [
            [
                'nom' => 'Consultation Spécialisée',
                'quantite' => 1,
                'montant' => 20000,
                'taux' => 80,
            ],
            [
                'nom' => 'Échographie Abdominale',
                'quantite' => 1,
                'montant' => 30000,
                'taux' => 60,
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('prestations.store'), [
            'date_prestation' => now()->toDateString(),
            'montant' => 0,
            'id_demande' => $demandeFeuille->id_demande,
            'id_praticien' => $praticien->id_praticien,
            'details_articles' => $articles,
        ]);

        $response->assertRedirect(route('prestations.index'));
        $response->assertSessionHas('success');

        $prestation = Prestation::where('id_demande', $demandeFeuille->id_demande)->latest()->first();
        $this->assertNotNull($prestation);
        $this->assertEquals(50000, (float)$prestation->montant);
        // Acte 1 : 20000 * 80% = 16000 part IPM
        // Acte 2 : 30000 * 60% = 18000 part IPM
        // Total part IPM = 34000, reste à charge = 16000
        $this->assertEquals(16000, (float)$prestation->reste_a_charge);
        $this->assertEquals(68.0, (float)$prestation->taux_prise_charge);
        $this->assertCount(2, $prestation->details_articles);
        $this->assertEquals(80, $prestation->details_articles[0]['taux']);
        $this->assertEquals(60, $prestation->details_articles[1]['taux']);
    }

    public function test_prestation_views_contain_demande_provider_auto_fill_attributes(): void
    {
        $responseIndex = $this->actingAs($this->admin)->get(route('prestations.index'));
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('modal_badge_prat');
        $responseIndex->assertSee('modal_badge_pharm');
        $responseIndex->assertSee('data-prat="' . $this->demande->id_praticien . '"', false);

        $responseEdit = $this->actingAs($this->admin)->get(route('prestations.edit', $this->prestation));
        $responseEdit->assertStatus(200);
        $responseEdit->assertSee('badge_prat');
        $responseEdit->assertSee('badge_pharm');
        $responseEdit->assertSee('data-praticien-id="' . $this->demande->id_praticien . '"', false);
    }
}
