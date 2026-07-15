<?php

namespace Tests\Feature;

use App\Models\Entreprise;
use App\Models\Salarie;
use App\Models\HistoriqueMouvement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EntrepriseApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_entreprises()
    {
        Entreprise::create([
            'code_adherent' => 'ADH001',
            'raison_sociale' => 'Test Company',
            'statut' => 'actif'
        ]);

        $response = $this->getJson('/api/entreprises?statut=actif');

        $response->assertStatus(200)
                 ->assertJsonFragment(['raison_sociale' => 'Test Company']);
    }

    public function test_can_create_entreprise()
    {
        $data = [
            'code_adherent' => 'ADH002',
            'raison_sociale' => 'New Company',
            'email' => 'contact@newcompany.com',
            'statut' => 'actif'
        ];

        $response = $this->postJson('/api/entreprises', $data);

        $response->assertStatus(201)
                 ->assertJsonPath('data.raison_sociale', 'New Company');
                 
        $this->assertDatabaseHas('entreprise', ['code_adherent' => 'ADH002']);
    }

    public function test_can_update_entreprise_status_and_logs_history()
    {
        $entreprise = Entreprise::create([
            'raison_sociale' => 'Status Company',
            'statut' => 'actif'
        ]);

        $response = $this->patchJson("/api/entreprises/{$entreprise->id_entreprise}/status", [
            'statut' => 'suspendu'
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.statut', 'suspendu');

        // Check if observer logged it in historique_mouvement
        $this->assertDatabaseHas('historique_mouvement', [
            'module' => 'Entreprise',
            'action' => 'Changement de statut',
            'ancienne_valeur' => 'actif',
            'nouvelle_valeur' => 'suspendu'
        ]);
    }

    public function test_can_delete_entreprise_without_salaries()
    {
        $entreprise = Entreprise::create([
            'raison_sociale' => 'To Delete'
        ]);

        $response = $this->deleteJson("/api/entreprises/{$entreprise->id_entreprise}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('entreprise', ['id_entreprise' => $entreprise->id_entreprise]);
    }

    public function test_cannot_delete_entreprise_with_salaries()
    {
        $entreprise = Entreprise::create([
            'raison_sociale' => 'Cannot Delete'
        ]);

        Salarie::create([
            'nom' => 'Doe',
            'id_entreprise' => $entreprise->id_entreprise
        ]);

        $response = $this->deleteJson("/api/entreprises/{$entreprise->id_entreprise}");

        $response->assertStatus(409)
                 ->assertJsonFragment(['message' => 'Impossible de supprimer cette entreprise car elle possède des salariés.']);
                 
        $this->assertDatabaseHas('entreprise', ['id_entreprise' => $entreprise->id_entreprise]);
    }
}
