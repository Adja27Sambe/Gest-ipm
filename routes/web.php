<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DemandeController;
use App\Http\Controllers\EntrepriseController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Web\PrestataireWebController;
use App\Http\Controllers\Web\ConventionWebController;
use App\Http\Controllers\DevisController;
use App\Http\Controllers\PrestationController;
use App\Http\Controllers\DossierMedicalController;

// Routes publiques
Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('login', [AuthController::class, 'login']);

// Routes protégées
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect()->route('demandes.index');
    });

    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::resource('demandes', DemandeController::class)->middleware('can:gerer_demandes');
    Route::resource('entreprises', EntrepriseController::class)->middleware('can:gerer_entreprises');
    
    Route::middleware('can:gerer_salaries')->group(function() {
        Route::resource('salaries', App\Http\Controllers\SalarieController::class)->parameters([
            'salaries' => 'salarie'
        ]);
        Route::post('salaries/{salarie}/ayants-droit', [App\Http\Controllers\AyantDroitController::class, 'store'])->name('ayants-droit.store');
        Route::get('ayants-droit/{ayantDroit}/edit', [App\Http\Controllers\AyantDroitController::class, 'edit'])->name('ayants-droit.edit');
        Route::put('ayants-droit/{ayantDroit}', [App\Http\Controllers\AyantDroitController::class, 'update'])->name('ayants-droit.update');
        Route::delete('ayants-droit/{ayantDroit}', [App\Http\Controllers\AyantDroitController::class, 'destroy'])->name('ayants-droit.destroy');
        Route::post('salaries/{salarie}/carte-assure/generate', [App\Http\Controllers\CarteAssureController::class, 'generate'])->name('cartes-assurees.generate');
        Route::get('cartes-assurees/{carte}/download', [App\Http\Controllers\CarteAssureController::class, 'downloadPdf'])->name('cartes-assurees.download');
    });

    // Gestion des Prestataires et Conventions
    Route::middleware('can:gerer_prestataires')->group(function() {
        Route::resource('prestataires', PrestataireWebController::class)->except(['create', 'show', 'edit']);
        Route::resource('conventions', ConventionWebController::class)->only(['store', 'update', 'destroy']);
    });

    // Gestion des Prestations (et Devis)
    Route::middleware('can:gerer_prestations')->group(function() {
        Route::get('devis', [DevisController::class, 'index'])->name('devis.index');
        Route::post('devis/{id}/transition', [DevisController::class, 'transition'])->name('devis.transition');
        Route::get('prestations', [PrestationController::class, 'index'])->name('prestations.index');
        Route::post('prestations', [PrestationController::class, 'store'])->name('prestations.store');
        Route::get('prestations/export', [PrestationController::class, 'export'])->name('prestations.export');
    });

    // Dossier Médical
    Route::middleware('can:consulter_dossier_medical')->group(function() {
        Route::get('dossier-medical', [DossierMedicalController::class, 'index'])->name('dossier-medical.index');
        Route::get('dossier-medical/{type}/{id}', [DossierMedicalController::class, 'show'])->name('dossier-medical.show');
        Route::post('dossier-medical/{type}/{id}', [DossierMedicalController::class, 'store'])->name('dossier-medical.store');
    });

    // Gestion des rôles et permissions
    Route::middleware('can:gerer_roles')->group(function () {
        Route::resource('roles', RoleController::class)->except(['create', 'edit', 'show']);
    });

    // Facturation et Paiements
    Route::middleware('can:Gérer la facturation')->group(function () {
        Route::get('factures', [App\Http\Controllers\FacturationController::class, 'index'])->name('factures.index');
        Route::get('factures/{facture}', [App\Http\Controllers\FacturationController::class, 'show'])->name('factures.show');
        Route::post('factures/{facture}/paiements', [App\Http\Controllers\FacturationController::class, 'storePaiement'])->name('factures.paiements.store');
    });

    // Gestion des Cotisations
    Route::middleware('can:gerer_cotisations')->group(function() {
        Route::post('cotisations/{id}/payer', [App\Http\Controllers\CotisationController::class, 'payer'])->name('cotisations.payer');
        Route::resource('cotisations', App\Http\Controllers\CotisationController::class);
    });

    // Audit (Historique)
    Route::middleware('can:voir_audit')->group(function() {
        Route::get('audit', [App\Http\Controllers\AuditController::class, 'index'])->name('audit.index');
        Route::get('audit/export', [App\Http\Controllers\AuditController::class, 'export'])->name('audit.export');
    });

    // Paramètres de Couverture
    Route::resource('parametres-couverture', App\Http\Controllers\ParametreCouvertureController::class)->except(['show'])->middleware('can:gerer_parametres_couverture');

    // Gestion Documentaire (Pièces Jointes)
    Route::middleware('can:gerer_pieces_jointes')->group(function() {
        Route::get('pieces-jointes', [App\Http\Controllers\PieceJointeController::class, 'index'])->name('pieces-jointes.index');
        Route::post('pieces-jointes', [App\Http\Controllers\PieceJointeController::class, 'store'])->name('pieces-jointes.store');
        Route::get('pieces-jointes/{pieceJointe}/view', [App\Http\Controllers\PieceJointeController::class, 'show'])->name('pieces-jointes.show');
        Route::get('pieces-jointes/{pieceJointe}/download', [App\Http\Controllers\PieceJointeController::class, 'download'])->name('pieces-jointes.download');
        Route::delete('pieces-jointes/{pieceJointe}', [App\Http\Controllers\PieceJointeController::class, 'destroy'])->name('pieces-jointes.destroy');
    });

    // Gestion des Médias
    Route::middleware('can:gerer_medias')->group(function() {
        Route::get('medias', [App\Http\Controllers\MediaController::class, 'index'])->name('medias.index');
        Route::post('medias', [App\Http\Controllers\MediaController::class, 'store'])->name('medias.store');
        Route::delete('medias/{media}', [App\Http\Controllers\MediaController::class, 'destroy'])->name('medias.destroy');
    });
});
