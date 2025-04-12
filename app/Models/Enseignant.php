<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enseignant extends Model
{
    use HasFactory;

    /**
     * Attributs assignables massivement.
     */
    protected $fillable = [
        'nom',
        'telephone',
        'commentaires',
        'schedule_json', // Emploi du temps
    ];

    /**
     * Casts d'attributs (conversion automatique).
     */
    protected $casts = [
        'schedule_json' => 'array', // Gère JSON <-> Tableau PHP
    ];

    /**
     * Relation: Un Enseignant enseigne Plusieurs Matières (Many-to-Many).
     */
    public function matieres(): BelongsToMany
    {
        return $this->belongsToMany(Matiere::class, 'enseignant_matiere', 'enseignant_id', 'matiere_id');
    }

    /**
     * Relation: Un Enseignant est responsable de Plusieurs Leçons.
     */
    public function leconsResponsable(): HasMany
    {
        return $this->hasMany(Lecon::class, 'responsable_id');
    }

    /**
     * Relation: Un Enseignant fait Plusieurs Réservations Studio.
     */
    public function reservationsStudio(): HasMany
    {
        return $this->hasMany(ReservationStudio::class);
    }
}