<?php
use Illuminate\Support\Facades\Route;

// Route pour afficher notre application principale (Single Page App style)
Route::get('/', function () {
    return view('app_layout'); // Charge resources/views/app_layout.blade.php
});

// ---- Routes API ----
// Nous ajouterons les routes pour les enseignants, etc., ici,
// en les liant aux contrôleurs que nous allons créer.
// Exemple (sera ajouté à l'étape suivante) :
// use App\Http\Controllers\EnseignantController;
// Route::get('/api/enseignants', [EnseignantController::class, 'index']);