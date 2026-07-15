<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fcmcentral_fcm_marins', function (Blueprint $table) {
            try {
                $table->dropForeign(['mentor_id']);
            } catch (\Throwable $e) {
                
            }
            $table->dropColumn('mentor_id');
        });
    }

    public function down(): void
    {
        Schema::table('fcmcentral_fcm_marins', function (Blueprint $table) {
            $table->foreignId('mentor_id')->after('rh_marin_id')->nullable()->constrained('rh_marins')->onDelete('cascade');
        });

        DB::table('fcmcentral_fcm_marin_mentors')
        ->select('fcm_marin_id', DB::raw('MIN(marin_id) as marin_id'))
        ->groupBy('fcm_marin_id')
        ->orderBy('fcm_marin_id')
        ->each(function ($row) {
            DB::table('fcmcentral_fcm_marins')
                ->where('id', $row->fcm_marin_id)
                ->update(['mentor_id' => $row->marin_id]);
        });
    }
};


