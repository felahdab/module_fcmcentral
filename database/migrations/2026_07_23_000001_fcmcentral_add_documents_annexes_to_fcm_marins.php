<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fcmcentral_fcm_marins', function (Blueprint $table) {
            $table->json('documents_annexes')->nullable()->after('data');
        });
    }

    public function down(): void
    {
        Schema::table('fcmcentral_fcm_marins', function (Blueprint $table) {
            $table->dropColumn('documents_annexes');
        });
    }
};
