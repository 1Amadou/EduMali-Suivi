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
        Schema::create('matieres', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 150);
            $table->string('classe_id', 20); // Clé étrangère (type string comme classes.id)
            $table->foreign('classe_id')
                  ->references('id')->on('classes')
                  ->onDelete('cascade'); // Si la classe est supprimée, la matière l'est aussi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Désactiver temporairement les contraintes pour éviter les erreurs lors du drop
        // Schema::table('matieres', function (Blueprint $table) {
        //     $table->dropForeign(['classe_id']);
        // }); // Pas toujours nécessaire si dropIfExists gère bien l'ordre inverse

        Schema::dropIfExists('matieres');
    }
};