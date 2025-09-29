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

        Schema::create('fcmcentral_competences', function (Blueprint $table) use ($default_value) {
            // $table->uuid('id')->primary();
            $table->id();
            $table->uuid('uuid')->default($default_value);
            $table->string('code');
            $table->string('libelle_court');
            $table->string('libelle_long');
            $table->string('url')->nullable();
            $table->tinyInteger('ordre')->default(0);
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
        Schema::dropIfExists('fcmcentral_competences');
    }
};
