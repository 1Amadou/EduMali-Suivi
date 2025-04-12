<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class StructureController extends Controller
{
    /**
     * Retourne la structure pédagogique complète ou filtrée par classe.
     */
    public function index(Request $request): JsonResponse
    {
        $classeIdFilter = $request->query('classe_id');

        try {
            $query = Classe::query();
            if ($classeIdFilter) { $query->where('id', $classeIdFilter); }

            // Charger les relations imbriquées avec Eager Loading et tris
            $structure = $query->with([
                    'matieres' => fn($q) => $q->orderBy('nom'),
                    'matieres.chapitres' => fn($q) => $q->orderBy('ordre')->orderBy('nom'),
                    'matieres.chapitres.lecons' => fn($q) => $q->orderByRaw("CAST(REGEXP_SUBSTR(num, '^[0-9]+') AS UNSIGNED), REGEXP_SUBSTR(num, '[^0-9].*$'), num ASC, id ASC"),
                    'matieres.chapitres.lecons.responsable:id,nom' // Charger seulement ID et Nom du responsable
                ])
                ->orderBy('nom')
                ->get();

            // Gérer le retour (liste ou objet unique si filtré)
            if ($classeIdFilter && $structure->count() === 1) {
                return response()->json($structure->first());
            } elseif ($classeIdFilter && $structure->isEmpty()) {
                return response()->json(['error' => 'Classe non trouvée.'], 404);
            } else {
                return response()->json($structure);
            }
        } catch (\Exception $e) {
             Log::error("Erreur index Structure: " . $e->getMessage());
             return response()->json(['error' => 'Impossible de charger la structure pédagogique.'], 500);
        }
    }
}