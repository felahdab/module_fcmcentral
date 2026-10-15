<?php

use App\Models\User;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Modules\RH\Models\Unite;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

pest()->group('Fcm Central');

beforeEach(function () {
    Storage::fake('s3-private');
    $this->admin = User::factory()->create(['admin' => 1]);
});

/**
 * Crée un marin en FCM avec un document annexe réellement présent sur le disque s3-private
 * (sans passer par les composants Livewire).
 */
function creerMarinAvecDocumentAnnexeStocke(): array
{
    // unite_id renseigné : sans ça, MarinFiltreParUniteScope (module RH, non modifié) génère
    // une sous-requête avec une colonne "id" ambiguë dans est_mentor_de() — bug préexistant
    // sans rapport avec ce contrôleur, contourné ici comme en usage réel.
    $marin = Marin::factory()->create(['unite_id' => Unite::factory()->create()->id]);

    event(new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::MARIN_EN_FCM,
        user_uuid: 'test-user',
        object_class: ObjectClasses::MARIN,
        object_uuid: $marin->nid,
        detail: ['cohorte_id' => 1, 'date_de_debut' => \Carbon\Carbon::tomorrow()]
    ));

    $marin->refresh();

    $documentUuid = (string) \Illuminate\Support\Str::uuid();
    $chemin = sprintf('fcmcentral/documents-annexes/marins/%s/2026/07/diplome.pdf', $marin->id);
    Storage::disk('s3-private')->put($chemin, 'contenu-pdf-de-test');

    $fcmMarin = $marin->complements_fcm;
    $fcmMarin->documents_annexes = [
        [
            'uuid' => $documentUuid,
            'nom' => 'Diplôme',
            'chemin' => $chemin,
            'ajoute_par' => 'test-user',
            'ajoute_le' => now()->toDateTimeString(),
        ],
    ];
    $fcmMarin->save();

    $marin->refresh();

    return [$marin, $documentUuid, $chemin];
}

function urlDocumentAnnexe(Marin $marin, string $documentUuid): string
{
    return route('fcmcentral.document-annexe.telecharger', [
        'marinUuid' => $marin->uuid,
        'documentUuid' => $documentUuid,
    ]);
}

describe('DocumentAnnexeDownloadController - Autorisations', function () {
    it('refuse l\'accès à un utilisateur non connecté', function () {
        [$marin, $documentUuid] = creerMarinAvecDocumentAnnexeStocke();

        $this->get(urlDocumentAnnexe($marin, $documentUuid))
            ->assertRedirect() // middleware auth : redirection vers la page de login
        ;
    });

    it('refuse l\'accès à un utilisateur connecté qui n\'est ni le marin, ni son mentor, ni autorisé par permission', function () {
        [$marin, $documentUuid] = creerMarinAvecDocumentAnnexeStocke();

        $intrus = User::factory()->create(['admin' => 0]);
        $voir_tous_les_marins = Permission::create(['name' => 'rh::voir_tous_les_marins', 'guard_name' => 'web']);
        $intrus->givePermissionTo($voir_tous_les_marins);

        $this->actingAs($intrus)
            ->get(urlDocumentAnnexe($marin, $documentUuid))
            ->assertForbidden()
        ;
    });

    it('refuse l\'accès à un autre marin (non mentor) qui tente de voir le document d\'un marin différent', function () {
        [$marinCible, $documentUuid] = creerMarinAvecDocumentAnnexeStocke();

        $autreMarin = Marin::factory()->create(['unite_id' => Unite::factory()->create()->id]);
        $userAutreMarin = $autreMarin->createUser();

        $this->actingAs($userAutreMarin)
            ->get(urlDocumentAnnexe($marinCible, $documentUuid))
            ->assertForbidden()
        ;
    });

    it('autorise le marin concerné à voir son propre document annexe, avec le bon contenu', function () {
        [$marin, $documentUuid, $chemin] = creerMarinAvecDocumentAnnexeStocke();

        $userDuMarin = $marin->createUser();

        $reponse = $this->actingAs($userDuMarin)
            ->get(urlDocumentAnnexe($marin, $documentUuid))
        ;

        $reponse->assertSuccessful();
        expect($reponse->streamedContent())->toBe(Storage::disk('s3-private')->get($chemin));
        expect($reponse->streamedContent())->toBe('contenu-pdf-de-test');
    });

    it('autorise un mentor du marin à voir son document annexe', function () {
        [$marin, $documentUuid] = creerMarinAvecDocumentAnnexeStocke();

        $mentor = Marin::factory()->create(['unite_id' => Unite::factory()->create()->id]);
        $userMentor = $mentor->createUser();

        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::ATTRIBUER_MENTOR_MARIN,
            user_uuid: 'test-user',
            object_class: ObjectClasses::MARIN,
            object_uuid: $marin->nid,
            detail: ['mentor' => [$mentor->uuid]]
        ));

        $this->actingAs($userMentor)
            ->get(urlDocumentAnnexe($marin, $documentUuid))
            ->assertSuccessful()
        ;
    });

    it('autorise un utilisateur ayant la permission fcmcentral::document_annexe.voir, même sans lien avec le marin', function () {
        [$marin, $documentUuid] = creerMarinAvecDocumentAnnexeStocke();

        $gestionnaire = User::factory()->create(['admin' => 0]);
        $permission = \App\Models\Permission::firstOrCreate([
            'name' => 'fcmcentral::document_annexe.voir',
            'guard_name' => 'web',
        ]);
        $gestionnaire->givePermissionTo($permission);

        $this->actingAs($gestionnaire)
            ->get(urlDocumentAnnexe($marin, $documentUuid))
            ->assertSuccessful()
        ;
    });

    it('n\'autorise pas un utilisateur ayant seulement fcmcentral::piece_jointe.voir (permission différente)', function () {
        [$marin, $documentUuid] = creerMarinAvecDocumentAnnexeStocke();

        $utilisateur = User::factory()->create(['admin' => 0]);
        $permission = \App\Models\Permission::firstOrCreate([
            'name' => 'fcmcentral::piece_jointe.voir',
            'guard_name' => 'web',
        ]);
        $utilisateur->givePermissionTo($permission);

        $this->actingAs($utilisateur)
            ->get(urlDocumentAnnexe($marin, $documentUuid))
            ->assertForbidden()
        ;
    });

    it('renvoie 404 si le document annexe n\'existe pas pour ce marin', function () {
        [$marin] = creerMarinAvecDocumentAnnexeStocke();

        $userDuMarin = $marin->createUser();

        $this->actingAs($userDuMarin)
            ->get(urlDocumentAnnexe($marin, 'uuid-inexistant'))
            ->assertNotFound()
        ;
    });
});
