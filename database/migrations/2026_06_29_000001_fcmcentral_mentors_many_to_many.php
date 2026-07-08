<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Créer la table pivot
        Schema::create('fcmcentral_fcm_marin_mentors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fcm_marin_id');
            $table->unsignedBigInteger('marin_id');
            $table->timestamps();

            $table->unique(['fcm_marin_id', 'marin_id']);
            $table->foreign('fcm_marin_id')->references('id')->on('fcmcentral_fcm_marins')->onDelete('cascade');
            $table->foreign('marin_id')->references('id')->on('rh_marins')->onDelete('cascade');
        });

        // 2. Migrer les mentor_id existants vers la table pivot (ignoré en test, base toujours fraîche)
        if (!app()->runningUnitTests() && Schema::hasColumn('fcmcentral_fcm_marins', 'mentor_id')) {
            $nbAvant = DB::table('fcmcentral_fcm_marins')->whereNotNull('mentor_id')->count();

            DB::table('fcmcentral_fcm_marins')
                ->whereNotNull('mentor_id')
                ->select('id', 'mentor_id')
                ->orderBy('id')
                ->each(function ($row) {
                    DB::table('fcmcentral_fcm_marin_mentors')->insertOrIgnore([
                        'fcm_marin_id' => $row->id,
                        'marin_id'     => $row->mentor_id,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                });

            $nbApres = DB::table('fcmcentral_fcm_marin_mentors')->count();

            echo "Migration mentors : {$nbAvant} mentor_id trouvés → {$nbApres} lignes dans la pivot.\n";

            if ($nbAvant > 0 && $nbApres === 0) {
                throw new \RuntimeException("Migration mentor : les données n'ont pas été transférées dans la pivot. Abandon.");
            }

            // 3. Supprimer mentor_id après transfert vérifié
            Schema::table('fcmcentral_fcm_marins', function (Blueprint $table) {
                try {
                    $table->dropForeign(['mentor_id']);
                } catch (\Throwable $e) {
                    // Pas de FK sur cette colonne
                }
                $table->dropColumn('mentor_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('fcmcentral_fcm_marins', function (Blueprint $table) {
            $table->unsignedBigInteger('mentor_id')->nullable();
        });

        // Restaure le premier mentor par fcm_marin (one-to-many → one-to-one)
        DB::table('fcmcentral_fcm_marin_mentors')
            ->select('fcm_marin_id', DB::raw('MIN(marin_id) as marin_id'))
            ->groupBy('fcm_marin_id')
            ->orderBy('fcm_marin_id')
            ->each(function ($row) {
                DB::table('fcmcentral_fcm_marins')
                    ->where('id', $row->fcm_marin_id)
                    ->update(['mentor_id' => $row->marin_id]);
            });

        Schema::dropIfExists('fcmcentral_fcm_marin_mentors');
    }
};


