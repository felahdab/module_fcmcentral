<?php

// filepath: Modules/FcmCentral/Database/Seeders/TestRolesUsersSeeder.php

namespace Modules\FcmCentral\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\FcmCentral\Models\FcmMarin;
use Modules\RH\Models\Brevet;
use Modules\RH\Models\Grade;
use Modules\RH\Models\Marin;
use Modules\RH\Models\Specialite;
use Modules\RH\Models\Unite;
use Spatie\Permission\Models\Role;

class TestMarinsRolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'fcmcentral::marin',
            'fcmcentral::mentor',
            'fcmcentral::chefdeservice',
            'fcmcentral::cdt',
            'fcmcentral::fps',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        $grade_id = Grade::inRandomOrder()->first()?->id ?? 1;
        $specialite_id = Specialite::inRandomOrder()->first()?->id ?? 1;
        $brevet_id = Brevet::inRandomOrder()->first()?->id ?? 1;
        $unite_id = Unite::inRandomOrder()->first()?->id ?? 1;

        $users = [
            [
                'nom' => 'Marin',
                'prenom' => 'Test',
                'email' => 'test.marin@example.com',
                'password' => bcrypt('password'),
                'role' => 'fcmcentral::marin',
                'display_name' => 'Marin Test',
                'marin' => [
                    'nid' => 'nid_test_marin',
                    'nom' => 'MARIN',
                    'prenom' => 'Test',
                    'display_name' => 'Marin Test',
                ],
            ],
            [
                'nom' => 'Marin 2',
                'prenom' => 'Test',
                'email' => 'test.marin2@example.com',
                'password' => bcrypt('password'),
                'role' => 'fcmcentral::marin',
                'display_name' => 'Marin2 Test',
                'marin' => [
                    'nid' => 'nid_test_marin2',
                    'nom' => 'MARIN2',
                    'prenom' => 'Test',
                    'display_name' => 'Marin2 Test',
                ],
            ],
            [
                'nom' => 'Mentor',
                'prenom' => 'Test',
                'email' => 'test.mentor@example.com',
                'password' => bcrypt('password'),
                'role' => 'fcmcentral::mentor',
                'display_name' => 'Mentor Test',
                'marin' => [
                    'nid' => 'nid_test_mentor',
                    'nom' => 'MENTOR',
                    'prenom' => 'Test',
                    'display_name' => 'Mentor Test',
                ],
            ],
            [
                'nom' => 'Chef de Service',
                'prenom' => 'Test',
                'email' => 'test.chefdeservice@example.com',
                'password' => bcrypt('password'),
                'role' => 'fcmcentral::chefdeservice',
                'display_name' => 'Cds Test',
                'marin' => [
                    'nid' => 'nid_test_chef',
                    'nom' => 'CHEFDESERVICE',
                    'prenom' => 'Test',
                    'display_name' => 'Cds Test',
                ],
            ],
            [
                'nom' => 'CDT',
                'prenom' => 'Test',
                'email' => 'test.cdt@example.com',
                'password' => bcrypt('password'),
                'role' => 'fcmcentral::cdt',
                'display_name' => 'Cdt Test',
                'marin' => [
                    'nid' => 'nid_test_cdt',
                    'nom' => 'CDT',
                    'prenom' => 'Test',
                    'display_name' => 'Cdt Test',
                ],
            ],
            [
                'nom' => 'FPS',
                'prenom' => 'Test',
                'email' => 'test.fps@example.com',
                'password' => bcrypt('password'),
                'role' => 'fcmcentral::fps',
                'display_name' => 'Fps Test',
                'marin' => [
                    'nid' => 'nid_test_fps',
                    'nom' => 'FPS',
                    'prenom' => 'Test',
                    'display_name' => 'Fps Test',
                ],
            ],
            [
                'nom' => 'BCF',
                'prenom' => 'Test',
                'email' => 'test.bcf@example.com',
                'password' => bcrypt('password'),
                'role' => 'fcmcentral::bcf',
                'display_name' => 'BCF Test',
                'marin' => [
                    'nid' => 'nid_test_bcf',
                    'nom' => 'BCF',
                    'prenom' => 'Test',
                    'display_name' => 'BCF Test',
                ],
            ],
        ];

        $marins = [];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'nom' => $data['nom'],
                    'prenom' => $data['prenom'],
                    'display_name' => $data['display_name'],
                    'password' => $data['password'],
                ]
            );
            $user->syncRoles([$data['role']]);

            // Preparer champ data role
            $marinData = ['status' => 'uuid_confirmed'];
            if ('fcmcentral::marin' === $data['role']) {
                $marinData['fcm'] = ['en_fcm' => true];
            }

            // Ajout du champ data avec status
            $marin = Marin::firstOrCreate(
                ['nid' => $data['marin']['nid']],
                [
                    'nom' => $data['marin']['nom'],
                    'prenom' => $data['marin']['prenom'],
                    'grade_id' => $grade_id,
                    'specialite_id' => $specialite_id,
                    'brevet_id' => $brevet_id,
                    'unite_id' => $unite_id,
                    'user_id' => $user->id,
                    'data' => json_encode($marinData),
                ]
            );
            // Si le marin existe déjà, on met à jour le champ data et user_id
            $marin->data = json_encode($marinData);
            $marin->user_id = $user->id;
            $marin->save();

            $marins[$data['role']][] = $marin;
        }

        // Raccord mentor/marins dans fcmcentral_fcm_marins
        $mentor = $marins['fcmcentral::mentor'][0] ?? null;
        $marin1 = $marins['fcmcentral::marin'][0] ?? null;
        $marin2 = $marins['fcmcentral::marin'][1] ?? null;

        if ($mentor && $marin1) {
            FcmMarin::firstOrCreate([
                'mentor_id' => $mentor->id,
                'rh_marin_id' => $marin1->id,
            ]);
        }
        if ($mentor && $marin2) {
            FcmMarin::firstOrCreate([
                'mentor_id' => $mentor->id,
                'rh_marin_id' => $marin2->id,
            ]);
        }
    }
}
