<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller; // Classe Controller de base de Laravel
use App\Models\Enseignant;          // Modèle Eloquent pour les enseignants
use App\Models\Lecon;               // Modèle Eloquent pour les leçons
use Illuminate\Http\Request;        // Pour gérer la requête HTTP
use Illuminate\Http\JsonResponse;   // Pour typer les réponses JSON
use Illuminate\Support\Facades\DB;  // Pour utiliser les transactions de base de données
use Illuminate\Support\Facades\Log;  // Pour enregistrer les erreurs/infos dans les logs
use Illuminate\Validation\Rule;     // Pour les règles de validation avancées (comme 'unique')

class EnseignantController extends Controller
{
    /**
     * Affiche la liste des enseignants (pour l'affichage principal).
     * Charge les matières associées pour l'affichage dans la table.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            // Récupère tous les enseignants avec les colonnes spécifiées
            // Eager load les matières liées, en sélectionnant seulement id, nom, et classe_id
            $enseignants = Enseignant::with('matieres:id,nom,classe_id')
                                    ->orderBy('nom') // Trie par nom
                                    ->get(['id', 'nom', 'telephone', 'commentaires']); // Colonnes de la table enseignants

            // Renvoie la collection d'enseignants en JSON
            return response()->json($enseignants);

        } catch (\Exception $e) {
            // En cas d'erreur (ex: problème DB), logguer et renvoyer une erreur 500
            Log::error("Erreur lors de la récupération des enseignants (index): " . $e->getMessage());
            return response()->json(['error' => 'Impossible de récupérer la liste des enseignants.'], 500);
        }
    }

    /**
     * Enregistre un nouvel enseignant dans la base de données.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Valider les données entrantes de la requête
        $validatedData = $request->validate([
            'nom' => 'required|string|max:150',
            'telephone' => ['nullable', 'string', 'max:20', Rule::unique('enseignants', 'telephone'), 'regex:/^[0-9\s\+\(\)-]+$/'], // Doit être unique dans la table enseignants
            'commentaires' => 'nullable|string',
            // schedule_json n'est pas géré ici, mais via TeacherScheduleController
            'matiere_ids' => 'nullable|array', // Doit être un tableau (peut être vide)
            'matiere_ids.*' => 'integer|exists:matieres,id' // Chaque ID dans le tableau doit exister dans la table matieres
        ]);

        $enseignant = null;
        try {
            // Démarrer une transaction : si une étape échoue, tout est annulé
            DB::beginTransaction();

            // Créer l'enseignant avec les données validées (sans les matières pour l'instant)
            $enseignant = Enseignant::create([
                'nom' => $validatedData['nom'],
                'telephone' => $validatedData['telephone'] ?? null,
                'commentaires' => $validatedData['commentaires'] ?? null,
                // schedule_json n'est pas défini ici
            ]);

            // Attacher les matières si elles sont fournies dans la requête
            if (!empty($validatedData['matiere_ids'])) {
                // attach() ajoute les liaisons dans la table pivot enseignant_matiere
                $enseignant->matieres()->attach($validatedData['matiere_ids']);
            }

            // Valider la transaction
            DB::commit();

             // Recharger le modèle avec les matières fraîchement liées pour la réponse
             $enseignant->load('matieres:id,nom');

            // Renvoyer l'enseignant créé avec un statut 201 Created
            return response()->json($enseignant, 201);

        } catch (\Exception $e) {
            // Annuler la transaction en cas d'erreur
            DB::rollBack();
            Log::error("Erreur lors de la création de l'enseignant: " . $e->getMessage());
            // Renvoyer une erreur 500 avec détails (si en mode debug)
            return response()->json([
                'error' => 'Erreur serveur lors de la création de l\'enseignant.',
                'details' => $e->getMessage() // Fournir détails peut aider au debug frontend/backend
                ], 500);
        }
    }

    /**
     * Affiche les détails d'un enseignant spécifique.
     * Utilise le Route Model Binding: Laravel trouve l'enseignant basé sur l'ID dans l'URL.
     *
     * @param \App\Models\Enseignant $enseignant
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Enseignant $enseignant): JsonResponse
    {
        try {
            // Charger les matières associées à cet enseignant (juste ID et Nom)
            $enseignant->load('matieres:id,nom');
             // Charger aussi l'emploi du temps ? Non, il a sa propre route dédiée pour le moment.
             // $enseignant->schedule_json; // Déjà chargé car c'est une colonne directe

            // Renvoyer l'enseignant avec ses matières
            return response()->json($enseignant);

        } catch (\Exception $e) {
            Log::error("Erreur lors de l'affichage de l'enseignant ID {$enseignant->id}: " . $e->getMessage());
            return response()->json(['error' => 'Impossible de charger les détails de l\'enseignant.'], 500);
        }
    }

    /**
     * Met à jour les informations d'un enseignant existant.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Enseignant $enseignant
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Enseignant $enseignant): JsonResponse
    {
        // Valider les données (similaire à store, mais unique ignore l'ID courant)
         $validatedData = $request->validate([
            'nom' => 'required|string|max:150',
            'telephone' => ['nullable', 'string', 'max:20', Rule::unique('enseignants', 'telephone')->ignore($enseignant->id), 'regex:/^[0-9\s\+\(\)-]+$/'],
            'commentaires' => 'nullable|string',
            // schedule_json n'est pas géré ici
            'matiere_ids' => 'nullable|array', // Permettre de vider les matières en envoyant []
            'matiere_ids.*' => 'integer|exists:matieres,id'
        ]);

         try {
            // Démarrer transaction
            DB::beginTransaction();

            // Mettre à jour les champs directs de l'enseignant
            $enseignant->update([
                 'nom' => $validatedData['nom'],
                 'telephone' => $validatedData['telephone'] ?? null,
                 'commentaires' => $validatedData['commentaires'] ?? null,
            ]);

            // Synchroniser les matières :
            // sync() va retirer les anciennes liaisons non présentes dans le nouveau tableau
            // et ajouter les nouvelles. Si 'matiere_ids' est absent ou null, on passe un tableau vide
            // pour détacher toutes les matières existantes.
            $enseignant->matieres()->sync($validatedData['matiere_ids'] ?? []);

            // Valider la transaction
            DB::commit();

             // Recharger le modèle avec les matières pour la réponse
             $enseignant->load('matieres:id,nom');

            // Renvoyer l'enseignant mis à jour
            return response()->json($enseignant);

         } catch (\Exception $e) {
            // Annuler la transaction
            DB::rollBack();
            Log::error("Erreur lors de la mise à jour de l'enseignant ID {$enseignant->id}: " . $e->getMessage());
            return response()->json(['error' => 'Erreur serveur lors de la mise à jour.', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * Supprime un enseignant de la base de données.
     *
     * @param \App\Models\Enseignant $enseignant
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Enseignant $enseignant): JsonResponse
    {
        try {
            // Les contraintes onDelete définies dans les migrations devraient gérer :
            // - Suppression des lignes dans enseignant_matiere (cascade)
            // - Mise à NULL de responsable_id dans lecons (set null)
            // - Suppression des lignes dans reservations_studio (cascade)
            $enseignant->delete();

            // Renvoyer un message de succès (ou statut 204 No Content)
            return response()->json(['message' => 'Enseignant supprimé avec succès.'], 200);

        } catch (\Exception $e) {
            Log::error("Erreur lors de la suppression de l'enseignant ID {$enseignant->id}: " . $e->getMessage());
            return response()->json(['error' => 'Erreur serveur lors de la suppression.', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * Retourne les leçons assignées à un enseignant qui sont prêtes à être planifiées.
     * (Statut 'torecord' ou 'redo').
     *
     * @param \App\Models\Enseignant $enseignant
     * @return \Illuminate\Http\JsonResponse
     */
    public function leconsPlanifiables(Enseignant $enseignant): JsonResponse
    {
         try {
            $lecons = Lecon::where('responsable_id', $enseignant->id)
                        ->whereIn('statut', ['torecord', 'redo'])
                        ->with([
                            'chapitre:id,nom', // Charger chapitre
                            'chapitre.matiere:id,nom', // Charger matière via chapitre
                            'chapitre.matiere.classe:id,nom' // Charger classe via matière
                            ])
                        ->orderBy('chapitre_id') // Trier par chapitre
                         // Trier par numéro de leçon (gestion num "1.1", "1.10", "2.1")
                        ->orderByRaw("CAST(REGEXP_SUBSTR(num, '^[0-9]+') AS UNSIGNED), REGEXP_SUBSTR(num, '[^0-9].*$'), num ASC, id ASC")
                        ->select('id', 'num', 'titre', 'chapitre_id') // Colonnes principales leçon
                        ->get();

            Log::debug("Leçons planifiables trouvées pour enseignant {$enseignant->id}: ", $lecons->toArray());
            return response()->json($lecons);

         } catch (\Exception $e) {
             Log::error("Erreur leconsPlanifiables pour Enseignant ID {$enseignant->id}: " . $e->getMessage());
             return response()->json(['error' => 'Impossible de charger les leçons planifiables.'], 500);
         }
    }
}