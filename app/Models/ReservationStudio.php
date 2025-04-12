<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationStudio extends Model
{
    use HasFactory;

    protected $table = 'reservations_studio';

    protected $fillable = [
        'enseignant_id',
        'lecon_id',
        'lecon_description',
        'date_reservation',
        'heure_debut',
        'heure_fin',
        'studio_id',
    ];

    protected $casts = [
        'date_reservation' => 'date',
        'studio_id' => 'integer',
    ];

    /**
     * Relation: Une Réservation appartient à un Enseignant.
     */
    public function enseignant(): BelongsTo
    {
        return $this->belongsTo(Enseignant::class);
    }

    /**
     * Relation: Une Réservation peut appartenir à une Leçon (nullable).
     */
    public function lecon(): BelongsTo
    {
        return $this->belongsTo(Lecon::class);
    }
}