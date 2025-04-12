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
        Schema::create('enseignants', function (Blueprint $table) {
            $table->id(); // Clé primaire auto-incrémentée (bigint unsigned)
            $table->string('nom', 150);
            $table->string('telephone', 20)->nullable()->unique();
            $table->text('commentaires')->nullable();
            $table->text('schedule_json')->nullable(); // Pour l'emploi du temps JSON
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enseignants');
    }
};