<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fcmcentral_parcours_types', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('libelle_court');
            $table->string('libelle_long')->nullable();
            $table->integer('ordre')->default(0);
            $table->string('color')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fcmcentral_parcours_types');
    }
};
