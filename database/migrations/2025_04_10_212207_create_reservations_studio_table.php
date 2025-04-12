<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reservations_studio', function (Blueprint $table) {
            $table->id();
            // Qui réserve
            $table->foreignId('enseignant_id')->constrained('enseignants')->onDelete('cascade');
            // Quelle leçon (optionnel, peut être NULL)
            $table->foreignId('lecon_id')->nullable()->constrained('lecons')->onDelete('set null');
            // Description texte (obligatoire si lecon_id est null, ou pour détails)
            $table->string('lecon_description')->nullable(); // Rendre nullable, validation dans contrôleur
            $table->date('date_reservation');
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->integer('studio_id'); // Ou string si les IDs studio ne sont pas numériques
            $table->timestamps();

            // Index pour la recherche de conflits
            $table->index(['date_reservation', 'studio_id', 'heure_debut', 'heure_fin'], 'idx_planning_conflict');
            // Autres index utiles
            $table->index('enseignant_id');
            $table->index('lecon_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations_studio');
    }
};