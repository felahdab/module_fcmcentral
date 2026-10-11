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
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages\FicheBilan;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

pest()->group('Fcm Central');

const FICHE_BILAN_SF_UUID = 'deasm1b1-1234-5678-cosf-11aa1c1c1sf1';
const FICHE_BILAN_OBJECTIFS = [
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
 * Crée un marin en FCM avec un savoir-faire (FICHE_BILAN_SF_UUID) validé
 * (les 3 objectifs qui le composent sont validés, ce qui valide le csf lui-même).
 */
function creerMarinAvecSavoirFaireValideFicheBilan(): \Modules\FcmCentral\Models\Marin
{
    $marin = \Modules\FcmCentral\Models\Marin::factory()->create();

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

    foreach (FICHE_BILAN_OBJECTIFS as $objectif) {
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

function trouverCsfFicheBilan(\Modules\FcmCentral\Models\Marin $marin, string $uuid): ?array
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

describe('FicheBilan - Pièce jointe', function () {
    it('ajoute une pièce jointe sur un savoir-faire validé et la stocke sur S3', function () {
        $marin = creerMarinAvecSavoirFaireValideFicheBilan();

        $csfAvant = trouverCsfFicheBilan($marin, FICHE_BILAN_SF_UUID);
        expect($csfAvant)->not->toBeNull();
        expect($csfAvant['etat_valid'] ?? false)->toBeTrue();
        expect($csfAvant['piece_jointe'] ?? null)->toBeNull();

        $fichier = UploadedFile::fake()->create('justificatif.pdf', 100, 'application/pdf');

        Livewire::actingAs($this->admin)
            ->test(FicheBilan::class, ['record' => $marin->id])
            ->call('ouvrirAjoutPieceJointe', FICHE_BILAN_SF_UUID)
            ->set('nouvellePieceJointe', $fichier)
            ->call('ajouterPieceJointe')
        ;

        $marin->refresh();
        $csfApres = trouverCsfFicheBilan($marin, FICHE_BILAN_SF_UUID);

        expect($csfApres['piece_jointe'] ?? null)->not->toBeNull();

        Storage::disk('s3-private')->assertExists($csfApres['piece_jointe']);
    });

    it('refuse d\'ajouter une pièce jointe sur un savoir-faire non validé', function () {
        // Marin sans validation des objectifs : le csf n'est pas etat_valid.
        $marin = \Modules\FcmCentral\Models\Marin::factory()->create();

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
            ->test(FicheBilan::class, ['record' => $marin->id])
            ->call('ouvrirAjoutPieceJointe', FICHE_BILAN_SF_UUID)
            ->set('nouvellePieceJointe', $fichier)
            ->call('ajouterPieceJointe')
        ;

        $marin->refresh();
        $csf = trouverCsfFicheBilan($marin, FICHE_BILAN_SF_UUID);

        expect($csf['piece_jointe'] ?? null)->toBeNull();
        Storage::disk('s3-private')->assertDirectoryEmpty('fcmcentral/livretfcm/pieces-jointes');
    });

    it('supprime une pièce jointe existante : référence retirée du parcours et fichier supprimé de S3', function () {
        $marin = creerMarinAvecSavoirFaireValideFicheBilan();

        $fichier = UploadedFile::fake()->create('justificatif.pdf', 100, 'application/pdf');

        Livewire::actingAs($this->admin)
            ->test(FicheBilan::class, ['record' => $marin->id])
            ->call('ouvrirAjoutPieceJointe', FICHE_BILAN_SF_UUID)
            ->set('nouvellePieceJointe', $fichier)
            ->call('ajouterPieceJointe')
        ;

        $marin->refresh();
        $chemin = trouverCsfFicheBilan($marin, FICHE_BILAN_SF_UUID)['piece_jointe'] ?? null;
        expect($chemin)->not->toBeNull();
        Storage::disk('s3-private')->assertExists($chemin);

        Livewire::actingAs($this->admin)
            ->test(FicheBilan::class, ['record' => $marin->id])
            ->call('supprimerPieceJointe', FICHE_BILAN_SF_UUID)
        ;

        $marin->refresh();
        $csfApres = trouverCsfFicheBilan($marin, FICHE_BILAN_SF_UUID);

        expect($csfApres['piece_jointe'] ?? null)->toBeNull();
        Storage::disk('s3-private')->assertMissing($chemin);
    });
});
