<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('country_codes', function (Blueprint $table) {
            $table->id();
            $table->string('name');           // Nom du pays
            $table->string('code', 10);       // Code indicatif, ex: +228
            $table->string('iso', 3);         // Code ISO, ex: TGO
            $table->string('flag_url')->nullable(); // URL du drapeau
            $table->unsignedTinyInteger('digits'); // Nombre de chiffres attendus
            $table->string('format');         // Format d'affichage, ex: XX XX XX XX
            $table->string('pattern');        // Regex de validation, ex: /^\d{8}$/
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('country_codes');
    }
};
