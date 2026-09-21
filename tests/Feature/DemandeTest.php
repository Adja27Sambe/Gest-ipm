<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Entreprise;
use App\Models\Salarie;
use App\Models\Pharmacie;
use App\Models\Praticien;
use App\Models\TypeDemande;
use App\Models\Demande;
use App\Models\BonCommande;
use App\Models\FeuilleMaladie;
use App\Models\LettreGarantie;
use App\Services\DemandeService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DemandeTest extends TestCase
{
    use RefreshDatabase;

    protected $salarie;
    protected $pharmacie;
    protected $praticien;
    protected $typeBc;
    protected $typeFm;
    protected $typeLg;

    protected function setUp(): void
    {
        parent::setUp();

        $entreprise = Entreprise::create([
            'raison_sociale' => 'Entreprise Test',
        ]);

        $this->salarie = Salarie::create([
            'id_entreprise' => $entreprise->id_entreprise,
            'nom' => 'Diallo',
            'prenom' => 'Amadou',
            'statut' => 'actif'
        ]);

        $this->pharmacie = Pharmacie::create([
            'nom' => 'Pharmacie Dakar',
        ]);

        $this->praticien = Praticien::create([
            'nom' => 'Dr. Ndiaye',
        ]);

        $this->typeBc = TypeDemande::firstOrCreate(['libelle' => 'Bon de Commande']);
        $this->typeFm = TypeDemande::firstOrCreate(['libelle' => 'Feuille de Maladie']);
        $this->typeLg = TypeDemande::firstOrCreate(['libelle' => 'Lettre de Garantie']);
    }

    public function test_can_create_bon_de_commande_with_pharmacie()
    {
        $service = app(DemandeService::class);
        $result = $service->traiterDemande([
            'id_type_demande' => $this->typeBc->id_type_demande,
            'id_salarie' => $this->salarie->id_salarie,
            'id_pharmacie' => $this->pharmacie->id_pharmacie,
            'date_ordonnance' => now()->toDateString(),
            'nombre_articles' => 3,
            'motif' => 'Achat antibiotiques'
        ]);

        $this->assertInstanceOf(Demande::class, $result['demande']);
        $this->assertInstanceOf(BonCommande::class, $result['document']);
        $this->assertEquals($this->pharmacie->id_pharmacie, $result['demande']->id_pharmacie);
        $this->assertEquals(3, $result['document']->nombre_articles);
    }

    public function test_bon_de_commande_rejects_praticien()
    {
        $this->expectException(\InvalidArgumentException::class);

        $service = app(DemandeService::class);
        $service->traiterDemande([
            'id_type_demande' => $this->typeBc->id_type_demande,
            'id_salarie' => $this->salarie->id_salarie,
            'id_pharmacie' => $this->pharmacie->id_pharmacie,
            'id_praticien' => $this->praticien->id_praticien,
            'date_ordonnance' => now()->toDateString(),
            'nombre_articles' => 2,
        ]);
    }

    public function test_can_create_feuille_de_maladie_with_praticien()
    {
        $service = app(DemandeService::class);
        $result = $service->traiterDemande([
            'id_type_demande' => $this->typeFm->id_type_demande,
            'id_salarie' => $this->salarie->id_salarie,
            'id_praticien' => $this->praticien->id_praticien,
            'diagnostic' => 'Fièvre et toux',
            'motif' => 'Consultation générale'
        ]);

        $this->assertInstanceOf(Demande::class, $result['demande']);
        $this->assertInstanceOf(FeuilleMaladie::class, $result['document']);
        $this->assertEquals($this->praticien->id_praticien, $result['demande']->id_praticien);
        $this->assertEquals('Fièvre et toux', $result['document']->diagnostic);
    }

    public function test_can_create_lettre_de_garantie_with_acte()
    {
        $service = app(DemandeService::class);
        $result = $service->traiterDemande([
            'id_type_demande' => $this->typeLg->id_type_demande,
            'id_salarie' => $this->salarie->id_salarie,
            'id_praticien' => $this->praticien->id_praticien,
            'choix_acte' => 'Hospitalisation',
            'observations' => 'Intervention programmée'
        ]);

        $this->assertInstanceOf(Demande::class, $result['demande']);
        $this->assertInstanceOf(LettreGarantie::class, $result['document']);
        $this->assertEquals($this->praticien->id_praticien, $result['demande']->id_praticien);
        $this->assertStringContainsString('Hospitalisation', $result['document']->choix_acte);
    }

    public function test_can_search_participant_by_matricule_with_autocomplete()
    {
        $role = \App\Models\Role::firstOrCreate(['libelle' => 'Administrateur']);
        $user = \App\Models\Utilisateur::create([
            'login' => 'admin_test2',
            'nom' => 'Admin',
            'prenom' => 'Test',
            'email' => 'admin2@test.com',
            'mot_de_passe' => bcrypt('password'),
            'id_role' => $role->id_role,
        ]);

        $this->salarie->update([
            'matricule' => 'MAT-7788',
            'nom' => 'Diallo',
            'prenom' => 'Amadou',
        ]);

        $response = $this->actingAs($user)->get(route('salaries.search-matricule', ['q' => '7788']));
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'matricule' => 'MAT-7788',
            'nom' => 'Diallo',
            'prenom' => 'Amadou',
        ]);
    }
}
