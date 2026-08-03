<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PrestataireRechercheController;
use App\Http\Controllers\Api\DemandeApiController;
use App\Http\Controllers\Api\PrestationApiController;
use App\Http\Controllers\Api\DossierMedicalApiController;
use App\Http\Controllers\Api\FacturationApiController;
use App\Http\Controllers\Api\CotisationApiController;
use App\Http\Controllers\PrestataireController;
use App\Http\Controllers\ConventionController;

// Routes publiques
Route::post('/login', [AuthController::class, 'login']);

// Routes protégées par Sanctum
Route::middleware('auth:sanctum')->group(function () {
    
    // Informations de l'utilisateur connecté
    Route::get('/user', function (Request $request) {
        $user = $request->user()->load(['role.permissions']);
        return response()->json($user);
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    // Gestion des rôles et permissions (Réservé aux administrateurs)
    Route::middleware('permission:Gérer les rôles')->group(function () {
        Route::get('/roles', [RoleController::class, 'index']);
        Route::post('/roles', [RoleController::class, 'store']);
        Route::get('/roles/{role}', [RoleController::class, 'show']);
        Route::put('/roles/{role}', [RoleController::class, 'update']);
        Route::delete('/roles/{role}', [RoleController::class, 'destroy']);
        
        Route::get('/permissions', [RoleController::class, 'permissions']);
    });

    // Recherche géolocalisée
    Route::get('/prestataires/search', [PrestataireRechercheController::class, 'search']);

    // API CRUD Prestataires
    Route::apiResource('prestataires', PrestataireController::class);

    // API CRUD Conventions
    Route::apiResource('conventions', ConventionController::class);

    // API Demandes (Bons, Feuilles, Lettres)
    Route::post('/demandes', [DemandeApiController::class, 'store']);
    Route::get('/demandes/history', [DemandeApiController::class, 'history']);


    // API Prestations
    Route::post('/prestations', [PrestationApiController::class, 'store']);
    Route::get('/prestations/historique', [PrestationApiController::class, 'history']);
    Route::get('/prestations/export', [PrestationApiController::class, 'export']);

    // API Paramètres de Couverture
    Route::post('/parametres-couverture/simuler', [\App\Http\Controllers\Api\ParametreCouvertureApiController::class, 'simuler']);
    Route::apiResource('parametres-couverture', \App\Http\Controllers\Api\ParametreCouvertureApiController::class);

    // API Dossier Médical
    Route::post('/dossier-medical', [DossierMedicalApiController::class, 'store']);
    Route::get('/dossier-medical/{beneficiaireType}/{idBeneficiaire}', [DossierMedicalApiController::class, 'history']);

    // API Facturation et Paiements
    Route::middleware('permission:Gérer la facturation')->group(function () {
        Route::get('/factures/impayees', [FacturationApiController::class, 'facturesImpayees']);
        Route::get('/paiements/export', [FacturationApiController::class, 'exportPaiements']);
    });

    // API Cotisations
    Route::get('/cotisations/statut', [CotisationApiController::class, 'statut']);
    Route::get('/cotisations/recouvrement', [CotisationApiController::class, 'recouvrement']);

    // API Audit (Historique)
    Route::get('/audit', [\App\Http\Controllers\Api\AuditController::class, 'index']);
    Route::get('/audit/export', [\App\Http\Controllers\Api\AuditController::class, 'export']);
});
