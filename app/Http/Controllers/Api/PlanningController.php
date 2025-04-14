<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReservationStudio;
use App\Models\Enseignant; // Importer Enseignant
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log; // Pour le logging d'erreurs
use Carbon\Carbon; // Pour manipuler dates/heures

class PlanningController extends Controller
{
    /**
     * Lister les réservations pour une date donnée.
     * Attend ?date=YYYY-MM-DD en paramètre GET.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Valider que le paramètre 'date' est présent et au bon format
        $validator = Validator::make($request->all(), [
            'date' => 'required|date_format:Y-m-d'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Format de date invalide.', 'errors' => $validator->errors()], 422); // 422 pour validation
        }
        $date = $validator->validated()['date'];

        try {
            // Récupérer les réservations pour cette date
            $bookings = ReservationStudio::with('enseignant:id,nom') // Charger le nom de l'enseignant lié
                ->where('date_reservation', $date)
                ->orderBy('studio_id') // Trier par studio
                ->orderBy('heure_debut') // Puis par heure de début
                ->get();

            return response()->json($bookings);

        } catch (\Exception $e) {
            Log::error("Erreur lors de la récupération du planning pour la date {$date}: " . $e->getMessage());
            return response()->json(['error' => 'Impossible de récupérer le planning.'], 500);
        }
    }

    /**
     * Ajouter une nouvelle réservation avec vérifications de conflits (studio et enseignant).
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Valider les données entrantes
        $validator = Validator::make($request->all(), [
            'enseignant_id' => 'required|integer|exists:enseignants,id', // L'enseignant doit exister
            'lecon_id' => 'required|integer|exists:lecons,id',         // La leçon doit exister
            'lecon_description' => 'nullable|string|max:255',          // Description facultative
            'date_reservation' => 'required|date_format:Y-m-d',        // Format YYYY-MM-DD
            'heure_debut' => 'required|date_format:H:i,H:i:s',       // Format HH:MM ou HH:MM:SS
            'heure_fin' => 'required|date_format:H:i,H:i:s|after:heure_debut', // Après l'heure de début
            'studio_id' => 'required|integer|min:1',                   // ID Studio valide
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Données fournies invalides.', 'errors' => $validator->errors()], 422);
        }
        $validatedData = $validator->validated();

        // Assurer le format H:i:s pour les comparaisons
        $validatedData['heure_debut'] = Carbon::parse($validatedData['heure_debut'])->format('H:i:s');
        $validatedData['heure_fin'] = Carbon::parse($validatedData['heure_fin'])->format('H:i:s');

        try {
            // --- Vérification 1 : Conflit d'horaire pour le STUDIO ---
            $studioConflict = ReservationStudio::where('date_reservation', $validatedData['date_reservation'])
                ->where('studio_id', $validatedData['studio_id'])
                // Vérifie si un créneau existant chevauche le nouveau créneau
                ->where(function ($query) use ($validatedData) {
                    $query->where(function($q) use ($validatedData) { // Commence pendant un créneau existant
                        $q->where('heure_debut', '<', $validatedData['heure_fin'])
                          ->where('heure_fin', '>', $validatedData['heure_debut']);
                    });
                        // ->orWhere(function($q) use ($validatedData) { // Englobe un créneau existant (géré par le dessus)
                        //    $q->where('heure_debut', '>=', $validatedData['heure_debut'])
                        //      ->where('heure_fin', '<=', $validatedData['heure_fin']);
                        // });
                })
                ->exists();

            if ($studioConflict) {
                Log::warning("Conflit Studio pour:", $validatedData);
                return response()->json(['message' => "Conflit d'horaire détecté pour le Studio {$validatedData['studio_id']} à cette date/heure."], 409); // 409 Conflict
            }

            // --- Vérification 2 : Disponibilité de l'ENSEIGNANT (selon son emploi du temps détaillé) ---
            $enseignant = Enseignant::find($validatedData['enseignant_id']);
            if (!$enseignant) {
                 // Normalement impossible grâce à la validation 'exists:enseignants,id'
                 return response()->json(['message' => "Erreur interne: Enseignant non trouvé après validation."], 500);
            }

            $schedule = $enseignant->schedule_json ?? []; // Récupérer son EDT (tableau PHP)
            $reservationDate = Carbon::parse($validatedData['date_reservation']);
            $dayOfWeek = $reservationDate->format('D'); // Format 'Mon', 'Tue', etc.
            $dayOfWeekMapping = ['Mon' => 'Lun', 'Tue' => 'Mar', 'Wed' => 'Mer', 'Thu' => 'Jeu', 'Fri' => 'Ven']; // Mapping vers nos clés
            $jourFr = $dayOfWeekMapping[$dayOfWeek] ?? null;

            $teacherAvailable = false; // Supposer indisponible par défaut pour Sam/Dim
            $conflictingStatus = ''; // Pour stocker le statut bloquant

            if ($jourFr) { // Vérifier seulement pour Lun-Ven
                $startHour = (int) substr($validatedData['heure_debut'], 0, 2);
                $endBookingTime = Carbon::parse($validatedData['heure_fin']);
                $endHour = (int) $endBookingTime->format('H');
                // Si l'heure de fin est pile (ex: 11:00), le dernier créneau à vérifier est 10h
                 if ($endBookingTime->format('i:s') === '00:00' && $validatedData['heure_fin'] !== '00:00:00') {
                     $endHour--;
                 }
                 // Gérer le cas où début=10:30 et fin=11:00 (ne concerne que le créneau de 10h)
                  if ($startHour > $endHour && $validatedData['heure_fin'] !== '00:00:00') {
                     $endHour = $startHour;
                 }

                $allSlotsOk = true;
                for ($h = $startHour; $h <= $endHour; $h++) {
                    if ($h < 8 || $h >= 19) continue; // Ignorer heures hors plage 8h-18h inclus

                    $slotId = sprintf('%s-%02d', $jourFr, $h);
                    $slotStatus = $schedule[$slotId] ?? 'available'; // Disponible par défaut si non défini

                    // *** LOGIQUE CORRIGÉE : Accepter UNIQUEMENT 'available' ***
                    $acceptableStatus = ['available'];

                    if (!in_array($slotStatus, $acceptableStatus)) {
                        Log::warning("Vérif Dispo Enseignant Échec pour Ens {$enseignant->id}: Slot {$slotId} statut '{$slotStatus}'");
                        $conflictingStatus = $slotStatus; // Noter le statut bloquant
                        $allSlotsOk = false;
                        break;
                    }
                }
                $teacherAvailable = $allSlotsOk;

            } else {
                 // Indisponible le weekend
                 return response()->json(['message' => "Conflit : Les réservations ne sont possibles que du Lundi au Vendredi."], 409);
            }

            // Renvoyer une erreur si l'enseignant n'est pas 'available'
            if (!$teacherAvailable) {
                 $statusMap = ['prep' => 'en Préparation', 'record' => 'en Enregistrement', 'unavailable' => 'Non Disponible'];
                 $reason = isset($statusMap[$conflictingStatus]) ? $statusMap[$conflictingStatus] : ($conflictingStatus ?: 'occupé');
                 return response()->json(['message' => "Conflit : L'enseignant est déjà {$reason} sur ce créneau dans son emploi du temps."], 409); // Conflict
            }

            // --- Création de la réservation ---
            // Utiliser une transaction pour créer la réservation ET mettre à jour l'EDT de l'enseignant
            DB::beginTransaction();
            try {
                 // 1. Créer la réservation
                 $reservation = ReservationStudio::create($validatedData);
                 $reservation->load('enseignant:id,nom'); // Charger pour la réponse

                 // 2. Mettre à jour l'emploi du temps de l'enseignant (marquer comme 'record')
                 if ($jourFr) { // S'assurer qu'on a bien un jour mappé
                     $schedule = $enseignant->schedule_json ?? []; // Redondant mais sûr
                     $startHour = (int) substr($validatedData['heure_debut'], 0, 2);
                     $endBookingTime = Carbon::parse($validatedData['heure_fin']);
                     $endHour = (int) $endBookingTime->format('H');
                     if ($endBookingTime->format('i:s') === '00:00' && $validatedData['heure_fin'] !== '00:00:00') { $endHour--; }
                     if ($startHour > $endHour && $validatedData['heure_fin'] !== '00:00:00') { $endHour = $startHour; }

                     for ($h = $startHour; $h <= $endHour; $h++) {
                          if ($h < 8 || $h >= 19) continue;
                          $slotId = sprintf('%s-%02d', $jourFr, $h);
                          $schedule[$slotId] = 'record'; // Marquer comme enregistrement
                     }
                     // Sauvegarder l'EDT mis à jour
                     $enseignant->update(['schedule_json' => $schedule]);
                     Log::info("Mise à jour auto EDT pour Ens {$enseignant->id} suite à réservation {$reservation->id}");
                 }

                 // 3. Valider la transaction
                 DB::commit();

                 return response()->json($reservation, 201); // Created

            } catch (\Exception $e) {
                 // Annuler la transaction en cas d'erreur (création résa OU màj EDT)
                 DB::rollBack();
                 Log::error("Erreur lors de la transaction store Planning: " . $e->getMessage() . "\nData: " . json_encode($validatedData ?? $request->all()));
                 return response()->json(['message' => 'Erreur serveur lors de la création de la réservation ou de la mise à jour de l\'emploi du temps.'], 500);
            }

        } catch (\Exception $e) {
            // Gérer les erreurs qui pourraient survenir AVANT la transaction
            Log::error("Erreur store Planning (pré-transaction): " . $e->getMessage());
            return response()->json(['message' => 'Erreur serveur inattendue.'], 500);
        }
    }

    /**
     * Supprimer une réservation existante.
     * Utilise le Route Model Binding.
     *
     * @param \App\Models\ReservationStudio $reservation
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(ReservationStudio $reservation): JsonResponse
    {
         try {
             // Logique optionnelle : Devrait-on remettre le statut 'available' dans l'EDT
             // de l'enseignant concerné lorsque sa réservation est annulée ?
             // Cela ajouterait de la complexité mais serait logique. À discuter.
             // Pour l'instant, on supprime juste la réservation.

             $reservation->delete();
             // Renvoyer 204 No Content est aussi une bonne pratique
             return response()->json(['message' => 'Réservation annulée avec succès.'], 200);

         } catch (\Exception $e) {
             Log::error("Erreur destroy Planning ID {$reservation->id}: " . $e->getMessage());
             return response()->json(['message' => 'Erreur lors de l\'annulation de la réservation.'], 500);
         }
    }
}