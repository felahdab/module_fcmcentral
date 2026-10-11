<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('fcmcentral_stored_events', function (Blueprint $table) {
            $table->string('object_uuid')->nullable()->change();
        });

        DB::table('rh_marins')
            ->whereNotNull('nid')
            ->where('nid', '<>', '')
            ->orderBy('id')
            ->chunkById(500, function ($marins): void {
                foreach ($marins as $marin) {
                    DB::table('fcmcentral_stored_events')
                        ->where('object_class', 'Marin')
                        ->where('object_uuid', $marin->uuid)
                        ->update(['object_uuid' => $marin->nid]);
                }
            });
    }

    public function down(): void
    {
        DB::table('rh_marins')
            ->whereNotNull('nid')
            ->where('nid', '<>', '')
            ->orderBy('id')
            ->chunkById(500, function ($marins): void {
                foreach ($marins as $marin) {
                    DB::table('fcmcentral_stored_events')
                        ->where('object_class', 'Marin')
                        ->where('object_uuid', $marin->nid)
                        ->update(['object_uuid' => $marin->uuid]);
                }
            });

        Schema::table('fcmcentral_stored_events', function (Blueprint $table) {
            $table->uuid('object_uuid')->nullable()->change();
        });
    }
};
