<?php

namespace Modules\FcmCentral\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\FcmCentral\Models\TypeEtat;

class TypeEtatSeeder extends Seeder
{
    public function run(): void
    {
        TypeEtat::updateOrCreate(['code' => 'FCM_ACT'], [
            'libelle_court' => 'En FCM',
        ]);
        TypeEtat::updateOrCreate(['code' => 'FCM_SED'], [
            'libelle_court' => 'En Sedimentation',
        ]);
        TypeEtat::updateOrCreate(['code' => 'FCM_PAU'], [
            'libelle_court' => 'En Pause',
        ]);
        TypeEtat::updateOrCreate(['code' => 'FCM_FIN'], [
            'libelle_court' => 'Fin FCM',
        ]);
    }
}
