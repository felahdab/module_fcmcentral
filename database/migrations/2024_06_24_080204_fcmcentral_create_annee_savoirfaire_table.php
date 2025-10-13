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

        // Savoir-faire affectés à une année (avec options personnalisées)
        Schema::create('fcmcentral_annee_savoirfaires', function (Blueprint $table) use ($default_value)  {
            $table->id();
            $table->uuid('uuid')->default($default_value);
            $table->unsignedBigInteger('annee_id');
            $table->unsignedBigInteger('savoirfaire_id');
            $table->unsignedBigInteger('savoirfaire_type_id')->nullable();
            
            $table->decimal('heures',6,2)->nullable();
            $table->decimal('jours',6,2)->nullable();
            $table->string('libelle_court')->nullable();
            $table->integer('ordre')->default(0);
            $table->json('data')->nullable();
            $table->timestamps();

            $table->foreign('annee_id')->references('id')->on('fcmcentral_annees')->onDelete('cascade');
            $table->foreign('savoirfaire_id')->references('id')->on('fcmcentral_savoirfaires')->onDelete('cascade');
            $table->foreign('savoirfaire_type_id')->references('id')->on('fcmcentral_savoirfaire_types')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fcmcentral_annee_savoirfaire');
    }
};

