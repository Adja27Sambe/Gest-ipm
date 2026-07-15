<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EntrepriseController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('entreprises', EntrepriseController::class);
Route::patch('entreprises/{entreprise}/status', [EntrepriseController::class, 'updateStatus']);
