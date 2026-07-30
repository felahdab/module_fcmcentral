<?php

use App\Models\User;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages\FicheBilan;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Modules\RH\Models\Unite;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

pest()->group('Tata3');

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('FCM Central'));
    Storage::fake('s3-private');
    $this->admin = User::factory()->create(['admin' => 1]);
});

/**
 * Crée un marin en FCM, sans document annexe.
 */
function creerMarinEnFcmSansDocument(): Marin
{
    // unite_id renseigné : sans ça, MarinFiltreParUniteScope (module RH, non modifié) génère
    // une sous-requête avec une colonne "id" ambiguë dans est_mentor_de() — bug préexistant.
    $marin = Marin::factory()->create(['unite_id' => Unite::factory()->create()->id]);

    event(new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::MARIN_EN_FCM,
        user_uuid: 'test-user',
        object_class: ObjectClasses::MARIN,
        object_uuid: $marin->uuid,
        detail: ['cohorte_id' => 1, 'date_de_debut' => Carbon::tomorrow()]
    ));

    $marin->refresh();

    return $marin;
}

describe('FicheBilan - Documents annexes', function () {
    it('ajoute un document annexe et le stocke sur S3', function () {
        $marin = creerMarinEnFcmSansDocument();

        expect($marin->complements_fcm->documents_annexes ?? [])->toBeEmpty();

        $fichier = UploadedFile::fake()->create('diplome.pdf', 100, 'application/pdf');

        Livewire::actingAs($this->admin)
            ->test(FicheBilan::class, ['record' => $marin->id])
            ->set('nomDocumentAnnexe', 'Diplôme de spécialité')
            ->set('nouveauDocumentAnnexe', $fichier)
            ->call('ajouterDocumentAnnexe')
        ;

        $marin->refresh();
        $documents = $marin->complements_fcm->documents_annexes ?? [];

        expect($documents)->toHaveCount(1);
        expect($documents[0]['nom'])->toBe('Diplôme de spécialité');
        expect($documents[0]['uuid'])->not->toBeNull();

        Storage::disk('s3-private')->assertExists($documents[0]['chemin']);
    });

    it('refuse d\'ajouter un document annexe sans nom', function () {
        $marin = creerMarinEnFcmSansDocument();

        $fichier = UploadedFile::fake()->create('diplome.pdf', 100, 'application/pdf');

        Livewire::actingAs($this->admin)
            ->test(FicheBilan::class, ['record' => $marin->id])
            ->set('nomDocumentAnnexe', '')
            ->set('nouveauDocumentAnnexe', $fichier)
            ->call('ajouterDocumentAnnexe')
            ->assertHasErrors(['nomDocumentAnnexe' => 'required'])
        ;

        $marin->refresh();
        expect($marin->complements_fcm->documents_annexes ?? [])->toBeEmpty();
    });

    it('supprime un document annexe existant : référence retirée et fichier supprimé de S3', function () {
        $marin = creerMarinEnFcmSansDocument();

        $fichier = UploadedFile::fake()->create('diplome.pdf', 100, 'application/pdf');

        Livewire::actingAs($this->admin)
            ->test(FicheBilan::class, ['record' => $marin->id])
            ->set('nomDocumentAnnexe', 'Diplôme de spécialité')
            ->set('nouveauDocumentAnnexe', $fichier)
            ->call('ajouterDocumentAnnexe')
        ;

        $marin->refresh();
        $document = $marin->complements_fcm->documents_annexes[0];
        Storage::disk('s3-private')->assertExists($document['chemin']);

        Livewire::actingAs($this->admin)
            ->test(FicheBilan::class, ['record' => $marin->id])
            ->call('supprimerDocumentAnnexe', $document['uuid'])
        ;

        $marin->refresh();
        expect($marin->complements_fcm->documents_annexes ?? [])->toBeEmpty();
        Storage::disk('s3-private')->assertMissing($document['chemin']);
    });

    it('permet d\'ajouter puis de télécharger le document avec le bon contenu, via le contrôleur dédié', function () {
        $marin = creerMarinEnFcmSansDocument();

        $fichier = UploadedFile::fake()->createWithContent('diplome.pdf', 'contenu-diplome-de-test');

        Livewire::actingAs($this->admin)
            ->test(FicheBilan::class, ['record' => $marin->id])
            ->set('nomDocumentAnnexe', 'Diplôme de spécialité')
            ->set('nouveauDocumentAnnexe', $fichier)
            ->call('ajouterDocumentAnnexe')
        ;

        $marin->refresh();
        $document = $marin->complements_fcm->documents_annexes[0];

        $userDuMarin = $marin->createUser();

        $reponse = $this->actingAs($userDuMarin)->get(route('fcmcentral.document-annexe.telecharger', [
            'marinUuid' => $marin->uuid,
            'documentUuid' => $document['uuid'],
        ]));

        $reponse->assertSuccessful();
        expect($reponse->streamedContent())->toBe('contenu-diplome-de-test');
    });
});
