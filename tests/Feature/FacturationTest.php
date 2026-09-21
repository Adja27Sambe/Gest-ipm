<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Role;
use App\Models\Utilisateur;
use App\Models\Facture;
use App\Models\Pharmacie;
use App\Models\Praticien;
use App\Models\Prestation;
use App\Models\Demande;
use App\Models\TypeDemande;
use App\Models\Entreprise;
use App\Models\Salarie;
use App\Models\TypePrestation;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FacturationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $typeBc;
    protected $typeFm;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create(['libelle' => 'Administrateur']);
        $this->admin = Utilisateur::create([
            'login' => 'admin_test_facture',
            'nom' => 'Admin',
            'prenom' => 'Facture',
            'email' => 'admin_facture@test.com',
            'mot_de_passe' => bcrypt('password'),
            'id_role' => $role->id_role,
        ]);

        $this->typeBc = TypeDemande::firstOrCreate(['libelle' => 'Bon de Commande']);
        $this->typeFm = TypeDemande::firstOrCreate(['libelle' => 'Feuille de Maladie']);
    }

    public function test_can_view_factures_index()
    {
        $response = $this->actingAs($this->admin, 'web')->get(route('factures.index'));
        $response->assertStatus(200);
    }

    public function test_can_create_facture_for_pharmacie()
    {
        $pharmacie = Pharmacie::create(['nom' => 'Pharmacie Test']);
        $typePrestation = TypePrestation::create(['libelle' => 'Pharmacie']);
        $entreprise = Entreprise::create(['raison_sociale' => 'Entreprise Test']);
        $salarie = Salarie::create(['id_entreprise' => $entreprise->id_entreprise, 'nom' => 'Diallo', 'prenom' => 'Amadou']);

        $demande = Demande::create([
            'numero_demande' => 'BC-TEST-FACT',
            'id_salarie' => $salarie->id_salarie,
            'id_type_demande' => $this->typeBc->id_type_demande,
            'id_pharmacie' => $pharmacie->id_pharmacie,
            'date_demande' => now(),
            'statut' => 'Approuvée',
        ]);

        $prestation = Prestation::create([
            'id_demande' => $demande->id_demande,
            'id_type_prestation' => $typePrestation->id_type_prestation,
            'id_pharmacie' => $pharmacie->id_pharmacie,
            'date_prestation' => now(),
            'montant' => 20000,
            'taux_prise_charge' => 75,
            'reste_a_charge' => 5000,
        ]);

        $response = $this->actingAs($this->admin, 'web')->post(route('factures.store'), [
            'partenaire' => 'pharmacie_' . $pharmacie->id_pharmacie,
            'numero_facture' => 'FACT-PHA-001',
            'date_facture' => now()->format('Y-m-d'),
            'prestations' => [$prestation->id_prestation],
        ]);

        $response->assertRedirect(route('factures.index'));

        $this->assertDatabaseHas('facture', [
            'numero_facture' => 'FACT-PHA-001',
            'id_pharmacie' => $pharmacie->id_pharmacie,
            'statut_paiement' => 'en_attente',
            'montant' => 15000.00,
        ]);

        $facture = Facture::where('numero_facture', 'FACT-PHA-001')->first();
        $this->assertNotNull($facture);
        $this->assertEquals(1, $facture->prestations()->count());
    }

    public function test_can_create_facture_for_praticien()
    {
        $praticien = Praticien::create(['nom' => 'Dr. Test']);
        $typePrestation = TypePrestation::create(['libelle' => 'Médecine Générale']);
        $entreprise = Entreprise::create(['raison_sociale' => 'Entreprise Test']);
        $salarie = Salarie::create(['id_entreprise' => $entreprise->id_entreprise, 'nom' => 'Ba', 'prenom' => 'Fatou']);

        $demande = Demande::create([
            'numero_demande' => 'FM-TEST-FACT',
            'id_salarie' => $salarie->id_salarie,
            'id_type_demande' => $this->typeFm->id_type_demande,
            'id_praticien' => $praticien->id_praticien,
            'date_demande' => now(),
            'statut' => 'Approuvée',
        ]);

        $prestation = Prestation::create([
            'id_demande' => $demande->id_demande,
            'id_type_prestation' => $typePrestation->id_type_prestation,
            'id_praticien' => $praticien->id_praticien,
            'date_prestation' => now(),
            'montant' => 10000,
            'taux_prise_charge' => 75,
            'reste_a_charge' => 2500,
        ]);

        $response = $this->actingAs($this->admin, 'web')->post(route('factures.store'), [
            'partenaire' => 'praticien_' . $praticien->id_praticien,
            'numero_facture' => 'FACT-PRA-001',
            'date_facture' => now()->format('Y-m-d'),
            'prestations' => [$prestation->id_prestation],
        ]);

        $response->assertRedirect(route('factures.index'));

        $this->assertDatabaseHas('facture', [
            'numero_facture' => 'FACT-PRA-001',
            'id_praticien' => $praticien->id_praticien,
            'statut_paiement' => 'en_attente',
            'montant' => 7500.00,
        ]);
    }
}
