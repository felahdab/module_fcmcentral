<?php

use App\Models\User;
use Livewire\Livewire;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\Pages\EditParcours;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\RelationManagers\AffectationsRelationManager;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\RelationManagers\CompetencesRelationManager;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\RelationManagers\SavoirfairesRelationManager;

use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Models\Affectation;
use Modules\FcmCentral\Models\Competence;
use Modules\FcmCentral\Models\CompetenceSavoirfaire;
use Modules\FcmCentral\Models\SavoirfaireType;

uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

pest()->group("Fcm Central");

describe('Gestion Parcours Creation Edit RM', function () {

    beforeEach(function () {
        $this->admin = User::factory()->create();

        // ✅ Fix route : initialiser le panel Filament pour la génération d'URLs
        \Filament\Facades\Filament::setCurrentPanel(
            \Filament\Facades\Filament::getPanel('Skeletor')
        );

        // ✅ Fix autorisation : accorder tous les droits pour les tests
        Gate::before(fn () => true);
    });

   

   

   

    // ============================================================
    // AFFECTATIONS RELATION MANAGER
    // ============================================================

    it('affiche les affectations du parcours', function () {
        Artisan::call('fcmcentral:charge-parcours Modules/FcmCentral/tests/Feature/parcours_deasm.json');
        $parcours = Parcours::first();

        $this->assertGreaterThan(0, $parcours->affectations->count());

        Livewire::actingAs($this->admin)
            ->test(AffectationsRelationManager::class, [
                'ownerRecord' => $parcours,
                'pageClass'   => EditParcours::class,
            ])
            ->assertSuccessful()
            ->assertCanSeeTableRecords($parcours->affectations);
    });

    it('peut attacher une affectation existante au parcours', function () {
        Artisan::call('fcmcentral:charge-parcours Modules/FcmCentral/tests/Feature/parcours_deasm.json');
        $parcours = Parcours::first();

        $affectation = Affectation::factory()->create([
            'uuid'          => Str::uuid(),
            'code'          => 'AFF-TEST',
            'libelle_court' => 'Affectation test',
        ]);

        Livewire::actingAs($this->admin)
            ->test(AffectationsRelationManager::class, [
                'ownerRecord' => $parcours,
                'pageClass'   => EditParcours::class,
            ])
            // ✅ Fix : callTableAction (pas callAction) pour headerActions
            ->callTableAction('attach', data: ['recordId' => $affectation->id])
            ->assertHasNoTableActionErrors();

        $this->assertTrue(
            $parcours->fresh()->affectations->contains('id', $affectation->id)
        );
    });

    it('peut creer une nouvelle affectation dans le parcours', function () {
        Artisan::call('fcmcentral:charge-parcours Modules/FcmCentral/tests/Feature/parcours_deasm.json');
        $parcours   = Parcours::first();
        $countAvant = $parcours->affectations->count();

        Livewire::actingAs($this->admin)
            ->test(AffectationsRelationManager::class, [
                'ownerRecord' => $parcours,
                'pageClass'   => EditParcours::class,
            ])
            // ✅ Fix : callTableAction pour headerActions
            ->callTableAction('create', data: [
                'code'          => 'AFF-NEW',
                'libelle_court' => 'Nouvelle affectation',
                'libelle_long'  => 'Description longue',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertEquals($countAvant + 1, $parcours->fresh()->affectations->count());
    });

    it('peut modifier une affectation', function () {
        Artisan::call('fcmcentral:charge-parcours Modules/FcmCentral/tests/Feature/parcours_deasm.json');
        $parcours    = Parcours::first();
        $affectation = $parcours->affectations->first();

        Livewire::actingAs($this->admin)
            ->test(AffectationsRelationManager::class, [
                'ownerRecord' => $parcours,
                'pageClass'   => EditParcours::class,
            ])
            // ✅ Gate::before(true) dans beforeEach rend EditAction visible
            ->callTableAction('edit', $affectation, data: [
                'code'          => 'AFF-MODIF',
                'libelle_court' => 'Libellé modifié',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertEquals('AFF-MODIF', $affectation->fresh()->code);
    });

    // ============================================================
    // COMPETENCES RELATION MANAGER
    // ============================================================

    it('affiche les competences du parcours', function () {
        Artisan::call('fcmcentral:charge-parcours Modules/FcmCentral/tests/Feature/parcours_deasm.json');
        $parcours = Parcours::first();

        $competences = Competence::where('parcours_type_id', $parcours->parcours_type_id)
            ->whereIn('affectation_id', $parcours->affectations->pluck('id'))
            ->get();

        $this->assertGreaterThan(0, $competences->count());

        Livewire::actingAs($this->admin)
            ->test(CompetencesRelationManager::class, [
                'ownerRecord' => $parcours,
                'pageClass'   => EditParcours::class,
            ])
            ->assertSuccessful()
            ->assertCanSeeTableRecords($competences);
    });

    it('peut creer une competence dans le parcours', function () {
        Artisan::call('fcmcentral:charge-parcours Modules/FcmCentral/tests/Feature/parcours_deasm.json');
        $parcours    = Parcours::first();
        $affectation = $parcours->affectations->first();
        $countAvant  = Competence::where('parcours_type_id', $parcours->parcours_type_id)->count();

        Livewire::actingAs($this->admin)
            ->test(CompetencesRelationManager::class, [
                'ownerRecord' => $parcours,
                'pageClass'   => EditParcours::class,
            ])
            // ✅ Fix : callTableAction pour headerActions
            ->callTableAction('creer_competence', data: [
                'affectation_id' => $affectation->id,
                'code'           => 'COMP-TEST',
                'libelle_court'  => 'Compétence test',
                'libelle_long'   => null,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertEquals(
            $countAvant + 1,
            Competence::where('parcours_type_id', $parcours->parcours_type_id)->count()
        );
    });

    it('peut modifier une competence', function () {
        Artisan::call('fcmcentral:charge-parcours Modules/FcmCentral/tests/Feature/parcours_deasm.json');
        $parcours   = Parcours::first();
        $competence = Competence::where('parcours_type_id', $parcours->parcours_type_id)->first();

        Livewire::actingAs($this->admin)
            ->test(CompetencesRelationManager::class, [
                'ownerRecord' => $parcours,
                'pageClass'   => EditParcours::class,
            ])
            ->callTableAction('edit', $competence, data: [
                'affectation_id' => $competence->affectation_id,
                'code'           => 'COMP-MODIF',
                'libelle_court'  => 'Compétence modifiée',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertEquals('COMP-MODIF', $competence->fresh()->code);
    });

    // ============================================================
    // SAVOIR-FAIRES RELATION MANAGER
    // ============================================================

    it('affiche les savoir-faires du parcours', function () {
        Artisan::call('fcmcentral:charge-parcours Modules/FcmCentral/tests/Feature/parcours_deasm.json');
        $parcours = Parcours::first();

        $competenceIds = Competence::where('parcours_type_id', $parcours->parcours_type_id)
            ->whereIn('affectation_id', $parcours->affectations->pluck('id'))
            ->pluck('id');

        // ✅ Fix pagination : limiter à 10 (taille de page par défaut)
        $csfs = CompetenceSavoirfaire::whereIn('competence_id', $competenceIds)
            ->orderBy('ordre')
            ->take(10)
            ->get();

        $this->assertGreaterThan(0, $csfs->count());

        Livewire::actingAs($this->admin)
            ->test(SavoirfairesRelationManager::class, [
                'ownerRecord' => $parcours,
                'pageClass'   => EditParcours::class,
            ])
            ->assertSuccessful()
            ->assertCanSeeTableRecords($csfs);
    });

    it('peut creer un savoir-faire sans objectifs', function () {
        Artisan::call('fcmcentral:charge-parcours Modules/FcmCentral/tests/Feature/parcours_deasm.json');
        $parcours   = Parcours::first();
        $competence = Competence::where('parcours_type_id', $parcours->parcours_type_id)->first();
        $type       = SavoirfaireType::where('code', 'S')->first();
        $countAvant = CompetenceSavoirfaire::where('competence_id', $competence->id)->count();

        Livewire::actingAs($this->admin)
            ->test(SavoirfairesRelationManager::class, [
                'ownerRecord' => $parcours,
                'pageClass'   => EditParcours::class,
            ])
            // ✅ Fix : callTableAction pour CreateAction dans headerActions
            ->callTableAction('create', data: [
                'competence_id'          => $competence->id,
                'nouvelle_code'          => 'SF-TEST',
                'nouvelle_libelle_court' => 'Savoir-faire test',
                'nouvelle_libelle_long'  => null,
                'savoirfaire_type_id'    => $type->id,
                'libelle_court'          => 'Contexte test',
                'heures'                 => 7,
                'jours'                  => null,
                'objectifs'              => [],
            ])
            ->assertHasNoTableActionErrors();

        $this->assertEquals(
            $countAvant + 1,
            CompetenceSavoirfaire::where('competence_id', $competence->id)->count()
        );
    });

    it('peut creer un savoir-faire de type compagnonnage avec objectifs', function () {
        Artisan::call('fcmcentral:charge-parcours Modules/FcmCentral/tests/Feature/parcours_deasm.json');
        $parcours   = Parcours::first();
        $competence = Competence::where('parcours_type_id', $parcours->parcours_type_id)->first();
        $typeC      = SavoirfaireType::where('code', 'C')->first();

        Livewire::actingAs($this->admin)
            ->test(SavoirfairesRelationManager::class, [
                'ownerRecord' => $parcours,
                'pageClass'   => EditParcours::class,
            ])
            ->callTableAction('create', data: [
                'competence_id'          => $competence->id,
                'nouvelle_code'          => 'SF-COMP',
                'nouvelle_libelle_court' => 'SF Compagnonnage',
                'savoirfaire_type_id'    => $typeC->id,
                'libelle_court'          => null,
                'heures'                 => 14,
                'jours'                  => null,
                'objectifs'              => [
                    ['code' => 'OBJ-1', 'libelle_court' => 'Objectif 1', 'heures' => 7],
                    ['code' => 'OBJ-2', 'libelle_court' => 'Objectif 2', 'heures' => 7],
                ],
            ])
            ->assertHasNoTableActionErrors();

        $csf = CompetenceSavoirfaire::whereHas('savoirfaire', fn ($q) => $q->where('code', 'SF-COMP'))->first();
        $this->assertNotNull($csf);
        $this->assertEquals(2, $csf->objectifs()->count());
    });

    it('peut modifier un savoir-faire existant', function () {
        Artisan::call('fcmcentral:charge-parcours Modules/FcmCentral/tests/Feature/parcours_deasm.json');
        $parcours = Parcours::first();

        $competenceIds = Competence::where('parcours_type_id', $parcours->parcours_type_id)
            ->whereIn('affectation_id', $parcours->affectations->pluck('id'))
            ->pluck('id');

        $csf  = CompetenceSavoirfaire::whereIn('competence_id', $competenceIds)->first();
        $type = SavoirfaireType::first();

        Livewire::actingAs($this->admin)
            ->test(SavoirfairesRelationManager::class, [
                'ownerRecord' => $parcours,
                'pageClass'   => EditParcours::class,
            ])
            ->callTableAction('edit', $csf, data: [
                'competence_id'       => $csf->competence_id,
                'savoirfaire_id'      => $csf->savoirfaire_id,
                'sf_code'             => 'SF-MODIF',
                'sf_libelle_court'    => 'SF modifié',
                'sf_libelle_long'     => null,
                'savoirfaire_type_id' => $type->id,
                'libelle_court'       => 'Contexte modifié',
                'heures'              => 21,
                'jours'               => null,
                'objectifs'           => [],
            ])
            ->assertHasNoTableActionErrors();

        $this->assertEquals('SF-MODIF', $csf->savoirfaire->fresh()->code);
        $this->assertEquals(21, $csf->fresh()->heures);
    });

    it('peut supprimer un savoir-faire', function () {
        Artisan::call('fcmcentral:charge-parcours Modules/FcmCentral/tests/Feature/parcours_deasm.json');
        $parcours = Parcours::first();

        $competenceIds = Competence::where('parcours_type_id', $parcours->parcours_type_id)
            ->whereIn('affectation_id', $parcours->affectations->pluck('id'))
            ->pluck('id');

        $csf        = CompetenceSavoirfaire::whereIn('competence_id', $competenceIds)->first();
        $csfId      = $csf->id;
        $countAvant = CompetenceSavoirfaire::whereIn('competence_id', $competenceIds)->count();

        Livewire::actingAs($this->admin)
            ->test(SavoirfairesRelationManager::class, [
                'ownerRecord' => $parcours,
                'pageClass'   => EditParcours::class,
            ])
            ->callTableAction('delete', $csf)
            ->assertHasNoTableActionErrors();

        $this->assertEquals($countAvant - 1, CompetenceSavoirfaire::whereIn('competence_id', $competenceIds)->count());
        $this->assertNull(CompetenceSavoirfaire::find($csfId));
    });

});
