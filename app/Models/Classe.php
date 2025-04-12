<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classe extends Model
{
    use HasFactory;

    public $incrementing = false; // Clé primaire non incrémentée (VARCHAR)
    protected $keyType = 'string'; // Type de la clé primaire

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