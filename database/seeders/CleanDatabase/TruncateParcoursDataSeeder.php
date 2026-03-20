<?php

namespace Modules\FcmCentral\Database\Seeders\CleanDatabase;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\FcmCentral\Models\Objectif;
use Modules\FcmCentral\Models\CompetenceSavoirfaire;
use Modules\FcmCentral\Models\AffectationParcours;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Models\Savoirfaire;
use Modules\FcmCentral\Models\Competence;
use Modules\FcmCentral\Models\Affectation;
use Modules\FcmCentral\Models\ParcoursType;
use Modules\FcmCentral\Models\SavoirfaireType;

use Modules\FcmCentral\Models\Domaine;
use Modules\FcmCentral\Models\TypeEtat;

class TruncateParcoursDataSeeder extends Seeder
{
    /**
     * Vide toutes les tables de définition des parcours de FCM, sans distinction.
     *
     *   php artisan module:seed FcmCentral --class=CleanDatabase\\TruncateParcoursDataSeeder
     */
    public function run(): void
    {
        $this->command->warn('⚠️  Suppression des données du parcours Deasm...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Objectif::query()->delete();
        CompetenceSavoirfaire::query()->delete();
        AffectationParcours::query()->delete();
        Parcours::query()->delete();
        Savoirfaire::query()->delete();
        Competence::query()->delete();
        Affectation::query()->delete();
        ParcoursType::query()->delete();
        SavoirfaireType::query()->delete();

        TypeEtat::query()->delete();
        Domaine::query()->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('🗑️  Tables vidées avec succès.');
    }
}
