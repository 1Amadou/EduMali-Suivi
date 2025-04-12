<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EnseignantController;
use App\Http\Controllers\Api\ClasseController;
use App\Http\Controllers\Api\MatiereController;
use App\Http\Controllers\Api\ChapitreController;
use App\Http\Controllers\Api\LeconController;
use App\Http\Controllers\Api\StructureController;
use App\Http\Controllers\Api\LeconBulkUpdateController;
use App\Http\Controllers\Api\PlanningController; 
use App\Http\Controllers\Api\TeacherScheduleController;
use App\Http\Controllers\Api\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
| Toutes ces routes auront automatiquement le préfixe /api/
|
*/

// --- Structure Pédagogique ---
// Route pour obtenir la hiérarchie complète (ou filtrée via ?classe_id=...)
Route::get('/structure', [StructureController::class, 'index']);

// Routes CRUD RESTful pour chaque niveau de la structure
Route::apiResource('/classes', ClasseController::class);
Route::apiResource('/matieres', MatiereController::class);
Route::apiResource('/chapitres', ChapitreController::class);
Route::apiResource('/lecons', LeconController::class);

// --- Feuille de Route ---
// Route pour la sauvegarde en masse des leçons modifiées
Route::post('/lecons-bulk-update', [LeconBulkUpdateController::class, 'store']);

// --- Enseignants ---
Route::apiResource('/enseignants', EnseignantController::class);

// --- Emploi du Temps Enseignant ---
Route::get('/enseignants/{enseignant}/schedule', [TeacherScheduleController::class, 'show'])
    ->name('enseignants.schedule.show'); // Nommer la route (optionnel mais bonne pratique)

Route::put('/enseignants/{enseignant}/schedule', [TeacherScheduleController::class, 'update'])
    ->name('enseignants.schedule.update');


// --- Planning ---
Route::get('/planning', [PlanningController::class, 'index'])->name('planning.index');
Route::post('/planning', [PlanningController::class, 'store'])->name('planning.store');
// Utilise {reservation} pour le Route Model Binding dans la méthode destroy
Route::delete('/planning/{reservation}', [PlanningController::class, 'destroy'])->name('planning.destroy');

// --- Route pour obtenir les leçons à planifier pour un enseignant ---
Route::get('/enseignants/{enseignant}/lecons-planifiables', [EnseignantController::class, 'leconsPlanifiables'])
     ->name('enseignants.lecons.planifiables'); // Nom optionnel


// --- Dashboard ---
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
// --- Authentification (Exemple par défaut de Laravel) ---
// Cette route n'est utile que si vous mettez en place l'authentification Sanctum
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });