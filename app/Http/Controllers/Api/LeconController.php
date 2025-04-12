<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lecon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class LeconController extends Controller
{
    // Index non implémenté (généralement chargé via la structure)
    // public function index(): JsonResponse { /* ... */ }

    /**
     * Enregistre une nouvelle leçon.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'num' => 'nullable|string|max:10',
            'titre' => 'required|string|max:255',
            'statut' => ['nullable', Rule::in(['torecord', 'editing', 'review', 'validated', 'redo'])],
            'date_enregistrement' => 'nullable|date_format:Y-m-d',
            'duree_min' => 'nullable|integer|min:0',
            'commentaires' => 'nullable|string',
            'chapitre_id' => 'required|integer|exists:chapitres,id',
            'responsable_id' => 'nullable|integer|exists:enseignants,id',
        ]);
        try {
            $lecon = Lecon::create($validated);
            return response()->json($lecon, 201);
        } catch (\Exception $e) {
             Log::error("Erreur store Lecon: " . $e->getMessage());
             return response()->json(['error' => 'Erreur lors de la création de la leçon.'], 500);
        }
    }

    /**
     * Affiche une leçon spécifique.
     */
    public function show(Lecon $lecon): JsonResponse
    {
        try {
             $lecon->load(['chapitre:id,nom', 'responsable:id,nom']); // Charger chapitre et responsable
             return response()->json($lecon);
         } catch (\Exception $e) {
              Log::error("Erreur show Lecon ID {$lecon->id}: " . $e->getMessage());
              return response()->json(['error' => 'Impossible de charger la leçon.'], 500);
         }
    }

    /**
     * Met à jour une leçon existante.
     * Note: Cette méthode est complète, mais le JS appelle seulement la sauvegarde via bulk update.
     * Elle pourrait être utilisée pour une édition individuelle future.
     */
    public function update(Request $request, Lecon $lecon): JsonResponse
    {
        $validated = $request->validate([
            'num' => 'sometimes|required|string|max:10',
            'titre' => 'sometimes|required|string|max:255',
            'statut' => ['sometimes','required', Rule::in(['torecord', 'editing', 'review', 'validated', 'redo'])],
            'date_enregistrement' => 'nullable|date_format:Y-m-d',
            'duree_min' => 'nullable|integer|min:0',
            'commentaires' => 'nullable|string',
            'chapitre_id' => 'sometimes|required|integer|exists:chapitres,id', // Permet de changer de chapitre
            'responsable_id' => 'nullable|integer|exists:enseignants,id', // Permet de changer/vider le responsable
        ]);
        try {
             // Utiliser fill() puis save() permet de ne mettre à jour que les champs validés présents dans la requête
             $lecon->fill($validated)->save();
             $lecon->load(['chapitre:id,nom', 'responsable:id,nom']); // Recharger relations pour la réponse
             return response()->json($lecon);
         } catch (\Exception $e) {
             Log::error("Erreur update Lecon ID {$lecon->id}: " . $e->getMessage());
             return response()->json(['error' => 'Erreur lors de la mise à jour de la leçon.'], 500);
         }
    }

    /**
     * Supprime une leçon.
     */
    public function destroy(Lecon $lecon): JsonResponse
    {
         try {
            // onDelete('set null') dans reservations_studio gère la référence
             $lecon->delete();
             return response()->json(['message' => 'Leçon supprimée.'], 200);
         } catch (\Exception $e) {
              Log::error("Erreur destroy Lecon ID {$lecon->id}: " . $e->getMessage());
              return response()->json(['error' => 'Erreur lors de la suppression de la leçon.'], 500);
         }
    }
}