<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fcmcentral_qlicksense_exports', function (Blueprint $table) {
            // Renommage progression_globale → taux_global
            $table->renameColumn('progression_globale', 'taux_global');

            // Ajout des deux nouvelles colonnes taux
            $table->decimal('taux_stage', 5, 2)->nullable()->after('taux_global');
            $table->decimal('taux_activite', 5, 2)->nullable()->after('taux_stage');

            // Suppression de parcoursmarin (non exploitable tel quel dans QlikSense)
            $table->dropColumn('parcoursmarin');
        });
    }

    public function down(): void
    {
        Schema::table('fcmcentral_qlicksense_exports', function (Blueprint $table) {
            $table->renameColumn('taux_global', 'progression_globale');
            $table->dropColumn(['taux_stage', 'taux_activite']);
            $table->longText('parcoursmarin')->nullable();
        });
    }
};


