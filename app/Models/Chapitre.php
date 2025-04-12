<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chapitre extends Model
{
    use HasFactory;

    /**
     * Attributs assignables massivement.
     */
    protected $fillable = [
        'nom',
        'matiere_id',
        'ordre',
    ];

    /**
     * Relation: Un Chapitre appartient à une Matière.
     */
    public function matiere(): BelongsTo
    {
        return $this->belongsTo(Matiere::class);
    }

    /**
     * Relation: Un Chapitre a Plusieurs Leçons.
     */
    public function lecons(): HasMany
    {
        return $this->hasMany(Lecon::class);
    }
}