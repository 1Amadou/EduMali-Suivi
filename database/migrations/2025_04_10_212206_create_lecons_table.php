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
        Schema::create('lecons', function (Blueprint $table) {
            $table->id();
            $table->string('num', 10)->nullable(); // Numéro comme "1.1a"
            $table->string('titre');
            $table->enum('statut', ['torecord', 'editing', 'review', 'validated', 'redo'])->default('torecord');
            $table->date('date_enregistrement')->nullable();
            $table->integer('duree_min')->unsigned()->nullable(); // Assurer positif
            $table->text('commentaires')->nullable();
            // Clé vers chapitres
            $table->foreignId('chapitre_id')->constrained('chapitres')->onDelete('cascade');
            // Clé vers enseignants (responsable)
            $table->foreignId('responsable_id')->nullable()->constrained('enseignants')->onDelete('set null'); // Si prof supprimé, responsable devient NULL
            $table->timestamps();

            // Index utiles
            $table->index('statut');
            $table->index('chapitre_id');
            $table->index('responsable_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lecons');
    }
};