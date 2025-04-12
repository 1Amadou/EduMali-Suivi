<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class ClasseController extends Controller
{
    /**
     * Affiche la liste de toutes les classes.
     */
    public function index(): JsonResponse
    {
        try {
            return response()->json(Classe::orderBy('nom')->get());
        } catch (\Exception $e) {
            Log::error("Erreur index Classes: " . $e->getMessage());
            return response()->json(['error' => 'Impossible de récupérer les classes.'], 500);
        }
    }

    /**
     * Enregistre une nouvelle classe.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'string', 'max:20', Rule::unique('classes'), 'regex:/^[a-zA-Z0-9_-]+$/'],
            'nom' => ['required', 'string', 'max:255', Rule::unique('classes', 'nom')],
        ]);

        try {
            $classe = Classe::create($validated);
            return response()->json($classe, 201); // 201 Created
        } catch (\Exception $e) {
            Log::error("Erreur store Classe: " . $e->getMessage());
             // Vérifier si c'est une violation de clé unique (double sécurité)
             if ($e->getCode() == '23000' || str_contains($e->getMessage(), 'Duplicate entry')) {
                 return response()->json(['error' => "L'ID de classe '{$validated['id']}' existe déjà."], 409); // Conflict
             }
            return response()->json(['error' => 'Erreur lors de la création de la classe.'], 500);
        }
    }

    /**
     * Affiche une classe spécifique.
     */
    public function show(Classe $classe): JsonResponse // Route Model Binding
    {
        // Optionnel: charger des relations si besoin pour un affichage détaillé
        // $classe->load('matieres');
        return response()->json($classe);
    }

    /**
     * Met à jour une classe existante.
     */
    public function update(Request $request, Classe $classe): JsonResponse // Route Model Binding
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255', Rule::unique('classes', 'nom')->ignore($classe->id)],
            // L'ID (clé primaire) n'est généralement pas modifiable
        ]);

        try {
            $classe->update($validated);
            return response()->json($classe);
        } catch (\Exception $e) {
             Log::error("Erreur update Classe ID {$classe->id}: " . $e->getMessage());
             return response()->json(['error' => 'Erreur lors de la mise à jour de la classe.'], 500);
        }
    }

    /**
     * Supprime une classe.
     */
    public function destroy(Classe $classe): JsonResponse // Route Model Binding
    {
        try {
            // La contrainte onDelete('cascade') dans la migration de 'matieres'
            // s'occupe de supprimer les matières, chapitres, et leçons liés.
            $classe->delete();
            // Renvoyer 204 No Content est aussi une option pour DELETE réussi
            return response()->json(['message' => 'Classe et contenu associé supprimés.'], 200);
        } catch (\Exception $e) {
             Log::error("Erreur destroy Classe ID {$classe->id}: " . $e->getMessage());
             return response()->json(['error' => 'Erreur lors de la suppression de la classe.'], 500);
        }
    }
}