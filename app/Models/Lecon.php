<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lecon extends Model
{
    use HasFactory;

    /**
     * Attributs assignables massivement.
     */
    protected $fillable = [
        'num',
        'titre',
        'statut',
        'date_enregistrement',
        'duree_min',
        'commentaires',
        'chapitre_id',
        'responsable_id',
    ];

     /**
     * Casts d'attributs.
     */
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
     * Relation: Une Leçon a un Enseignant responsable (peut être null).
     */
    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Enseignant::class, 'responsable_id');
    }

     /**
     * Relation: Une Leçon peut avoir Plusieurs Réservations Studio.
     */
    public function reservationsStudio(): HasMany
    {
        return $this->hasMany(ReservationStudio::class);
    }
}