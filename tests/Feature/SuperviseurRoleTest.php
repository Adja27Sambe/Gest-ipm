<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Utilisateur;
use App\Models\Role;
use App\Models\Demande;
use App\Models\Salarie;
use App\Models\CarteAssure;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SuperviseurRoleTest extends TestCase
{
    protected Utilisateur $superviseur;

    protected function setUp(): void
    {
        parent::setUp();

        $roleSuperviseur = Role::where('code', 'superviseur')->first();
        $this->assertNotNull($roleSuperviseur, "Le rôle Superviseur doit exister en base.");

        // Création ou récupération d'un utilisateur Superviseur
        $this->superviseur = Utilisateur::firstOrCreate(
            ['email' => 'superviseur.test@gest-ipm.sn'],
            [
                'nom' => 'Diop',
                'prenom' => 'Superviseur',
                'login' => 'superviseur_test',
                'id_role' => $roleSuperviseur->id_role,
                'mot_de_passe' => bcrypt('password123'),
                'statut' => 'actif',
            ]
        );
        $this->superviseur->id_role = $roleSuperviseur->id_role;
        $this->superviseur->save();
    }

    public function test_superviseur_can_access_dashboard_and_demandes()
    {
        $this->actingAs($this->superviseur);
        $this->get(route('reporting.index'))->assertStatus(200);
        $this->get(route('demandes.index'))->assertStatus(200);
        $this->get(route('demandes.validation.index'))->assertStatus(200);
    }

    public function test_superviseur_can_access_adherents_and_salaries()
    {
        $this->actingAs($this->superviseur);
        $this->get(route('entreprises.index'))->assertStatus(200);
        $this->get(route('salaries.index'))->assertStatus(200);
    }

    public function test_superviseur_can_access_praticiens()
    {
        $this->actingAs($this->superviseur);
        $this->get(route('praticiens.index'))->assertStatus(200);
    }

    public function test_superviseur_can_access_pharmacies()
    {
        $this->actingAs($this->superviseur);
        $this->get(route('pharmacies.index'))->assertStatus(200);
    }

    public function test_superviseur_can_access_prestations_and_factures()
    {
        $this->actingAs($this->superviseur);
        $this->get(route('prestations.index'))->assertStatus(200);
        $this->get(route('factures.index'))->assertStatus(200);
    }

    public function test_superviseur_can_access_cotisations_and_documents()
    {
        $this->actingAs($this->superviseur);
        $this->get(route('cotisations.index'))->assertStatus(200);
        $this->get(route('pieces-jointes.index'))->assertStatus(200);
        $this->get(route('medias.index'))->assertStatus(200);
    }

    public function test_superviseur_can_access_audit_and_parametres()
    {
        $this->actingAs($this->superviseur);
        $this->get(route('audit.index'))->assertStatus(200);
        $this->get(route('parametres-couverture.index'))->assertStatus(200);
    }

    public function test_superviseur_cannot_manage_system_roles()
    {
        $this->actingAs($this->superviseur);

        // Les rôles système restent protégés et réservés à l'Administrateur
        $response = $this->get(route('roles.index'));
        $response->assertStatus(403);
    }

    public function test_superviseur_can_generate_and_download_documents()
    {
        $this->actingAs($this->superviseur);

        // Téléchargement Carte Assuré PDF si une carte existe
        $carte = CarteAssure::first();
        if ($carte) {
            $response = $this->get(route('cartes-assurees.download', $carte->id_carte));
            $response->assertStatus(200);
            $this->assertEquals('application/pdf', $response->headers->get('content-type'));
        }

        // Téléchargement / Génération PDF Prise en charge si une demande existe
        $demande = Demande::first();
        if ($demande) {
            $response = $this->get(route('demandes.pdf', $demande->id_demande));
            $response->assertStatus(200);
            $this->assertEquals('application/pdf', $response->headers->get('content-type'));
        }
    }

    public function test_superviseur_profile_is_strictly_read_only()
    {
        $this->assertTrue($this->superviseur->isReadOnly());
        $this->assertFalse($this->superviseur->canEdit());
        $this->assertTrue($this->superviseur->role->isReadOnly());
        $this->assertFalse($this->superviseur->role->canEdit());
    }

    public function test_superviseur_cannot_access_create_and_edit_routes()
    {
        $this->actingAs($this->superviseur);

        // Toutes les routes *.create et *.edit doivent renvoyer 403 Forbidden
        $this->get(route('salaries.create'))->assertStatus(403);
        $this->get(route('entreprises.create'))->assertStatus(403);
        $this->get(route('factures.create'))->assertStatus(403);
        $this->get(route('cotisations.create'))->assertStatus(403);
        $this->get(route('parametres-couverture.create'))->assertStatus(403);

        $salarie = Salarie::first();
        if ($salarie) {
            $this->get(route('salaries.edit', $salarie))->assertStatus(403);
        }
    }

    public function test_superviseur_cannot_execute_mutations_post_put_delete()
    {
        $this->actingAs($this->superviseur);

        // POST /salaries bloqué
        $this->post(route('salaries.store'), [
            'nom' => 'Test',
            'prenom' => 'Test',
        ])->assertStatus(403);

        // POST /entreprises bloqué
        $this->post(route('entreprises.store'), [
            'raison_sociale' => 'Entreprise Test',
        ])->assertStatus(403);

        // POST /demandes bloqué
        $this->post(route('demandes.store'), [])->assertStatus(403);

        // Validation / Rejet de demande bloqués
        $demande = Demande::first();
        if ($demande) {
            $this->post(route('demandes.approuver', $demande->id_demande))->assertStatus(403);
            $this->post(route('demandes.rejeter', $demande->id_demande))->assertStatus(403);
        }

        // Suppression bloquée
        $salarie = Salarie::first();
        if ($salarie) {
            $this->delete(route('salaries.destroy', $salarie))->assertStatus(403);
        }
    }

    public function test_superviseur_ui_hides_creation_and_modification_buttons()
    {
        $this->actingAs($this->superviseur);

        // Sur le tableau de bord / navigation : présence du badge Mode Lecteur
        $responseReporting = $this->get(route('reporting.index'));
        $responseReporting->assertStatus(200);
        $responseReporting->assertSee('Mode Consultation / Lecteur');

        // Demandes : pas de bouton de création, mais consultation autorisée
        $responseDemandes = $this->get(route('demandes.index'));
        $responseDemandes->assertStatus(200);
        $responseDemandes->assertDontSee('Nouvelle Prise en charge');

        // Salariés : pas de bouton "Nouveau Participant"
        $responseSalaries = $this->get(route('salaries.index'));
        $responseSalaries->assertStatus(200);
        $responseSalaries->assertDontSee('Nouveau Participant');

        // Entreprises : pas de bouton "+ Nouvelle entreprise"
        $responseEntreprises = $this->get(route('entreprises.index'));
        $responseEntreprises->assertStatus(200);
        $responseEntreprises->assertDontSee('+ Nouvelle entreprise');

        // Prestations : pas de bouton "Saisir une prestation", mais export présent
        $responsePrestations = $this->get(route('prestations.index'));
        $responsePrestations->assertStatus(200);
        $responsePrestations->assertDontSee('Saisir une prestation');
        $responsePrestations->assertSee('Exporter XLSX');

        // Factures : pas de bouton "Nouvelle Facture"
        $responseFactures = $this->get(route('factures.index'));
        $responseFactures->assertStatus(200);
        $responseFactures->assertDontSee('Nouvelle Facture');

        // Cotisations : pas de bouton "Nouvelle Cotisation"
        $responseCotisations = $this->get(route('cotisations.index'));
        $responseCotisations->assertStatus(200);
        $responseCotisations->assertDontSee('Nouvelle Cotisation');

        // Praticiens et Pharmacies
        $responsePraticiens = $this->get(route('praticiens.index'));
        $responsePraticiens->assertStatus(200);
        $responsePraticiens->assertDontSee('Nouveau Praticien');

        $responsePharmacies = $this->get(route('pharmacies.index'));
        $responsePharmacies->assertStatus(200);
        $responsePharmacies->assertDontSee('Nouvelle Pharmacie');
    }
}
