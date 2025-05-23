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
        Schema::table('fcmcentral_fonction_parcours', function (Blueprint $table) {
            $table->integer('ordre')->default(0)->after('fonction_id');
        });

        Schema::table('fcmcentral_competence_fonction', function (Blueprint $table) {
            $table->integer('ordre')->default(0)->after('competence_id');
        });

        Schema::table('fcmcentral_competence_savoirfaire', function (Blueprint $table) {
            $table->integer('ordre')->default(0)->after('savoirfaire_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
          // Supp relation et champ 
          Schema::table('fcmcentral_fonction_parcours', function (Blueprint $table){
            $table->dropColumn('ordre');
        });

        Schema::table('fcmcentral_competence_fonction', function (Blueprint $table){
            $table->dropColumn('ordre');
        });

        Schema::table('fcmcentral_competence_savoirfaire', function (Blueprint $table){
            $table->dropColumn('ordre');
        });
    }
};
