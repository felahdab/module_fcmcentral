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
        Schema::create('fcmcentral_qlicksense_exports', function (Blueprint $table) {
            $table->id();
            $table->uuid('marin_uuid')->index();
            $table->string('grade', 50)->nullable();
            $table->string('nom', 255)->nullable();
            $table->string('prenom', 255)->nullable();
            $table->string('specialite', 500)->nullable();
            $table->string('affectation', 500)->nullable();
            $table->string('cohorte', 255)->nullable();
            $table->string('parcours', 255)->nullable();
            $table->string('gel_fcm', 50)->nullable();
            $table->string('vert_ppa_depuis_le', 50)->nullable();
            $table->integer('temps_dans_le_ppa')->nullable();
            $table->decimal('progression_globale', 5, 2)->nullable();
            $table->integer('stage_manquants')->nullable();
            $table->uuid('mentor_uuid')->nullable();
            $table->string('mentor_grade', 50)->nullable();
            $table->string('mentor_nom', 255)->nullable();
            $table->string('mentor_prenom', 255)->nullable();
            $table->string('mentor_unite', 500)->nullable();
            $table->string('mentor_email', 255)->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fcmcentral_qlicksense_exports');
    }
};

