<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller; use App\Models\Classe; use App\Models\Matiere; use App\Models\Chapitre; use App\Models\Lecon; use App\Models\ReservationStudio;
use Illuminate\Http\Request; use Illuminate\Http\JsonResponse; use Illuminate\Support\Facades\DB; use Illuminate\Support\Facades\Log; use Carbon\Carbon;
class DashboardController extends Controller {
    public function index(): JsonResponse {
        try {
            $totalClasses = Classe::count(); $totalMatieres = Matiere::count(); $totalChapitres = Chapitre::count(); $totalLecons = Lecon::count();
            $leconStatusCounts = Lecon::query()->select('statut', DB::raw('count(*) as total'))->groupBy('statut')->pluck('total', 'statut');
            $upcomingBookings = ReservationStudio::where('date_reservation', '>=', Carbon::today()->toDateString())
                ->where('date_reservation', '<=', Carbon::today()->addDays(7)->toDateString())
                ->with('enseignant:id,nom')->orderBy('date_reservation')->orderBy('heure_debut')->limit(5)
                ->get(['id', 'date_reservation', 'heure_debut', 'heure_fin', 'studio_id', 'lecon_description', 'enseignant_id']);
            $dashboardData = ['stats' => ['totalClasses'=>$totalClasses, 'totalMatieres'=>$totalMatieres, 'totalChapitres'=>$totalChapitres, 'totalLecons'=>$totalLecons, 'leconStatusCounts'=>$leconStatusCounts], 'upcomingBookings' => $upcomingBookings];
            return response()->json($dashboardData);
        } catch (\Exception $e) { Log::error("Err Dashboard: ".$e->getMessage()); return response()->json(['error'=>'Err load TdB.'], 500); }
    }
}