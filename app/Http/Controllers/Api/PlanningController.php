<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReservationStudio;
use App\Models\Enseignant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PlanningController extends Controller
{
    /** Lister les réservations pour une date donnée. */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), ['date' => 'required|date_format:Y-m-d']);
        if ($validator->fails()) { return response()->json(['message' => 'Format date invalide.', 'errors' => $validator->errors()], 422); }
        $date = $validator->validated()['date'];
        try {
            $bookings = ReservationStudio::with('enseignant:id,nom')
                ->where('date_reservation', $date)
                ->orderBy('studio_id')->orderBy('heure_debut')->get();
            return response()->json($bookings);
        } catch (\Exception $e) { Log::error("Erreur index Planning {$date}: " . $e->getMessage()); return response()->json(['error' => 'Impossible de récupérer planning.'], 500); }
    }

    /** Ajouter une nouvelle réservation avec vérifications. */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'enseignant_id' => 'required|integer|exists:enseignants,id',
            'lecon_id' => 'required|integer|exists:lecons,id',
            'lecon_description' => 'nullable|string|max:255',
            'date_reservation' => 'required|date_format:Y-m-d',
            'heure_debut' => 'required|date_format:H:i,H:i:s',
            'heure_fin' => 'required|date_format:H:i,H:i:s|after:heure_debut',
            'studio_id' => 'required|integer|min:1',
        ]);
        if ($validator->fails()) { return response()->json(['message' => 'Données invalides.', 'errors' => $validator->errors()], 422); }
        $validatedData = $validator->validated();
        $validatedData['heure_debut'] = Carbon::parse($validatedData['heure_debut'])->format('H:i:s');
        $validatedData['heure_fin'] = Carbon::parse($validatedData['heure_fin'])->format('H:i:s');

        try {
            // Vérif 1 : Conflit Studio
            $studioConflict = ReservationStudio::where('date_reservation', $validatedData['date_reservation'])
                ->where('studio_id', $validatedData['studio_id'])
                ->where('heure_fin', '>', $validatedData['heure_debut'])
                ->where('heure_debut', '<', $validatedData['heure_fin'])
                ->exists();
            if ($studioConflict) { return response()->json(['message' => "Conflit Studio détecté."], 409); }

            // Vérif 2 : Disponibilité Enseignant
            $enseignant = Enseignant::find($validatedData['enseignant_id']);
            if (!$enseignant) { return response()->json(['message' => "Enseignant non trouvé."], 404); }
            $schedule = $enseignant->schedule_json ?? [];
            $reservationDate = Carbon::parse($validatedData['date_reservation']);
            $dayOfWeek = $reservationDate->format('D');
            $dayOfWeekMapping = ['Mon' => 'Lun', 'Tue' => 'Mar', 'Wed' => 'Mer', 'Thu' => 'Jeu', 'Fri' => 'Ven'];
            $jourFr = $dayOfWeekMapping[$dayOfWeek] ?? null;
            $teacherAvailable = false;
            if ($jourFr) {
                 $startHour = (int) substr($validatedData['heure_debut'], 0, 2);
                 $endBookingTime = Carbon::parse($validatedData['heure_fin']);
                 $endHour = (int) $endBookingTime->format('H');
                 if ($endBookingTime->format('i:s') === '00:00' && $validatedData['heure_fin'] !== '00:00:00') { $endHour--; }
                 if ($startHour > $endHour && $validatedData['heure_fin'] !== '00:00:00') { $endHour = $startHour; }

                $allSlotsOk = true;
                for ($h = $startHour; $h <= $endHour; $h++) {
                    if ($h < 8 || $h >= 19) continue; // Plage horaire 8h-19h
                    $slotId = sprintf('%s-%02d', $jourFr, $h);
                    $slotStatus = $schedule[$slotId] ?? 'available';
                    $acceptableStatus = ['available', 'prep', 'record'];
                    if (!in_array($slotStatus, $acceptableStatus)) { Log::warning("Dispo Enseignant Échec: Slot {$slotId} statut {$slotStatus}"); $allSlotsOk = false; break; }
                } $teacherAvailable = $allSlotsOk;
            } else { return response()->json(['message' => "Réservations Lun-Ven seulement."], 409); }

            if (!$teacherAvailable) { return response()->json(['message' => "Conflit: Enseignant non disponible sur ce créneau."], 409); }

            // Création Réservation
            $reservation = ReservationStudio::create($validatedData);
            $reservation->load('enseignant:id,nom');
            return response()->json($reservation, 201);

        } catch (\Exception $e) { Log::error("Erreur store Planning: " . $e->getMessage() . "\nData: " . json_encode($validatedData ?? $request->all())); return response()->json(['message' => 'Erreur serveur lors de la création.'], 500); }
    }

    /** Supprimer une réservation. */
    public function destroy(ReservationStudio $reservation): JsonResponse
    {
         try { $reservation->delete(); return response()->json(['message' => 'Réservation annulée.'], 200);
         } catch (\Exception $e) { Log::error("Erreur destroy Planning ID {$reservation->id}: " . $e->getMessage()); return response()->json(['message' => 'Erreur lors de l\'annulation.'], 500); }
    }
}