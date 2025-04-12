<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classe extends Model
{
    use HasFactory;

    // Spécifier que l'ID n'est pas numérique et pas auto-incrémenté
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Attributs assignables massivement.
     */
    protected $fillable = [
        'id',
        'nom',
    ];

    /**
     * Relation: Une Classe a Plusieurs Matières.
     */
    public function matieres(): HasMany
    {
        return $this->hasMany(Matiere::class, 'classe_id', 'id');
    }
}