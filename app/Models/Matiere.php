<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Matiere extends Model
{
    use HasFactory;

    // enseignant_responsable_id est retiré, l'enseignant a matiere_principale_id
    protected $fillable = [
        'nom',
        'classe_id',
    ];

    /**
     * Relation: Une Matière appartient à une Classe.
     */
    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'classe_id', 'id');
    }

    /**
     * Relation: Une Matière a Plusieurs Chapitres.
     */
    public function chapitres(): HasMany
    {
        return $this->hasMany(Chapitre::class);
    }

    /**
     * Relation: Une Matière peut être la matière principale de Plusieurs Enseignants.
     */
    public function enseignantsResponsables(): HasMany
    {
        // Cherche les enseignants dont la clé étrangère 'matiere_principale_id' pointe vers cette matière
        return $this->hasMany(Enseignant::class, 'matiere_principale_id');
    }
}