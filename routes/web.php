<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DemandeController;
use App\Http\Controllers\EntrepriseController;

Route::get('/', function () {
    return redirect()->route('demandes.index');
});

Route::resource('demandes', DemandeController::class);
Route::resource('entreprises', EntrepriseController::class);
