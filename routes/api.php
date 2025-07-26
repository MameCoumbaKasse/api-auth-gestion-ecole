<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\NoteController;
use App\Http\Controllers\API\EleveController;
use App\Http\Controllers\API\ClasseController;
use App\Http\Controllers\API\MatiereController;
use App\Http\Controllers\API\BulletinController;
use App\Http\Controllers\API\EnseignantController;

Route::middleware('guest.api')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/dashboard/global-stats', [DashboardController::class, 'globalStats']);
    Route::get('/dashboard/moyennes-par-classe', [DashboardController::class, 'moyennesParClasse']);
    Route::get('/matieres/periodes', [MatiereController::class, 'periodes']);
    Route::get('/notes/periodes', [NoteController::class, 'periodes']);

    Route::apiResource('classes', ClasseController::class);
    Route::apiResource('matieres', MatiereController::class);
    Route::apiResource('eleves', EleveController::class);
    Route::get('/bulletins/periodes', [BulletinController::class, 'getPeriodes']);
    Route::get('/bulletins', [BulletinController::class, 'index']);
    Route::get('/bulletins/{id}', [BulletinController::class, 'show']);
    Route::get('/bulletins/{id}/download', [BulletinController::class, 'download']);

    Route::middleware(['role:admin'])->group(function () { 
        Route::apiResource('enseignants', EnseignantController::class);
        Route::post('/bulletins/generate', [BulletinController::class, 'generate']);
        Route::post('/bulletins/zip/{periode}', [BulletinController::class, 'downloadZip']);
    });

    // Groupe Admin + Enseignant pour les notes
    Route::middleware(['role:admin,enseignant'])->group(function () {
        Route::apiResource('notes', NoteController::class);
    });

    Route::middleware(['role:eleve'])->group(function () {
        // Voir ses propres bulletins
    });
});