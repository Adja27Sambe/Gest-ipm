<?php

namespace Tests\Feature;

use App\Models\Entreprise;
use App\Models\Salarie;
use App\Models\AyantDroit;
use App\Models\CarteAssure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SalarieApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_salarie_and_auto_generates_carte_assure()
    {
        $entreprise = Entreprise::create([
            'raison_sociale' => 'Tech Corp'
        ]);

        $data = [
            'id_entreprise' => $entreprise->id_entreprise,
            'nom' => 'Doe',
            'prenom' => 'John',
            'statut' => 'actif'
        ];

        $response = $this->postJson('/api/salaries', $data);

        $response->assertStatus(201)
                 ->assertJsonPath('data.nom', 'Doe')
                 ->assertJsonStructure(['data' => ['carte_assure']]);

        // Vérifie en base si la carte assuré existe bien
        $salarieId = $response->json('data.id_salarie');
        $this->assertDatabaseHas('carte_assure', [
            'id_salarie' => $salarieId,
            'statut' => 'actif'
        ]);
    }

    public function test_date_naissance_cannot_be_after_date_embauche()
    {
        $entreprise = Entreprise::create(['raison_sociale' => 'Tech Corp']);

        $data = [
            'id_entreprise' => $entreprise->id_entreprise,
            'nom' => 'Doe',
            'date_naissance' => '2025-01-01',
            'date_embauche' => '2024-01-01'
        ];

        $response = $this->postJson('/api/salaries', $data);

        // Expect validation error
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['date_naissance']);
    }

    public function test_salarie_radie_cascades_status_to_ayants_droit()
    {
        $entreprise = Entreprise::create(['raison_sociale' => 'Tech Corp']);
        
        $salarie = Salarie::create([
            'id_entreprise' => $entreprise->id_entreprise,
            'nom' => 'Smith',
            'statut' => 'actif'
        ]);

        AyantDroit::create([
            'id_salarie' => $salarie->id_salarie,
            'nom' => 'Smith',
            'prenom' => 'Jane',
            'lien_parente' => 'conjoint',
            'statut' => 'actif'
        ]);

        // Mettre à jour le statut du salarié en 'radie'
        $response = $this->putJson("/api/salaries/{$salarie->id_salarie}", [
            'statut' => 'radie'
        ]);

        $response->assertStatus(200);

        // Vérifier si le listener a bien passé l'ayant droit en 'inactif'
        $this->assertDatabaseHas('ayant_droit', [
            'id_salarie' => $salarie->id_salarie,
            'statut' => 'inactif'
        ]);
    }

    public function test_search_and_famille_endpoints()
    {
        $entreprise = Entreprise::create(['raison_sociale' => 'Tech Corp']);
        
        $salarie = Salarie::create([
            'id_entreprise' => $entreprise->id_entreprise,
            'matricule' => 'EMP-001',
            'nom' => 'Johnson',
            'statut' => 'actif'
        ]);

        AyantDroit::create([
            'id_salarie' => $salarie->id_salarie,
            'nom' => 'Johnson',
            'prenom' => 'Baby',
            'lien_parente' => 'enfant',
            'statut' => 'actif'
        ]);

        // Search test
        $responseSearch = $this->getJson('/api/salaries/search?nom=John');
        $responseSearch->assertStatus(200)
                       ->assertJsonFragment(['matricule' => 'EMP-001']);

        // Famille test
        $responseFamille = $this->getJson("/api/salaries/{$salarie->id_salarie}/famille");
        $responseFamille->assertStatus(200)
                        ->assertJsonPath('data.salarie.nom', 'Johnson')
                        ->assertJsonPath('data.ayants_droit_actifs.0.nom', 'Johnson');
    }
}
