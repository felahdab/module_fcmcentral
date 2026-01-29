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
        // Ajout du champ uuid
        Schema::table('fcmcentral_savoirfaire_types', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
        });
        Schema::table('fcmcentral_parcours_types', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
        });
        Schema::table('fcmcentral_type_etats', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
        });
        Schema::table('fcmcentral_type_notes', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
        });
        Schema::table('fcmcentral_domaines', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fcmcentral_savoirfaire_types', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
        Schema::table('fcmcentral_parcours_types', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
        Schema::table('fcmcentral_type_etats', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
        Schema::table('fcmcentral_type_notes', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
        Schema::table('fcmcentral_domaines', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

    }
};
