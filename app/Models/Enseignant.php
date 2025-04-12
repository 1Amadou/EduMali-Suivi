<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- Ajouté
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enseignant extends Model
{
    use HasFactory;

    // Ajout de matiere_principale_id
    protected $fillable = [
        'nom',
        'telephone',
        'commentaires',
        'schedule_json',
        'matiere_principale_id', // Clé étrangère vers sa matière principale
    ];

    /**
     * Casts d'attributs.
     */
    protected $casts = [
        'schedule_json' => 'array', // Gère JSON <-> Tableau PHP
    ];

    /**
     * Relation: Un Enseignant a UNE Matière Principale (peut être null).
     */
    public function matierePrincipale(): BelongsTo
    {
        return $this->belongsTo(Matiere::class, 'matiere_principale_id');
    }

    /**
     * Relation: Un Enseignant fait Plusieurs Réservations Studio.
     */
    public function reservationsStudio(): HasMany
    {
        return $this->hasMany(ReservationStudio::class);
    }

    // Note: La relation directe pour trouver les leçons dont un prof est responsable
    // se fait maintenant via sa matière principale:
    // $enseignant->matierePrincipale->chapitres()->with('lecons')->get() ... puis itérer.
    // Ou une relation HasManyThrough si nécessaire.
}