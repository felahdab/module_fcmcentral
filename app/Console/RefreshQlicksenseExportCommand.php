<?php

namespace Modules\FcmCentral\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\QlicksenseExport;

class RefreshQlicksenseExportCommand extends Command
{
    protected $signature = 'fcmcentral:refresh-qlicksense';

    protected $description = 'Rafraîchit la table fcmcentral_qlicksense_exports avec les données marins FCM pour QlickSense';

    public function handle(): int
    {
        $this->info('Début du rafraîchissement de la table QlickSense...');

        QlicksenseExport::query()->truncate();
        $this->info('Table vidée');

        // Seuls les marins ayant un parcours assigné (ligne dans fcmcentral_marin_parcours)
        $marins = Marin::with([
            'grade',
            'specialite',
            'unite',
            'complementsFcm.cohorte',
            'complementsFcm.mentors.grade',
            'complementsFcm.mentors.unite',
            'etats.typeEtat',
        ])->whereHas('complementsFcm.lignesDansMarinParcours')->get();

        $this->info("{$marins->count()} marins avec un parcours à traiter...");

        $progressBar = $this->output->createProgressBar($marins->count());
        $progressBar->start();

        foreach ($marins as $marin) {
            // Récupère la ligne MarinParcours la plus récente
            $mp = DB::table('fcmcentral_marin_parcours')
                ->where('fcmmarin_id', $marin->complementsFcm?->id)
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->first();

            if (! $mp) {
                $progressBar->advance();
                continue;
            }

            // Taux directement depuis fcmcentral_marin_parcours
            $tauxGlobal   = $mp->taux_global;
            $tauxStage    = $mp->taux_stage;
            $tauxActivite = $mp->taux_activite;

            // STAGES MANQUANTS — calculés depuis parcoursmarin JSON
            $stagesManquants = 0;
            $parcoursmarin = $mp->parcoursmarin ? json_decode($mp->parcoursmarin, true) : null;

            if ($parcoursmarin) {
                foreach ($parcoursmarin['affectations'] ?? [] as $affectation) {
                    foreach ($affectation['competences'] ?? [] as $competence) {
                        foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                            if (($csf['savoirfaire_type']['code'] ?? null) === 'S' && empty($csf['etat_valid'])) {
                                ++$stagesManquants;
                            }
                        }
                    }
                }
            }

            // GEL FCM
            $gelFcm = $marin->etats()
                ->whereHas('typeEtat', fn ($q) => $q->where('code', 'FCM_ENP'))
                ->orderBy('date_debut')
                ->first()?->date_debut;

            // VERT PPA
            $vertPpa = $marin->etats()
                ->whereHas('typeEtat', fn ($q) => $q->where('code', 'FCM_ACT'))
                ->orderBy('date_debut')
                ->first()?->date_debut;

            // TEMPS PPA
            $dateDebut = $marin->etats()->orderBy('date_debut')->first()?->date_debut;
            $tempsPpa  = $dateDebut ? Carbon::parse($dateDebut)->diffInDays(now()) : 0;

            // Premier mentor (BelongsToMany)
            $mentor = $marin->complementsFcm?->mentors->first();

            QlicksenseExport::create([
                'marin_uuid'         => $marin->uuid ?? '',
                'grade'              => $marin->grade?->libelle_court ?? '',
                'nom'                => $marin->nom ?? '',
                'prenom'             => $marin->prenom ?? '',
                'specialite'         => $marin->specialite?->libelle_long ?? '',
                'affectation'        => $marin->unite?->libelle_long ?? '',
                'cohorte'            => $marin->complementsFcm?->cohorte?->libelle_long ?? '',
                'parcours'           => $marin->complementsFcm?->donnees_du_parcours?->parcours_serialise?->libelle_court ?? 'Non assigné',
                'gel_fcm'            => $gelFcm ? Carbon::parse($gelFcm)->format('d/m/Y') : 'Non renseigné',
                'vert_ppa_depuis_le' => $vertPpa ? Carbon::parse($vertPpa)->format('d/m/Y') : 'Non renseigné',
                'temps_dans_le_ppa'  => $tempsPpa,
                'taux_global'        => $tauxGlobal,
                'taux_stage'         => $tauxStage,
                'taux_activite'      => $tauxActivite,
                'stage_manquants'    => $stagesManquants,
                'mentor_uuid'        => $mentor?->uuid ?? '',
                'mentor_grade'       => $mentor?->grade?->libelle_court ?? '',
                'mentor_nom'         => $mentor?->nom ?? '',
                'mentor_prenom'      => $mentor?->prenom ?? '',
                'mentor_unite'       => $mentor?->unite?->libelle_long ?? '',
                'mentor_email'       => $mentor?->email ?? '',
            ]);

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
        $this->info("Export terminé : {$marins->count()} marins exportés dans fcmcentral_qlicksense_exports");

        return self::SUCCESS;
    }
}


