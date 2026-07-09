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


        // 2. Création des enregistrements correspondant au champs mentor_id dans la table pivot
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
            logger()->error("Migration mentor : les données n'ont pas été transférées correctement.");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('fcmcentral_fcm_marin_mentors');
    }
};


