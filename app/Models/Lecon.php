<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lecon extends Model
{
    use HasFactory;

    // Retrait de responsable_id
    protected $fillable = [
        'num',
        'titre',
        'statut',
        'date_enregistrement',
        'duree_min',
        'commentaires',
        'chapitre_id',
    ];

    protected $casts = [
        'date_enregistrement' => 'date',
        'duree_min' => 'integer',
    ];

    /**
     * Relation: Une Leçon appartient à un Chapitre.
     */
    public function chapitre(): BelongsTo
    {
        return $this->belongsTo(Chapitre::class);
    }

    /**
     * Relation: Une Leçon peut avoir Plusieurs Réservations Studio.
     */
    public function reservationsStudio(): HasMany
    {
        return $this->hasMany(ReservationStudio::class);
    }

    // Pas de relation directe vers le responsable ici
}