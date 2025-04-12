<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller; use App\Models\Enseignant; use Illuminate\Http\Request; use Illuminate\Http\JsonResponse; use Illuminate\Support\Facades\Log;
class TeacherScheduleController extends Controller {
    public function show(Enseignant $enseignant): JsonResponse { try { $schedule = $enseignant->schedule_json ?? (object)[]; return response()->json($schedule); } catch (\Exception $e) { Log::error("Err show TeacherSchedule {$enseignant->id}: ".$e->getMessage()); return response()->json(['error'=>'Err load EDT.'], 500); } }
    public function update(Request $request, Enseignant $enseignant): JsonResponse { $newSchedule = $request->all(); if (!is_array($newSchedule)) { $newSchedule = []; } try { $enseignant->update(['schedule_json' => $newSchedule]); return response()->json(['message' => 'EDT enregistré.']); } catch (\Exception $e) { Log::error("Err update TeacherSchedule {$enseignant->id}: ".$e->getMessage()); return response()->json(['error'=>'Err sauvegarde EDT.', 'details'=>$e->getMessage()], 500); } }
}