<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\PostgresConnection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (DB::connection() instanceof PostgresConnection) {
            $default_value = DB::raw('(gen_random_uuid())');
        } elseif (DB::connection() instanceof MySqlConnection) {
            $default_value = DB::raw('(UUID())');
        }

        // Objectifs liés à Parcours-Annee-SavoirFaire
        Schema::create('fcmcentral_objectifs', function (Blueprint $table) use ($default_value) {
            $table->id();
            $table->uuid('uuid')->default($default_value);
            $table->string('code');
            $table->unsignedBigInteger('competence_savoirfaire_id');
            $table->string('libelle_court')->nullable();
            $table->string('libelle_long')->nullable();
            // $table->string('duree')->nullable();
            $table->decimal('coeff', 5, 2)->nullable();
            $table->integer('ordre')->default(0);
            $table->timestamps();

            $table->foreign('competence_savoirfaire_id')->references('id')->on('fcmcentral_competence_savoirfaires')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fcmcentral_objectifs');
    }
};
