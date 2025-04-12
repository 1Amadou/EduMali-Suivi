<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lecon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule; // <--- CETTE LIGNE EST ESSENTIELLE !

class LeconBulkUpdateController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            '*.id' => 'required|integer|exists:lecons,id',
            // Utilise la classe Rule importée ci-dessus
            '*.statut' => ['nullable', Rule::in(['torecord', 'editing', 'review', 'validated', 'redo'])],
            '*.date_enregistrement' => 'nullable|date_format:Y-m-d',
            '*.duree_min' => 'nullable|integer|min:0',
            '*.commentaires' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Données invalides.', 'errors' => $validator->errors()], 422);
        }

        $validatedDataArray = $validator->validated();
        $updatedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($validatedDataArray as $lessonData) {
                $affected = Lecon::where('id', $lessonData['id'])->update(
                     collect($lessonData)->only((new Lecon)->getFillable())->toArray()
                );
                 $updatedCount++;
            }
            DB::commit();
            return response()->json(['message' => "$updatedCount leçon(s) mise(s) à jour."]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur LeconBulkUpdate: " . $e->getMessage());
            return response()->json(['error' => 'Erreur MàJ Leçons.', 'details' => $e->getMessage()], 500);
        }
    }
}