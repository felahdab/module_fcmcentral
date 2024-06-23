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
        Schema::create('fcmcentral_fonction_parcours', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('parcours_id')->references('id')->on('fcmcentral_parcours');
			$table->foreignUuid('fonction_id')->references('id')->on('fcmcentral_fonctions');

            $table->timestamps();
        });

        Schema::create('fcmcentral_competence_fonction', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('fonction_id')->references('id')->on('fcmcentral_fonctions');
            $table->foreignUuid('competence_id')->references('id')->on('fcmcentral_competences');

            $table->timestamps();
        });

        Schema::create('fcmcentral_competence_savoirfaire', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('competence_id')->references('id')->on('fcmcentral_competences');
            $table->foreignUuid('savoirfaire_id')->references('id')->on('fcmcentral_savoir_faires');

            $table->timestamps();
        });

        Schema::create('fcmcentral_savoirfaire_stage', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('savoirfaire_id')->references('id')->on('fcmcentral_savoir_faires');
            $table->foreignUuid('stage_id')->references('id')->on('fcmcentral_stages');

            $table->timestamps();
        });

        Schema::create('fcmcentral_savoirfaire_objectif', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('savoirfaire_id')->references('id')->on('fcmcentral_savoir_faires');
            $table->foreignUuid('objectif_id')->references('id')->on('fcmcentral_objectifs');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fcmcentral_fonction_parcours');
        Schema::dropIfExists('fcmcentral_competence_fonction');
        Schema::dropIfExists('fcmcentral_competence_savoirfaire');
        Schema::dropIfExists('fcmcentral_savoirfaire_stage');
        Schema::dropIfExists('fcmcentral_savoirfaire_objectif');

    }

};
