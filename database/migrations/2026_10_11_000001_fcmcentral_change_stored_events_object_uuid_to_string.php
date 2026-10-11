<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('fcmcentral_stored_events', function (Blueprint $table) {
            $table->string('object_uuid')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('fcmcentral_stored_events', function (Blueprint $table) {
            $table->uuid('object_uuid')->nullable()->change();
        });
    }
};
