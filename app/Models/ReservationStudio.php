<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationStudio extends Model
{
    use HasFactory;

    /**
     * Le nom explicite de la table.
     */
    protected $table = 'reservations_studio';

    /**
     * Attributs assignables massivement.
     */
    protected $fillable = [
        'enseignant_id',
        'lecon_id',
        'lecon_description',
        'date_reservation',
        'heure_debut',
        'heure_fin',
        'studio_id',
    ];

     /**
     * Casts d'attributs.
     */
    protected $casts = [
        'date_reservation' => 'date',
        'studio_id' => 'integer',
        // Les heures sont souvent laissées en string pour H:i:s
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