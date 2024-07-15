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
        Schema::create('fcmcentral_user_parcours', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->references('uuid')->on('users');
            // La relation ci-dessous emporte avec elle l'uuid du parcours et la version de celui-ci.
            $table->foreignUuid('parcours_id')->references('id')->on('fcmcentral_parcours_serialises');

            $table->json('parcours');
            
            $table->timestamps();

            $table->unique(['user_id', 'parcours_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fcmcentral_user_parcours');
    }
};
