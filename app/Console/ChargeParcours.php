<?php

namespace Modules\FcmCentral\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Modules\FcmCentral\Models\Affectation;
use Modules\FcmCentral\Models\Competence;
use Modules\FcmCentral\Models\CompetenceSavoirfaire;
use Modules\FcmCentral\Models\Objectif;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Models\ParcoursType;
use Modules\FcmCentral\Models\Savoirfaire;
use Modules\FcmCentral\Models\SavoirfaireType;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ChargeParcours extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     */
    protected $name = 'fcmcentral:charge-parcours';

    /**
     * The console command description.
     */
    protected $description = 'Charge la définition d\'un parcours depuis un fichier.';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filename = $this->argument('filename');
        if (!File::exists($filename)) {
            $this->fail("Le fichier {$filename} n'existe pas: ");
        }

        $content = json_decode(File::get($filename), true);

        $force = $this->option('force');

        $this->chargeParcoursEnBase($content, $force);
    }

    public function chargeParcoursEnBase($content, $force = false)
    {
        /**
         * Le parcours doit impérativement avoir un parcours_type (parcours_type_uuid != NULL).
         * Il faut donc d'abord regarder si le parcours_type est bien défini.
         */
        $parcours_type = ParcoursType::where('uuid', $content['parcours_type']['uuid'])->first();
        if (null == $parcours_type) {
            $this->warn("Le parcours type référencé dans ce fichier de données n'existe pas. On créé un parcours type bidon pour permettre l'import.");
            $parcours_type = ParcoursType::create($content['parcours_type']);
        }
        /*
         * Le parcours type référencé dans les données à charger existe désormais. On rajoute
         * son id à la description du parcours en vue de l'import de ce dernier et de la création
         * de la référence.
         */
        $content['parcours_type_id'] = $parcours_type->id;

        $parcours = Parcours::where('uuid', $uuid = Arr::get($content, 'uuid'))->first();
        if (null != $parcours) {
            if (!$force) {
                $this->fail("Un parcours avec l'uuid {$uuid} existe déjà!");
            }
            $this->warn("Un parcours avec l'uuid {$uuid} existe déjà ! On poursuit la mise à jour.");
            $parcours->update(
                Arr::except($content, ['id', 'affectations', 'parcours_type'])
            );
        } else {
            $this->info('Creation du parcours');
            $parcours = Parcours::firstOrCreate(
                ['uuid' => $content['uuid']],
                Arr::except($content, ['id', 'affectations', 'parcours_type'])
            );
        }
        /*
         * Quand on arrive là, on dispose du parcours mis à jour ou créé à partir du fichier
         * en cours de traitement. On passe à l'insertion ou la mise à jour des affectations.
         */
        foreach (Arr::get($content, 'affectations') as $affectation) {
            $this->chargeAffectationEnBase($parcours, $affectation, $force);
        }
    }

    public function chargeAffectationEnBase(Parcours $parcours, $affectation, $force = false)
    {
        $aff = Affectation::where('uuid', $uuid = Arr::get($affectation, 'uuid'))->first();
        if (null != $aff) {
            if (!$force) {
                $this->fail("Une affectation avec l'uuid {$uuid} existe déjà!");
            }
            $this->warn("Une affectation avec l'uuid {$uuid} existe déjà ! On poursuit la mise à jour.");
            $aff->update(
                Arr::except($affectation, ['id', 'pivot', 'competences'])
            );
        } else {
            $this->info("Creation de l'affectation {$uuid}");
            $aff = Affectation::firstOrCreate(
                ['uuid' => $affectation['uuid']],
                Arr::except($affectation, ['id', 'pivot', 'competences'])
            );
        }

        /**
         * Quand on arrive là, on a bien l'affectation en base. Il reste à faire 2 choses:
         * - l'associer au parcours qu'on est en train de charger
         * - charger les competences
         */
        $ordre = Arr::get($affectation, 'pivot.ordre');
        if (null == $ordre) {
            $this->fail("L'ordre de l'affectation {$uuid} n'est pas défini. Vérifiez vos données.");
        }
        if (!$parcours->affectations->contains($aff)) {
            $parcours->affectations()->attach($aff, ['ordre' => $ordre]);
        }

        // On a associé l'affectation au parcours en reprenant son ordre. On charge les compétences:
        foreach (Arr::get($affectation, 'competences') as $competence) {
            $this->chargeCompetenceEnBase($aff, $competence, $force);
        }
    }

    public function chargeCompetenceEnBase(Affectation $affectation, $competence, $force = false)
    {
        // ✅ Récupère le parcours_type_id depuis le parcours lié à l'affectation
        $parcours = $affectation->parcours()->first();
        $parcoursTypeId = $parcours?->parcours_type_id;

        $comp = Competence::where('uuid', $uuid = Arr::get($competence, 'uuid'))->first();
        if (null != $comp) {
            if (!$force) {
                $this->fail("Une compétence avec l'uuid {$uuid} existe déjà!");
            }
            $this->warn("Une compétence avec l'uuid {$uuid} existe déjà ! On poursuit la mise à jour.");
            $comp->update(
                Arr::except($competence, ['id', 'competenceSavoirfaires'])
            );
        } else {
            $this->info("Creation de la compétence {$uuid}");
            $comp = Competence::firstOrCreate(
                ['uuid' => $competence['uuid']],
                array_merge(
                    Arr::except($competence, ['id', 'competenceSavoirfaires']),
                    ['parcours_type_id' => $parcoursTypeId] // ✅
                )
            );
        }

        $comp->affectation()->associate($affectation);
        $comp->parcours_type_id = $parcoursTypeId; // ✅ force la mise à jour
        $comp->save();

        foreach (Arr::get($competence, 'competenceSavoirfaires') as $competenceSavoirfaire) {
            $this->chargeCompetenceSavoirfaireEnBase($comp, $competenceSavoirfaire, $force);
        }
    }

    public function chargeCompetenceSavoirfaireEnBase(Competence $competence, $competenceSavoirfaire, $force = false)
    {
        /**
         * Un objet du type ComptenceSavoirfaire doit imperativement avoir:
         * - une competence (competence_id != NULL): c'est bon, on a $competence
         * - un savoir faire (savoirfaire_id != NULL: là, c'est pas encore bon: il faut récupérer le savoir faire et
         * le coller en base d'abord.)
         */
        $savoir_faire = Arr::get($competenceSavoirfaire, 'savoirfaire');
        $SF = $this->chargerSavoirFaireEnBase($savoir_faire, $competence, $force);

        /*
         * Voilà: maintenant, on a le savoir-faire qu'on va devoir utiliser lors de la mise à jour ou de la création de la
         * compétenceSavoirFaire. On le rajoute au tableau qui définit l'objet à créér ou mettre à jour.
         */
        $competenceSavoirfaire['savoirfaire_id'] = $SF->id;
        $competenceSavoirfaire['competence_id'] = $competence->id;

        $compSF = CompetenceSavoirfaire::where('uuid', $uuid = Arr::get($competenceSavoirfaire, 'uuid'))->first();
        if (null != $compSF) {
            if (!$force) {
                $this->fail("Une entrée compétenceSavoirfaire avec l'uuid {$uuid} existe déjà!");
            }
            $this->warn("Une entrée compétenceSavoirfaire avec l'uuid {$uuid} existe déjà ! On poursuit la mise à jour.");
            $compSF->update(
                Arr::except($competenceSavoirfaire, ['id', 'savoirfaire', 'objectifs', 'savoirfaire_type'])
            );
        } else {
            $this->info("Creation de l'entrée compétenceSavoirfaire {$uuid}");
            $compSF = CompetenceSavoirfaire::firstOrCreate(
                ['uuid' => $competenceSavoirfaire['uuid']],
                Arr::except($competenceSavoirfaire, ['id', 'savoirfaire', 'objectifs', 'savoirfaire_type'])
            );
        }

        /**
         * Quand on arrive là, on a bien la compétenceSavoirfaire en base. Il reste à faire X choses:
         * - charger le savoirfairetype associé
         * - charger les objectifs
         */
        $savoir_faire_type = Arr::get($competenceSavoirfaire, 'savoirfaire_type');
        $SFtype = $this->chargerSavoirFaireTypeEnBase($savoir_faire_type, $force);

        // On peut attacher ce savoir-faire-type à l'entrée compétenceSavoirFaire
        $compSF->savoirfaireType()->associate($SFtype);
        $compSF->save();

        // Reste à charger les objectifs.
        foreach (Arr::get($competenceSavoirfaire, 'objectifs') as $objectif) {
            $this->chargeObjectifEnBase($compSF, $objectif, $force);
        }
    }

    public function chargerSavoirFaireEnBase($savoirfaire, Competence $competence, $force = false)
    {
        $SF = Savoirfaire::where('uuid', $uuid = Arr::get($savoirfaire, 'uuid'))->first();
        if (null != $SF) {
            if (!$force) {
                $this->fail("Un savoir-faire  avec l'uuid {$uuid} existe déjà!");
            }
            $this->warn("Un savoir-faire avec l'uuid {$uuid} existe déjà ! On poursuit la mise à jour.");
            $SF->update(
                Arr::except($savoirfaire, ['id', 'competence_id'])
            );
        } else {
            $this->info("Creation du savoir-faire {$uuid}");
            $SF = Savoirfaire::firstOrCreate(
                ['uuid' => $savoirfaire['uuid']],
                Arr::except($savoirfaire, ['id', 'competence_id'])
            );
        }
        // Quand on arrive là, on a bien le savoir-faire en base. Il reste à l'associer à la compétence
        $SF->competence()->associate($competence);
        $SF->save();

        return $SF;
    }

    public function chargerSavoirFaireTypeEnBase($savoir_faire_type, $force = false)
    {
        /**
         * Voyons le savoir faire type.
         */
        $SFT = SavoirfaireType::where('uuid', $uuid = Arr::get($savoir_faire_type, 'uuid'))->first();
        if (null != $SFT) {
            if (!$force) {
                $this->fail("Un savoir-faire-type avec le uuid {$uuid} existe déjà!");
            }
            $this->warn("Un savoir-faire-type avec le uuid {$uuid} existe déjà ! On poursuit la mise à jour.");
            $SFT->update(
                Arr::except($savoir_faire_type, 'uuid')
            );
        } else {
            $this->info("Creation de l'entrée savoir-faire-type {$uuid}");
            $SFT = SavoirfaireType::firstOrCreate($savoir_faire_type);
        }

        return $SFT;
    }

    public function chargeObjectifEnBase(CompetenceSavoirfaire $compSF, $objectif, $force = false)
    {
        // Un objectif doit forcément avoir une entree CompetenceSavoirFaire (competence_savoir_faire_id != NULL)

        $objectif['competence_savoirfaire_id'] = $compSF->id;

        $obj = Objectif::where('uuid', $uuid = Arr::get($objectif, 'uuid'))->first();
        if (null != $obj) {
            if (!$force) {
                $this->fail("Un objectif avec l'uuid {$uuid} existe déjà!");
            }
            $this->warn("Une objectif avec l'uuid {$uuid} existe déjà ! On poursuit la mise à jour.");
            $obj->update(
                Arr::except($objectif, ['id'])
            );
        } else {
            $this->info("Creation de l'objectif : {$uuid}");
            $obj = Objectif::firstOrCreate(
                ['uuid' => $objectif['uuid']],
                Arr::except($objectif, ['id'])
            );
        }
    }

    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Remplacer les données déjà présentes en base s\'il y en a.'],
        ];
    }

    protected function getArguments()
    {
        return [
            ['filename', InputArgument::REQUIRED, 'Le nom du fichier de destination.'],
        ];
    }
}
