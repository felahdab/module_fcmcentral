<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $tables = [
            'fcmcentral_competences',
            'fcmcentral_savoirfaires',
            'fcmcentral_parcours',
            'fcmcentral_affectations',
            'fcmcentral_objectifs',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->string('libelle_court', 500)->nullable()->change();
                $t->text('libelle_long')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'fcmcentral_competences',
            'fcmcentral_savoirfaires',
            'fcmcentral_parcours',
            'fcmcentral_affectations',
            'fcmcentral_objectifs',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->string('libelle_court', 255)->nullable()->change();
                $t->string('libelle_long', 255)->nullable()->change();
            });
        }
    }
};
