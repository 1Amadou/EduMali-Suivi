<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lecons', function (Blueprint $table) {
            $table->id();
            $table->string('num', 10)->nullable();
            $table->string('titre');
            $table->enum('statut', ['torecord', 'editing', 'review', 'validated', 'redo'])
                  ->default('torecord');
            $table->date('date_enregistrement')->nullable();
            $table->unsignedInteger('duree_min')->nullable();
            $table->text('commentaires')->nullable();
            $table->foreignId('chapitre_id')
                  ->constrained('chapitres')
                  ->onDelete('cascade');
            $table->timestamps();

            // Ajout des index pour améliorer les performances
            $table->index('statut');
            $table->index('chapitre_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lecons');
    }
};
