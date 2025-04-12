<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations_studio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enseignant_id')
                  ->constrained('enseignants')
                  ->onDelete('cascade');
            $table->foreignId('lecon_id')
                  ->nullable()
                  ->constrained('lecons')
                  ->onDelete('set null');
            $table->string('lecon_description')->nullable();
            $table->date('date_reservation');
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->integer('studio_id');
            $table->timestamps();

            // Ajout d'index pour optimiser la gestion des conflits de planning
            $table->index(['date_reservation', 'studio_id', 'heure_debut', 'heure_fin'], 'idx_planning_conflict');
            $table->index('enseignant_id');
            $table->index('lecon_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations_studio');
    }
};
