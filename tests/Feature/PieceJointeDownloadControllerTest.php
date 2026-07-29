<?php

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\MarinParcours;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Modules\RH\Models\Unite;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

pest()->group('Fcm Central1');

const DOWNLOAD_SF_UUID = 'deasm1b1-1234-5678-cosf-11aa1c1c1sf1';
const DOWNLOAD_OBJECTIFS = [
    'deasm1b1-1234-5678-obje-11aa1c1sf1o1',
    'deasm1b1-1234-5678-obje-11aa1c1sf1o2',
    'deasm1b1-1234-5678-obje-11aa1c1sf1o3',
];

beforeEach(function () {
    Storage::fake('s3-private');
    $this->admin = User::factory()->create(['admin' => 1]);
});

/**
 * Crée un marin en FCM avec un savoir-faire validé (DOWNLOAD_SF_UUID) et une pièce jointe
 * réellement présente sur le disque s3-private (sans passer par les composants Livewire).
 */
function creerMarinAvecPieceJointeStockee(): array
{
    // unite_id renseigné : sans ça, MarinFiltreParUniteScope (module RH, non modifié) génère
    // une sous-requête avec une colonne "id" ambiguë dans est_mentor_de() — bug préexistant
    // sans rapport avec ce contrôleur, contourné ici comme en usage réel.
    $marin = Marin::factory()->create(['unite_id' => Unite::factory()->create()->id]);

    event(new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::MARIN_EN_FCM,
        user_uuid: 'test-user',
        object_class: ObjectClasses::MARIN,
        object_uuid: $marin->uuid,
        detail: ['cohorte_id' => 1, 'date_de_debut' => Carbon::tomorrow()]
    ));

    Artisan::call('fcmcentral:charge-parcours Modules/FcmCentral/tests/Feature/parcours_deasm.json');
    $parcours = Parcours::first();

    event(new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::CREATION_PARCOURS_SERIALISE,
        user_uuid: 'test-user',
        object_class: ObjectClasses::PARCOURS,
        object_uuid: $parcours->uuid,
        detail: ['version' => 1, 'date_de_debut' => Carbon::tomorrow()]
    ));

    event(new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN,
        user_uuid: 'test-user',
        object_class: ObjectClasses::MARIN,
        object_uuid: $marin->uuid,
        detail: ['parcours' => $parcours->uuid]
    ));

    foreach (DOWNLOAD_OBJECTIFS as $objectif) {
        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::VALID_ELMT_MARIN_PARCOURS,
            user_uuid: 'test-user',
            object_class: ObjectClasses::MARIN,
            object_uuid: $marin->uuid,
            detail: [
                'type_element' => 'objectif',
                'uuid_element' => $objectif,
                'date' => Carbon::now(),
                'commentaire' => 'validation objectif',
            ]
        ));
    }

    $marin->refresh();

    // Injection directe de la pièce jointe dans le JSON du parcours (contourne les composants
    // Livewire, dont le montage souffre d'un bug préexistant sans rapport avec ce contrôleur).
    $chemin = sprintf('fcmcentral/livretfcm/pieces-jointes/savoirfaire/%s/2026/07/justificatif.pdf', $marin->uuid);
    Storage::disk('s3-private')->put($chemin, 'contenu-pdf-de-test');

    $donneesDuParcours = $marin->complements_fcm->donnees_du_parcours;
    $parcoursMarin = $donneesDuParcours->parcoursmarin;

    foreach ($parcoursMarin['affectations'] as &$affectation) {
        foreach ($affectation['competences'] as &$competence) {
            foreach ($competence['competenceSavoirfaires'] as &$csf) {
                if (($csf['uuid'] ?? null) === DOWNLOAD_SF_UUID) {
                    $csf['piece_jointe'] = $chemin;
                }
            }
        }
    }
    unset($affectation, $competence, $csf);

    $donneesDuParcours->parcoursmarin = $parcoursMarin;
    $donneesDuParcours->save();

    $marin->refresh();

    return [$marin, $chemin];
}

function urlPieceJointe(Marin $marin, string $type = 'savoirfaire', string $uuid = DOWNLOAD_SF_UUID): string
{
    // Chemin construit manuellement SANS le préfixe "apps" : le helper de test $this->get()
    // préfixe déjà l'URI avec config('app.url'), qui contient lui-même "/apps" — passer par
    // route() ici doublerait ce préfixe ("/apps/apps/...") et produirait un faux 404.
    return sprintf(
        '/fcmcentral/piece-jointe/%s/%s/%s',
        $marin->uuid,
        $type,
        $uuid
    );
}

describe('PieceJointeDownloadController - Autorisations', function () {
    it('refuse l\'accès à un utilisateur non connecté', function () {
        [$marin] = creerMarinAvecPieceJointeStockee();

        $this->get(urlPieceJointe($marin))
            ->assertRedirect() // middleware auth : redirection vers la page de login
        ;
    });

    it('refuse l\'accès à un utilisateur connecté qui n\'est ni le marin, ni son mentor, ni autorisé par permission', function () {
        [$marin] = creerMarinAvecPieceJointeStockee();

        $intrus = User::factory()->create(['admin' => 0]);

        $this->actingAs($intrus)
            ->get(urlPieceJointe($marin))
            ->assertForbidden()
        ;
    });

    it('refuse l\'accès à un autre marin (non mentor) qui tente de voir le PDF d\'un marin différent', function () {
        [$marinCible] = creerMarinAvecPieceJointeStockee();

        $autreMarin = Marin::factory()->create(['unite_id' => Unite::factory()->create()->id]);
        $userAutreMarin = $autreMarin->createUser();

        $this->actingAs($userAutreMarin)
            ->get(urlPieceJointe($marinCible))
            ->assertForbidden()
        ;
    });

    it('autorise le marin concerné à voir sa propre pièce jointe', function () {
        [$marin, $chemin] = creerMarinAvecPieceJointeStockee();

        $userDuMarin = $marin->createUser();

        $this->actingAs($userDuMarin)
            ->get(urlPieceJointe($marin))
            ->assertSuccessful()
        ;
    });

    it('autorise un mentor du marin à voir sa pièce jointe', function () {
        [$marin] = creerMarinAvecPieceJointeStockee();

        $mentor = Marin::factory()->create(['unite_id' => Unite::factory()->create()->id]);
        $userMentor = $mentor->createUser();

        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::ATTRIBUER_MENTOR_MARIN,
            user_uuid: 'test-user',
            object_class: ObjectClasses::MARIN,
            object_uuid: $marin->uuid,
            detail: ['mentors' => [$mentor->uuid]]
        ));

        $this->actingAs($userMentor)
            ->get(urlPieceJointe($marin))
            ->assertSuccessful()
        ;
    });

    it('autorise un utilisateur ayant la permission fcmcentral::piece_jointe.voir, même sans lien avec le marin', function () {
        [$marin] = creerMarinAvecPieceJointeStockee();

        $gestionnaire = User::factory()->create(['admin' => 0]);
        $permission = \App\Models\Permission::firstOrCreate([
            'name' => 'fcmcentral::piece_jointe.voir',
            'guard_name' => 'web',
        ]);
        $gestionnaire->givePermissionTo($permission);

        $this->actingAs($gestionnaire)
            ->get(urlPieceJointe($marin))
            ->assertSuccessful()
        ;
    });

    it('renvoie 404 si la pièce jointe n\'existe pas dans le parcours du marin', function () {
        [$marin] = creerMarinAvecPieceJointeStockee();

        $userDuMarin = $marin->createUser();

        $this->actingAs($userDuMarin)
            ->get(urlPieceJointe($marin, 'savoirfaire', 'uuid-inexistant'))
            ->assertNotFound()
        ;
    });
});
