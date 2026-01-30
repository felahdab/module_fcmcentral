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
        if (DB::connection() instanceof \Illuminate\Database\PostgresConnection) {
            $default_value = DB::raw('(gen_random_uuid())');
        }
        elseif (DB::connection() instanceof \Illuminate\Database\MySqlConnection){
            $default_value = DB::raw('(UUID())');
        }
        // Ajout du champ uuid
        Schema::table('fcmcentral_savoirfaire_types', function (Blueprint $table) use ($default_value) {
            $table->uuid('uuid')->default($default_value)->after('id');
        });
        Schema::table('fcmcentral_parcours_types', function (Blueprint $table)  use ($default_value) {
            $table->uuid('uuid')->default($default_value)->after('id');
        });
        Schema::table('fcmcentral_type_etats', function (Blueprint $table)  use ($default_value) {
            $table->uuid('uuid')->default($default_value)->after('id');
        });
        Schema::table('fcmcentral_type_notes', function (Blueprint $table)  use ($default_value) {
            $table->uuid('uuid')->default($default_value)->after('id');
        });
        Schema::table('fcmcentral_domaines', function (Blueprint $table)  use ($default_value) {
            $table->uuid('uuid')->default($default_value)->after('id');
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
