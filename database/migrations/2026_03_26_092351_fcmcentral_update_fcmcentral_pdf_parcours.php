<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fcmcentral_parcours', function (Blueprint $table) {
            $table->string('livret_pdf')->nullable()->after('color');
        });
    }

    public function down(): void
    {
        Schema::table('fcmcentral_parcours', function (Blueprint $table) {
            $table->dropColumn('livret_pdf');
        });
    }
};
