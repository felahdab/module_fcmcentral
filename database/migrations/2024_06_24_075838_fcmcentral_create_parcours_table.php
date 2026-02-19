<?php

use Illuminate\Database\PostgresConnection;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        
        if (DB::connection() instanceof PostgresConnection) {
            $default_value = DB::raw('(gen_random_uuid())');
        }
        elseif (DB::connection() instanceof MySqlConnection){
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
        Schema::create('fcmcentral_affectations', function (Blueprint $table) use ($default_value) {
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

        Schema::create('fcmcentral_affectation_parcours', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parcours_id');
            $table->unsignedBigInteger('affectation_id');
            $table->integer('ordre')->default(0);
            $table->foreign('parcours_id')->references('id')->on('fcmcentral_parcours')->onDelete('cascade');
			$table->foreign('affectation_id')->references('id')->on('fcmcentral_affectations')->onDelete('cascade');
            $table->unique(['parcours_id','affectation_id'],'fcmcentral_aff_par_unique');
            $table->timestamps();
            
        });

        Schema::table('fcmcentral_competences', function (Blueprint $table) {
            $table->foreignId('affectation_id')
            ->nullable()
            ->constrained('fcmcentral_affectations')
             ->nullOnDelete()
             ->after('parcours_type_id');
         });
    }

    public function down(): void
    {
        Schema::dropIfExists('fcmcentral_affectations');
        Schema::dropIfExists('fcmcentral_parcours');
        Schema::table('fcmcentral_competences', function (Blueprint $table){
            $table->dropForeign(['annee_id']);
            $table->dropColumn('annee_id');
        });
    }
};

