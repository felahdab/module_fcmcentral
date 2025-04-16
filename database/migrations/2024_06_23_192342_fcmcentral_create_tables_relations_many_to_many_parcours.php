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
        Schema::create('fcmcentral_fonction_parcours', function (Blueprint $table) {
            $table->id();
            //$table->foreignUuid('parcours_id')->references('id')->on('fcmcentral_parcours');
			//$table->foreignUuid('fonction_id')->references('id')->on('fcmcentral_fonctions');
            $table->foreignId('parcours_id')->constrained('fcmcentral_parcours');
			$table->foreignId('fonction_id')->constrained('fcmcentral_fonctions');
            $table->timestamps();
        });

        Schema::create('fcmcentral_competence_fonction', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fonction_id')->constrained('fcmcentral_fonctions');
            $table->foreignId('competence_id')->constrained('fcmcentral_competences');

            $table->timestamps();
        });

        Schema::create('fcmcentral_competence_savoirfaire', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competence_id')->constrained('fcmcentral_competences');
            $table->foreignId('savoirfaire_id')->constrained('fcmcentral_savoirfaires');

            $table->timestamps();
        });

        Schema::create('fcmcentral_activite_savoirfaire', function (Blueprint $table) {
            $table->id();
            $table->foreignId('savoirfaire_id')->constrained('fcmcentral_savoirfaires');
            $table->foreignId('activite_id')->constrained('fcmcentral_activites');
            $table->json('data')->nullable();
            $table->string('duree')->nullable();
            $table->decimal('coeff',5,2)->nullable();
            $table->integer('ordre')->default(0);

            $table->timestamps();
        });

        // Liaison avec table FcmCentralActivites et FcmCentralTypeAvtivites
        // Schema::table('fcmcentral_activites', function (Blueprint $table){
        //     $table->unsignedBigInteger('typeactivite_id')->nullable()->after('duree_validite');
        //     $table->foreign('typeactivite_id')->references('id')->on('fcmcentral_typeactivites')->onDelete('cascade');
        // });

        // Liaison avec table FcmCentralDomaines et FcmCentralSavoirfaires
        Schema::table('fcmcentral_savoirfaires', function (Blueprint $table){
            $table->unsignedBigInteger('domaine_id')->nullable()->after('code_sicomp');
            $table->foreign('domaine_id')->references('id')->on('fcmcentral_domaines')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fcmcentral_fonction_parcours');
        Schema::dropIfExists('fcmcentral_competence_fonctions');
        Schema::dropIfExists('fcmcentral_competence_savoirfaire');
        Schema::dropIfExists('fcmcentral_activite_savoirfaire');

         // Supp relation et champ 
        // Schema::table('fcmcentral_activites', function (Blueprint $table){
        //     $table->dropForeign(['typeactivite_id']);
        //     $table->dropColumn('typeactivite_id');
        // });

         // Supp relation et champ 
        Schema::table('fcmcentral_savoirfaires', function (Blueprint $table){
            $table->dropForeign(['domaine_id']);
            $table->dropColumn('domaine_id');
        });

    }

};
