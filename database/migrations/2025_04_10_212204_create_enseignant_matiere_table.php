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
        Schema::create('enseignant_matiere', function (Blueprint $table) {
            // Clé étrangère vers enseignants (type bigint unsigned)
            $table->foreignId('enseignant_id')->constrained('enseignants')->onDelete('cascade');
            // Clé étrangère vers matieres (type bigint unsigned)
            $table->foreignId('matiere_id')->constrained('matieres')->onDelete('cascade');

            // Clé primaire composite
            $table->primary(['enseignant_id', 'matiere_id']);

            // Pas de timestamps ici généralement
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enseignant_matiere');
    }
};