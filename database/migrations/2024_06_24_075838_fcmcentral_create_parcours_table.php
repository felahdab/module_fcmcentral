<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        
        if (DB::connection() instanceof \Illuminate\Database\PostgresConnection) {
            $default_value = DB::raw('(gen_random_uuid())');
        }
        elseif (DB::connection() instanceof \Illuminate\Database\MySqlConnection){
            $default_value = DB::raw('(UUID())');
        }
        
        // Parcours
        Schema::create('fcmcentral_parcours', function (Blueprint $table) use ($default_value) {
            $table->id();
            $table->uuid('uuid')->default($default_value);
            $table->string('code');
            $table->string('libelle_court');
            $table->string('libelle_long')->nullable();
            $table->unsignedBigInteger('parcours_type_id');
            $table->foreign('parcours_type_id')->references('id')->on('fcmcentral_parcours_types')->onDelete('cascade');
            $table->integer('ordre')->default(0);
            $table->string('color')->nullable();
            $table->timestamps();
        });

        // Années du parcours
        Schema::create('fcmcentral_annees', function (Blueprint $table) use ($default_value) {
            $table->id();
            $table->uuid('uuid')->default($default_value);
            $table->string('code');
            $table->string('libelle_court');
            $table->string('libelle_long')->nullable();
            $table->integer('nombre')->default(0);
            $table->integer('ordre')->default(0);
            $table->string('color')->nullable();
            $table->timestamps();
        });

        Schema::create('fcmcentral_annee_parcours', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parcours_id');
            $table->unsignedBigInteger('annee_id');
            $table->integer('ordre')->default(0);
            $table->foreign('parcours_id')->references('id')->on('fcmcentral_parcours')->onDelete('cascade');
			$table->foreign('annee_id')->references('id')->on('fcmcentral_annees')->onDelete('cascade');
            $table->unique(['parcours_id','annee_id']);
            $table->timestamps();
            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fcmcentral_annees');
        Schema::dropIfExists('fcmcentral_parcours');
    }
};

