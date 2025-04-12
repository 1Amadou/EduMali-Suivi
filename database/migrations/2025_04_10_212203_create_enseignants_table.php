<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
public function up(): void {
Schema::create('enseignants', function (Blueprint $table) {
$table->id();
$table->string('nom', 150);
$table->string('telephone', 20)->nullable()->unique();
$table->foreignId('matiere_principale_id')->nullable()->constrained('matieres')->onDelete('set null'); // Clé étrangère vers matieres
$table->text('commentaires')->nullable();
$table->text('schedule_json')->nullable();
$table->timestamps();
});
}
public function down(): void { Schema::dropIfExists('enseignants'); }
};