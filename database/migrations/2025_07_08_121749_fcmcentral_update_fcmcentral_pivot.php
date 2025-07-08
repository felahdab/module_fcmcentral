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
        
        Schema::table('fcmcentral_competence_savoirfaire', function (Blueprint $table) {
            $table->decimal('coeff', 5, 2)->default(0)->after('ordre');
            $table->string('duree')->nullable()->after('coeff');
            $table->string('an_acquis')->nullable()->after('duree');
            $table->string('mod_acquis')->nullable()->after('an_acquis');
        });
 
        Schema::table('fcmcentral_competence_fonction', function (Blueprint $table) {
            $table->string('same')->nullable()->after('ordre');
        });
        

        Schema::table('fcmcentral_savoirfaires', function (Blueprint $table) {
            $table->dropColumn(['coeff', 'duree', 'an_acquis', 'mod_acquis']);
        });
 
        Schema::table('fcmcentral_competences', function (Blueprint $table) {
            $table->dropColumn('same');
        });
  
  
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fcmcentral_savoirfaires', function (Blueprint $table) {
            $table->decimal('coeff', 5, 2)->default(0);
            $table->string('duree')->nullable();
            $table->string('an_acquis')->nullable();
            $table->string('mod_acquis')->nullable();
        });
 
        Schema::table('fcmcentral_competences', function (Blueprint $table) {
            $table->string('same')->nullable();
        });
 
        Schema::table('fcmcentral_competence_savoirfaire', function (Blueprint $table) {
            $table->dropColumn(['coeff', 'duree', 'an_acquis', 'mod_acquis']);
        });
 
        Schema::table('fcmcentral_competence_fonction', function (Blueprint $table) {
            $table->dropColumn('same');
        });

    }
};
