<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enseignant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TeacherScheduleController extends Controller
{
    /**
     * Affiche l'emploi du temps d'un enseignant.
     */
    public function show(Enseignant $enseignant): JsonResponse
    {
        try {
            // Le cast 'array' dans le modèle Enseignant décode schedule_json
            $schedule = $enseignant->schedule_json ?? (object)[]; // Renvoyer objet vide si null
            return response()->json($schedule);
        } catch (\Exception $e) {
            Log::error("Erreur show TeacherSchedule ID {$enseignant->id}: " . $e->getMessage());
            return response()->json(['error' => 'Impossible de charger l\'emploi du temps.'], 500);
        }
    }

    /**
     * Met à jour l'emploi du temps complet d'un enseignant.
     */
    public function update(Request $request, Enseignant $enseignant): JsonResponse
    {
        // Valider que les données reçues sont bien un tableau/objet JSON
        // request->all() renvoie un tableau PHP si le JSON est valide
        $newSchedule = $request->all(); // Aucune validation stricte du contenu ici, on fait confiance au frontend
                                        // On pourrait ajouter une validation si nécessaire: 'nullable|array'

        // S'assurer que $newSchedule est bien un tableau pour le cast du modèle
        if (!is_array($newSchedule)) {
             $newSchedule = []; // Ou renvoyer une erreur 400
        }

        try {
            // Le cast 'array' du modèle Enseignant encode $newSchedule en JSON avant sauvegarde
            $enseignant->update(['schedule_json' => $newSchedule]);
            return response()->json(['message' => 'Emploi du temps enregistré.']);
        } catch (\Exception $e) {
            Log::error("Erreur update TeacherSchedule ID {$enseignant->id}: " . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de l\'enregistrement.', 'details' => $e->getMessage()], 500);
        }
    }
}