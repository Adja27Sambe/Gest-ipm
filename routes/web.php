<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DemandeController;
use App\Http\Controllers\EntrepriseController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Web\PraticienWebController;
use App\Http\Controllers\Web\PharmacieWebController;
use App\Http\Controllers\Web\ConventionWebController;
use App\Http\Controllers\PrestationController;
use App\Http\Controllers\DossierMedicalController;

use App\Http\Controllers\UtilisateurController;
use Illuminate\Support\Facades\Hash;
use App\Models\Salarie;

// Route temporaire pour générer un code de démonstration
Route::get('generate-demo', function () {
    $salarie = Salarie::first();
    if (!$salarie) {
        return "Aucun salarié trouvé dans la base de données. Créez-en un d'abord.";
    }

    $demoCode = '1234';
    $salarie->code_securite = Hash::make($demoCode);
    $salarie->save();

    return "<h2>DÉMO ESPACE BÉNÉFICIAIRE</h2>" .
           "<p><strong>Matricule :</strong> " . $salarie->matricule . "</p>" .
           "<p><strong>Code de sécurité :</strong> " . $demoCode . "</p>" .
           "<p><a href='" . route('participant.login') . "'>Aller à la page de connexion</a></p>";
});

// Routes pour l'espace bénéficiaire (Participants)
Route::prefix('espace-beneficiaire')->name('participant.')->group(function () {
    Route::get('login', [App\Http\Controllers\Participant\AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [App\Http\Controllers\Participant\AuthController::class, 'login']);

    Route::middleware('auth:participant')->group(function () {
        Route::match(['get', 'post'], 'logout', [App\Http\Controllers\Participant\AuthController::class, 'logout'])->name('logout');
        Route::get('dashboard', [App\Http\Controllers\Participant\DashboardController::class, 'index'])->name('dashboard');
        
        Route::get('demandes/create', [App\Http\Controllers\Participant\DemandeController::class, 'create'])->name('demandes.create');
        Route::post('demandes', [App\Http\Controllers\Participant\DemandeController::class, 'store'])->name('demandes.store');
    });
});

// Routes publiques
Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('login', [AuthController::class, 'login']);

// Routes protégées
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect()->route('reporting.index');
    });

    Route::get('/reporting', [App\Http\Controllers\ReportingController::class, 'index'])->name('reporting.index');

    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('demandes/recherche-participant', [App\Http\Controllers\DemandeController::class, 'rechercheParticipant'])->name('demandes.recherche');
    Route::get('dashboard/demandes', [App\Http\Controllers\DemandeDashboardController::class, 'index'])->name('dashboard.demandes');
    Route::get('demandes/{id}/pdf', [App\Http\Controllers\Api\DemandeApiController::class, 'generatePdf'])->name('demandes.pdf');
    
    // Validation des demandes
    Route::get('demandes/validation', [App\Http\Controllers\ValidationDemandeController::class, 'index'])->name('demandes.validation.index');
    Route::post('demandes/{demande}/approuver', [App\Http\Controllers\ValidationDemandeController::class, 'approuver'])->name('demandes.approuver');
    Route::post('demandes/{demande}/rejeter', [App\Http\Controllers\ValidationDemandeController::class, 'rejeter'])->name('demandes.rejeter');

    Route::resource('demandes', DemandeController::class)->middleware('can:gerer_demandes');
    Route::resource('entreprises', EntrepriseController::class)->middleware('can:gerer_entreprises');
    Route::get('entreprises/{entreprise}/next-matricule', [EntrepriseController::class, 'getNextMatricule'])->name('entreprises.next-matricule');
    
    Route::middleware('can:gerer_salaries')->group(function() {
        Route::resource('salaries', App\Http\Controllers\SalarieController::class)->parameters([
            'salaries' => 'salarie'
        ]);
        Route::post('salaries/{salarie}/ayants-droit', [App\Http\Controllers\AyantDroitController::class, 'store'])->name('ayants-droit.store');
        Route::get('ayants-droit/{ayantDroit}/edit', [App\Http\Controllers\AyantDroitController::class, 'edit'])->name('ayants-droit.edit');
        Route::put('ayants-droit/{ayantDroit}', [App\Http\Controllers\AyantDroitController::class, 'update'])->name('ayants-droit.update');
        Route::delete('ayants-droit/{ayantDroit}', [App\Http\Controllers\AyantDroitController::class, 'destroy'])->name('ayants-droit.destroy');
        Route::post('salaries/{salarie}/carte-assure/generate', [App\Http\Controllers\CarteAssureController::class, 'generate'])->name('cartes-assurees.generate');
        Route::get('cartes-assurees/{carte}', [App\Http\Controllers\CarteAssureController::class, 'show'])->name('cartes-assurees.show');
        Route::get('cartes-assurees/{carte}/download', [App\Http\Controllers\CarteAssureController::class, 'downloadPdf'])->name('cartes-assurees.download');
    });

    // Gestion des Praticiens et Pharmacies (Anciennement Prestataires)
    Route::middleware('can:gerer_prestataires')->group(function() {
        Route::resource('praticiens', PraticienWebController::class)->except(['create', 'show', 'edit']);
        Route::resource('pharmacies', PharmacieWebController::class)->except(['create', 'show', 'edit']);
        Route::resource('conventions', ConventionWebController::class)->only(['store', 'update', 'destroy']);
    });

    // Gestion des Prestations (et Devis)
    Route::middleware('can:gerer_prestations')->group(function() {
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

    // Gestion des utilisateurs, rôles et permissions
    Route::middleware('can:gerer_roles')->group(function () {
        Route::resource('roles', RoleController::class)->except(['create', 'edit', 'show']);
        Route::resource('utilisateurs', UtilisateurController::class);
    });

    // Facturation et Paiements
    Route::middleware('can:Gérer la facturation')->group(function () {
        Route::get('factures', [App\Http\Controllers\FacturationController::class, 'index'])->name('factures.index');
        Route::get('factures/create', [App\Http\Controllers\FacturationController::class, 'create'])->name('factures.create');
        Route::post('factures', [App\Http\Controllers\FacturationController::class, 'store'])->name('factures.store');
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
