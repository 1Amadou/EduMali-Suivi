<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Matiere;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class MatiereController extends Controller
{
    /**
     * Affiche la liste de toutes les matières (utilisé pour le formulaire enseignant).
     */
    public function index(): JsonResponse
    {
        try {
             // Charger l'ID et le nom de la classe associée
            $matieres = Matiere::with('classe:id,nom')
                               ->orderBy('nom')
                               ->get(['id', 'nom', 'classe_id']);
            return response()->json($matieres);
        } catch (\Exception $e) {
            Log::error("Erreur index Matieres: " . $e->getMessage());
            return response()->json(['error' => 'Impossible de récupérer les matières.'], 500);
        }
    }

    /**
     * Enregistre une nouvelle matière.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:150',
            'classe_id' => 'required|string|exists:classes,id', // Vérifie que la classe existe
        ]);

        try {
            $matiere = Matiere::create($validated);
            $matiere->load('classe:id,nom'); // Charger la classe pour la réponse
            return response()->json($matiere, 201);
        } catch (\Exception $e) {
             Log::error("Erreur store Matiere: " . $e->getMessage());
             return response()->json(['error' => 'Erreur lors de la création de la matière.'], 500);
        }
    }

    /**
     * Affiche une matière spécifique.
     */
    public function show(Matiere $matiere): JsonResponse
    {
        try {
            $matiere->load(['classe:id,nom', 'chapitres:id,nom,matiere_id']); // Charger classe et chapitres
            return response()->json($matiere);
         } catch (\Exception $e) {
             Log::error("Erreur show Matiere ID {$matiere->id}: " . $e->getMessage());
             return response()->json(['error' => 'Impossible de charger la matière.'], 500);
         }
    }

    /**
     * Met à jour une matière existante.
     */
    public function update(Request $request, Matiere $matiere): JsonResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:150',
            'classe_id' => 'sometimes|required|string|exists:classes,id', // Permet de changer de classe
        ]);
         try {
             $matiere->update($validated);
             $matiere->load('classe:id,nom'); // Recharger la classe pour la réponse
             return response()->json($matiere);
         } catch (\Exception $e) {
             Log::error("Erreur update Matiere ID {$matiere->id}: " . $e->getMessage());
             return response()->json(['error' => 'Erreur lors de la mise à jour de la matière.'], 500);
         }
    }

    /**
     * Supprime une matière.
     */
    public function destroy(Matiere $matiere): JsonResponse
    {
         try {
             // onDelete('cascade') dans la migration de 'chapitres' gère les enfants
             $matiere->delete();
             return response()->json(['message' => 'Matière et contenu associé supprimés.'], 200);
         } catch (\Exception $e) {
              Log::error("Erreur destroy Matiere ID {$matiere->id}: " . $e->getMessage());
              return response()->json(['error' => 'Erreur lors de la suppression de la matière.'], 500);
         }
    }
}