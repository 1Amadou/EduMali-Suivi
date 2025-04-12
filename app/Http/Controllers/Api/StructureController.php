<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller; use App\Models\Classe; use Illuminate\Http\Request; use Illuminate\Http\JsonResponse; use Illuminate\Support\Facades\Log;
class StructureController extends Controller {
    public function index(Request $request): JsonResponse {
         $classeIdFilter = $request->query('classe_id');
         try {
             $query = Classe::query(); if ($classeIdFilter) { $query->where('id', $classeIdFilter); }
             $structure = $query->with([
                    'matieres' => fn($q) => $q->orderBy('nom'),
                    'matieres.enseignantsResponsables:id,nom,matiere_principale_id', // MAJ relation
                    'matieres.chapitres' => fn($q) => $q->orderBy('ordre')->orderBy('nom'),
                    'matieres.chapitres.lecons' => fn($q) => $q->orderByRaw("CAST(REGEXP_SUBSTR(num, '^[0-9]+') AS UNSIGNED), REGEXP_SUBSTR(num, '[^0-9].*$'), num ASC, id ASC")
                 ])->orderBy('nom')->get();
             if ($classeIdFilter && $structure->count() === 1) { return response()->json($structure->first()); }
             elseif ($classeIdFilter && $structure->isEmpty()) { return response()->json(['error' => 'Classe non trouvée.'], 404); }
             else { return response()->json($structure); }
         } catch (\Exception $e) { Log::error("Err index Structure: ".$e->getMessage()); return response()->json(['error'=>'Err load structure.'], 500); }
    }
}