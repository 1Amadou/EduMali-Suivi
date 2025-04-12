<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Matiere extends Model
{
    use HasFactory;

    /**
     * Attributs assignables massivement.
     */
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
     * Relation: Une Matière peut être enseignée par Plusieurs Enseignants (Many-to-Many).
     */
    public function enseignants(): BelongsToMany
    {
        return $this->belongsToMany(Enseignant::class, 'enseignant_matiere', 'matiere_id', 'enseignant_id');
    }
}