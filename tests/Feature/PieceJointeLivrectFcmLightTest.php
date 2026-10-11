<?php

use App\Models\User;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Models\FcmMarin;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Modules\FcmCommun\Http\Livewire\LivretFcmLight;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

pest()->group('Fcm Central');

const LIVRET_SF_UUID = 'deasm1b1-1234-5678-cosf-11aa1c1c1sf1';
const LIVRET_OBJECTIFS = [
    'deasm1b1-1234-5678-obje-11aa1c1sf1o1',
    'deasm1b1-1234-5678-obje-11aa1c1sf1o2',
    'deasm1b1-1234-5678-obje-11aa1c1sf1o3',
];

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('FCM Central'));
    $this->admin = User::factory()->create(['admin' => 1]);
    Storage::fake('s3-private');
});

/**
 * Crée un marin en FCM avec un savoir-faire (LIVRET_SF_UUID) validé
 * (les 3 objectifs qui le composent sont validés, ce qui valide le csf lui-même).
 */
function creerMarinAvecSavoirFaireValideLivret(): Marin
{
    $fcmMarin = FcmMarin::factory()->create();
    $marin = $fcmMarin->marin;

    event(new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::MARIN_EN_FCM,
        user_uuid: 'test-user',
        object_class: ObjectClasses::MARIN,
        object_uuid: $marin->nid,
        detail: [
            'cohorte_id' => 1,
            'date_de_debut' => Carbon::tomorrow(),
        ]
    ));

    Artisan::call('fcmcentral:charge-parcours Modules/FcmCentral/tests/Feature/parcours_deasm.json');
    $parcours = Parcours::first();

    event(new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::CREATION_PARCOURS_SERIALISE,
        user_uuid: 'test-user',
        object_class: ObjectClasses::PARCOURS,
        object_uuid: $parcours->uuid,
        detail: [
            'version' => 1,
            'date_de_debut' => Carbon::tomorrow(),
        ]
    ));

    event(new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN,
        user_uuid: 'test-user',
        object_class: ObjectClasses::MARIN,
        object_uuid: $marin->nid,
        detail: ['parcours' => $parcours->uuid]
    ));

    foreach (LIVRET_OBJECTIFS as $objectif) {
        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::VALID_ELMT_MARIN_PARCOURS,
            user_uuid: 'test-user',
            object_class: ObjectClasses::MARIN,
            object_uuid: $marin->nid,
            detail: [
                'type_element' => 'objectif',
                'uuid_element' => $objectif,
                'date' => Carbon::now(),
                'commentaire' => 'validation objectif',
            ]
        ));
    }

    $marin->refresh();

    return $marin;
}

function trouverCsfLivret(Marin $marin, string $uuid): ?array
{
    $parcoursMarin = $marin->complements_fcm->donnees_du_parcours->parcoursmarin ?? [];

    foreach ($parcoursMarin['affectations'] ?? [] as $affectation) {
        foreach ($affectation['competences'] ?? [] as $competence) {
            foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                if (($csf['uuid'] ?? null) === $uuid) {
                    return $csf;
                }
            }
        }
    }

    return null;
}

function montageLivretProps(int $marinId): array
{
    return [
        'marinId' => $marinId,
        'Marin' => Marin::class,
        'UserGeneratedEvent' => UserGeneratedEvent::class,
        'PermissionPrefix' => 'fcmcentral',
    ];
}

describe('LivretFcmLight - Pièce jointe', function () {
    it('ajoute une pièce jointe sur un élément validé et la stocke sur S3', function () {
        $marin = creerMarinAvecSavoirFaireValideLivret();

        $csfAvant = trouverCsfLivret($marin, LIVRET_SF_UUID);
        expect($csfAvant)->not->toBeNull();
        expect($csfAvant['etat_valid'] ?? false)->toBeTrue();
        expect($csfAvant['piece_jointe'] ?? null)->toBeNull();

        $fichier = UploadedFile::fake()->create('justificatif.pdf', 100, 'application/pdf');

        Livewire::actingAs($this->admin)
            ->test(LivretFcmLight::class, montageLivretProps($marin->id))
            ->call('ouvrirGestionPieceJointe', 'savoirfaire', LIVRET_SF_UUID)
            ->set('nouvellePieceJointeElement', $fichier)
            ->call('ajouterPieceJointeSurElement')
        ;

        $marin->refresh();
        $csfApres = trouverCsfLivret($marin, LIVRET_SF_UUID);

        expect($csfApres['piece_jointe'] ?? null)->not->toBeNull();
        Storage::disk('s3-private')->assertExists($csfApres['piece_jointe']);
    });

    it('refuse d\'ajouter une pièce jointe sur un élément non validé', function () {
        $fcmMarin = FcmMarin::factory()->create();
        $marin = $fcmMarin->marin;

        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::MARIN_EN_FCM,
            user_uuid: 'test-user',
            object_class: ObjectClasses::MARIN,
            object_uuid: $marin->nid,
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
            object_uuid: $marin->nid,
            detail: ['parcours' => $parcours->uuid]
        ));

        $marin->refresh();

        $fichier = UploadedFile::fake()->create('justificatif.pdf', 100, 'application/pdf');

        Livewire::actingAs($this->admin)
            ->test(LivretFcmLight::class, montageLivretProps($marin->id))
            ->call('ouvrirGestionPieceJointe', 'savoirfaire', LIVRET_SF_UUID)
            ->set('nouvellePieceJointeElement', $fichier)
            ->call('ajouterPieceJointeSurElement')
        ;

        $marin->refresh();
        $csf = trouverCsfLivret($marin, LIVRET_SF_UUID);

        expect($csf['piece_jointe'] ?? null)->toBeNull();
        Storage::disk('s3-private')->assertDirectoryEmpty('fcmcentral/livretfcm/pieces-jointes');
    });

    it('supprime une pièce jointe existante : référence retirée du parcours et fichier supprimé de S3', function () {
        $marin = creerMarinAvecSavoirFaireValideLivret();

        $fichier = UploadedFile::fake()->create('justificatif.pdf', 100, 'application/pdf');

        Livewire::actingAs($this->admin)
            ->test(LivretFcmLight::class, montageLivretProps($marin->id))
            ->call('ouvrirGestionPieceJointe', 'savoirfaire', LIVRET_SF_UUID)
            ->set('nouvellePieceJointeElement', $fichier)
            ->call('ajouterPieceJointeSurElement')
        ;

        $marin->refresh();
        $chemin = trouverCsfLivret($marin, LIVRET_SF_UUID)['piece_jointe'] ?? null;
        expect($chemin)->not->toBeNull();
        Storage::disk('s3-private')->assertExists($chemin);

        Livewire::actingAs($this->admin)
            ->test(LivretFcmLight::class, montageLivretProps($marin->id))
            ->call('supprimerPieceJointeSurElement', 'savoirfaire', LIVRET_SF_UUID)
        ;

        $marin->refresh();
        $csfApres = trouverCsfLivret($marin, LIVRET_SF_UUID);

        expect($csfApres['piece_jointe'] ?? null)->toBeNull();
        Storage::disk('s3-private')->assertMissing($chemin);
    });
});
