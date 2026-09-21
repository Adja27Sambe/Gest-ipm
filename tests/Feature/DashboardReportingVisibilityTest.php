<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Utilisateur;
use App\Models\Role;

class DashboardReportingVisibilityTest extends TestCase
{
    public function test_superviseur_can_see_total_facture_and_reste_a_payer()
    {
        $roleSuperviseur = Role::where('code', 'superviseur')->first();
        $this->assertNotNull($roleSuperviseur);

        $superviseur = Utilisateur::firstOrCreate(
            ['email' => 'superviseur.kpi@gest-ipm.sn'],
            [
                'nom' => 'Superviseur',
                'prenom' => 'KPI',
                'login' => 'superviseur_kpi',
                'id_role' => $roleSuperviseur->id_role,
                'mot_de_passe' => bcrypt('password123'),
                'statut' => 'actif',
            ]
        );
        $superviseur->id_role = $roleSuperviseur->id_role;
        $superviseur->save();

        $this->actingAs($superviseur);

        $response = $this->get(route('reporting.index'));
        $response->assertStatus(200);
        $response->assertSee("Vue d'ensemble des statistiques de l'IPM", false);
        $response->assertSee('Adhérents');
        $response->assertSee('Bénéficiaires');
        $response->assertSee('Total Facturé');
        $response->assertSee('Reste à Payer');
        $response->assertSee('Évolution des Facturations');
        $response->assertSee('Dernières Factures Émises');
    }

    public function test_facturation_service_can_see_total_facture_and_reste_a_payer()
    {
        $roleFacturation = Role::where('code', 'role_facturation')
            ->orWhere('libelle', 'like', '%Facturation%')
            ->first();
        $this->assertNotNull($roleFacturation);

        $agentFacturation = Utilisateur::firstOrCreate(
            ['email' => 'facturation.kpi@gest-ipm.sn'],
            [
                'nom' => 'Facturation',
                'prenom' => 'Agent',
                'login' => 'facturation_kpi',
                'id_role' => $roleFacturation->id_role,
                'mot_de_passe' => bcrypt('password123'),
                'statut' => 'actif',
            ]
        );
        $agentFacturation->id_role = $roleFacturation->id_role;
        $agentFacturation->save();

        $this->actingAs($agentFacturation);

        $response = $this->get(route('reporting.index'));
        $response->assertStatus(200);
        $response->assertSee('Adhérents');
        $response->assertSee('Bénéficiaires');
        $response->assertSee('Total Facturé');
        $response->assertSee('Reste à Payer');
        $response->assertSee('Évolution des Facturations');
        $response->assertSee('Dernières Factures Émises');
    }

    public function test_admin_can_see_total_facture_and_reste_a_payer()
    {
        $roleAdmin = Role::where('libelle', 'Administrateur')
            ->orWhere('code', 'administrateur')
            ->first();
        $this->assertNotNull($roleAdmin);

        $admin = Utilisateur::firstOrCreate(
            ['email' => 'admin.kpi@gest-ipm.sn'],
            [
                'nom' => 'Admin',
                'prenom' => 'KPI',
                'login' => 'admin_kpi',
                'id_role' => $roleAdmin->id_role,
                'mot_de_passe' => bcrypt('password123'),
                'statut' => 'actif',
            ]
        );
        $admin->id_role = $roleAdmin->id_role;
        $admin->save();

        $this->actingAs($admin);

        $response = $this->get(route('reporting.index'));
        $response->assertStatus(200);
        $response->assertSee('Adhérents');
        $response->assertSee('Bénéficiaires');
        $response->assertSee('Total Facturé');
        $response->assertSee('Reste à Payer');
    }

    public function test_non_billing_roles_cannot_see_total_facture_nor_reste_a_payer()
    {
        // Rôle non-facturation (ex: Agent de saisie ou Demandes ou Entreprises)
        $roleAgent = Role::where('code', 'agent_saisie')
            ->orWhere('code', 'role_demandes')
            ->orWhere('code', 'role_salaries')
            ->first();
        $this->assertNotNull($roleAgent);

        $agent = Utilisateur::firstOrCreate(
            ['email' => 'agent.saisie.kpi@gest-ipm.sn'],
            [
                'nom' => 'Agent',
                'prenom' => 'Saisie',
                'login' => 'agent_saisie_kpi',
                'id_role' => $roleAgent->id_role,
                'mot_de_passe' => bcrypt('password123'),
                'statut' => 'actif',
            ]
        );
        $agent->id_role = $roleAgent->id_role;
        $agent->save();

        $this->actingAs($agent);

        $response = $this->get(route('reporting.index'));
        $response->assertStatus(200);
        $response->assertSee("Vue d'ensemble des statistiques de l'IPM", false);
        $response->assertSee('Adhérents');
        $response->assertSee('Bénéficiaires');

        // Total Facturé et Reste à Payer NE doivent PAS figurer
        $response->assertDontSee('Total Facturé');
        $response->assertDontSee('Reste à Payer');
        $response->assertDontSee('Évolution des Facturations');
        $response->assertDontSee('Dernières Factures Émises');
    }
}
