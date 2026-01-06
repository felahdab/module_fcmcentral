<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{

    /**
     * Run the migrations.
     */
    public function up(): void
    {

        if (DB::connection() instanceof \Illuminate\Database\PostgresConnection) {
            $default_value = DB::raw('(gen_random_uuid())');
        }
        elseif (DB::connection() instanceof \Illuminate\Database\MySqlConnection){
            $default_value = DB::raw('(UUID())');
        }

        Schema::create('fcmcentral_competences', function (Blueprint $table) use ($default_value) {
            $table->id();
            $table->uuid('uuid')->default($default_value);
            $table->string('code');
            $table->string('libelle_court');
            $table->string('libelle_long')->nullable();
            $table->unsignedBigInteger('parcours_type_id')->nullable();
            $table->foreign('parcours_type_id')->references('id')->on('fcmcentral_parcours_types')->onDelete('cascade');
            $table->integer('ordre')->default(0);
            $table->string('color')->nullable();
            $table->timestamps();
        });

        // Savoir-faire (lié à une compétence)
        Schema::create('fcmcentral_savoirfaires', function (Blueprint $table) use ($default_value) {
            $table->id();
            $table->uuid('uuid')->default($default_value);
            $table->string('code');
            $table->string('libelle_court');
            $table->string('libelle_long')->nullable();
            $table->string('same')->nullable();
            $table->string('code_sicomp')->nullable();
            // $table->string('niveau');
            $table->integer('ordre')->default(0);
            $table->string('color')->nullable();
            $table->unsignedBigInteger('competence_id')->nullable();
            $table->foreign('competence_id')->references('id')->on('fcmcentral_competences')->onDelete('cascade');
            $table->unsignedBigInteger('domaine_id')->nullable();
            $table->foreign('domaine_id')->references('id')->on('fcmcentral_domaines')->onDelete('cascade');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fcmcentral_savoirfaires');
        Schema::dropIfExists('fcmcentral_competences');

    }
};
