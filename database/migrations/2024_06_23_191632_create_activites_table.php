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
        Schema::create('fcmcentral_activites', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('libelle_long');
            $table->string('libelle_court');
            $table->string('url')->nullable();
            $table->string('duree_validite')->nullable();
            $table->string('prerequis')->nullable();
            $table->enum('type', ['STAGE', 'OBJECTIF']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fcmcentral_activites');
    }
};
