<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chapitre;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ChapitreController extends Controller
{
    // Index non implémenté (généralement chargé via la structure)
    // public function index(): JsonResponse { /* ... */ }

    /**
     * Enregistre un nouveau chapitre.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'matiere_id' => 'required|integer|exists:matieres,id',
            'ordre' => 'nullable|integer',
        ]);
        try {
            $chapitre = Chapitre::create($validated);
            return response()->json($chapitre, 201);
        } catch (\Exception $e) {
             Log::error("Erreur store Chapitre: " . $e->getMessage());
             return response()->json(['error' => 'Erreur lors de la création du chapitre.'], 500);
        }
    }

    /**
     * Affiche un chapitre spécifique.
     */
    public function show(Chapitre $chapitre): JsonResponse
    {
         try {
             $chapitre->load(['matiere:id,nom', 'lecons']); // Charger matière et leçons
             return response()->json($chapitre);
         } catch (\Exception $e) {
              Log::error("Erreur show Chapitre ID {$chapitre->id}: " . $e->getMessage());
              return response()->json(['error' => 'Impossible de charger le chapitre.'], 500);
         }
    }

    /**
     * Met à jour un chapitre existant.
     */
    public function update(Request $request, Chapitre $chapitre): JsonResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'matiere_id' => 'sometimes|required|integer|exists:matieres,id', // Permet de changer de matière
            'ordre' => 'nullable|integer',
        ]);
        try {
            $chapitre->update($validated);
            return response()->json($chapitre);
        } catch (\Exception $e) {
            Log::error("Erreur update Chapitre ID {$chapitre->id}: " . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la mise à jour du chapitre.'], 500);
        }
    }

    /**
     * Supprime un chapitre.
     */
    public function destroy(Chapitre $chapitre): JsonResponse
    {
        try {
            // onDelete('cascade') dans la migration de 'lecons' gère les enfants
            $chapitre->delete();
            return response()->json(['message' => 'Chapitre et leçons associées supprimés.'], 200);
        } catch (\Exception $e) {
             Log::error("Erreur destroy Chapitre ID {$chapitre->id}: " . $e->getMessage());
             return response()->json(['error' => 'Erreur lors de la suppression du chapitre.'], 500);
        }
    }
}