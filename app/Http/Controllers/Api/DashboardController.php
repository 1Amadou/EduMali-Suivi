<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\Matiere;
use App\Models\Chapitre;
use App\Models\Lecon;
use App\Models\ReservationStudio;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB; // Pour utiliser DB::raw
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Récupère et retourne les données agrégées pour le tableau de bord.
     */
    public function index(): JsonResponse
    {
        try {
            // 1. Compter les éléments de structure
            $totalClasses = Classe::count();
            $totalMatieres = Matiere::count();
            $totalChapitres = Chapitre::count();
            $totalLecons = Lecon::count();

            // 2. Compter les leçons par statut
            $leconStatusCounts = Lecon::query()
                ->select('statut', DB::raw('count(*) as total'))
                ->groupBy('statut')
                ->pluck('total', 'statut'); // Donne un tableau associatif ['statut' => count]

            // 3. Récupérer les prochaines réservations (ex: 5 prochaines dans les 7 jours)
            $upcomingBookings = ReservationStudio::where('date_reservation', '>=', Carbon::today()->toDateString())
                ->where('date_reservation', '<=', Carbon::today()->addDays(7)->toDateString())
                ->with('enseignant:id,nom') // Charger le nom de l'enseignant
                ->orderBy('date_reservation')
                ->orderBy('heure_debut')
                ->limit(5) // Limiter pour ne pas surcharger
                ->get([
                    'id', 'date_reservation', 'heure_debut', 'heure_fin',
                    'studio_id', 'lecon_description', 'enseignant_id' // Inclure enseignant_id pour la relation
                ]);

            // Combiner toutes les données
            $dashboardData = [
                'stats' => [
                    'totalClasses' => $totalClasses,
                    'totalMatieres' => $totalMatieres,
                    'totalChapitres' => $totalChapitres,
                    'totalLecons' => $totalLecons,
                    'leconStatusCounts' => $leconStatusCounts,
                ],
                'upcomingBookings' => $upcomingBookings,
            ];

            return response()->json($dashboardData);

        } catch (\Exception $e) {
            Log::error("Erreur DashboardController@index: " . $e->getMessage());
            return response()->json(['error' => 'Impossible de charger les données du tableau de bord.'], 500);
        }
    }
}
