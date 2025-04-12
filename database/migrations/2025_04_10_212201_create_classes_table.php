<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::create('classes', function (Blueprint $table) { $table->string('id', 20)->primary(); $table->string('nom', 255)->unique(); $table->timestamps(); }); }
    public function down(): void { Schema::dropIfExists('classes'); }
};