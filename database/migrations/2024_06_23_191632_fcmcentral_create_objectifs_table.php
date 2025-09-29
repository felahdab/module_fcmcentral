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

        $default_value = "";

        if (DB::connection() instanceof \Illuminate\Database\PostgresConnection) {
            $default_value = DB::raw('(gen_random_uuid())');
        }
        elseif (DB::connection() instanceof \Illuminate\Database\MySqlConnection){
            $default_value = DB::raw('(UUID())');
        }

        Schema::create('fcmcentral_objectifs', function (Blueprint $table) use ($default_value) {
            // $table->uuid('id')->primary();
            $table->id();
            $table->uuid('uuid')->default($default_value);
            $table->string('code');
            $table->string('libelle_long');
            $table->string('libelle_court');
            $table->string('url')->nullable();
            $table->string('duree')->nullable();
            $table->decimal('coeff',5,2)->nullable();
            $table->string('prerequis')->nullable();
            $table->string('duree_validite')->nullable();
            //$table->enum('type_activite', ['STAGE', 'TACHE']);
            $table->string('type_activite');
            $table->tinyInteger('ordre')->default(0);
            $table->unsignedByInteger('savoirfaire_id')->nullable();
            $table->foreign('savoirfaire_id')->references('id')->on('fcmcentral_savoirfaires')->onDelete('set null');
            $table->unsignedByInteger('parcours_id')->nullable();
            $table->foreign('parcours_id')->references('id')->on('fcmcentral_parcours')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fcmcentral_objectifs');
    }
};
