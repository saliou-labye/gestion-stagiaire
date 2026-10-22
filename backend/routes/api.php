<?php

use App\Http\Controllers\Api\AffectationController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EncadreurController;
use App\Http\Controllers\Api\EvaluationController;
use App\Http\Controllers\Api\MeController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\StagiaireController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // Accessible à tous les rôles connectés
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthController::class, 'user']);

    // Espace personnel en lecture seule
    Route::get('me/stagiaire', [MeController::class, 'stagiaire'])->middleware('role:stagiaire');
    Route::get('me/encadreur', [MeController::class, 'encadreur'])->middleware('role:encadreur');

    // Gestion complète : réservée aux administrateurs
    Route::middleware('role:admin')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index']);
        Route::apiResource('stagiaires', StagiaireController::class);
        Route::apiResource('encadreurs', EncadreurController::class);
        Route::apiResource('services', ServiceController::class);
        Route::apiResource('affectations', AffectationController::class);
        Route::apiResource('evaluations', EvaluationController::class);
    });
});
