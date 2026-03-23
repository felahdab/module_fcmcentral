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
        Schema::table('fcmcentral_objectifs', function (Blueprint $table) {
           
            $table->decimal('heures',6,2)->nullable()->after('coeff');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fcmcentral_objectifs', function (Blueprint $table) {
            
            $table->dropColumn('heures');
        });

    }
};
