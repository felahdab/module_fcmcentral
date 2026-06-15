<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fcmcentral_qlicksense_exports', function (Blueprint $table) {
            $table->longText('parcoursmarin')->nullable()->after('parcours');
        });
    }

    public function down(): void
    {
        Schema::table('fcmcentral_qlicksense_exports', function (Blueprint $table) {
            $table->dropColumn('parcoursmarin');
        });
    }

};
