<?php

namespace Modules\FcmCentral\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\QlicksenseExport;
use Modules\FcmCommun\Services\CalculHeuresParcoursService;

class RefreshQlicksenseExportCommand extends Command
{
    protected $signature = 'fcmcentral:refresh-qlicksense';
    
    protected $description = 'Rafraîchit la table fcmcentral_qlicksense_exports avec les données marins FCM pour QlikSense';

    public function handle(): int
    {
        $this->info('🔄 Début du rafraîchissement de la table QlikSense...');

        // Vider la table
        QlicksenseExport::query()->truncate();
        $this->info('✓ Table vidée');

        // Charger tous les marins avec relations
        $marins = Marin::with([
            'grade',
            'specialite',
            'unite',
            'complementsFcm.cohorte',
            'complementsFcm.mentor.grade',
            'complementsFcm.mentor.unite',
            'etats.typeEtat',
        ])->get();

        $this->info("📊 {$marins->count()} marins à traiter...");

        $progressBar = $this->output->createProgressBar($marins->count());
        $progressBar->start();

        foreach ($marins as $marin) {
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
            $tempsPpa = $dateDebut ? Carbon::parse($dateDebut)->diffInDays(now()) : 0;

            // PROGRESSION
            $parcours = $marin->complementsFcm?->donnees_du_parcours?->parcoursmarin;
            $progression = null;
            if ($parcours) {
                try {
                    $progression = (new CalculHeuresParcoursService())->calculerPourcentageEtatValid($parcours);
                } catch (\Exception $e) {
                    $this->warn("Erreur calcul progression pour {$marin->nom}: {$e->getMessage()}");
                }
            }

            // STAGES MANQUANTS
            $stagesManquants = 0;
            if ($parcours) {
                foreach ($parcours['affectations'] ?? [] as $affectation) {
                    foreach ($affectation['competences'] ?? [] as $competence) {
                        foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                            if (($csf['savoirfaire_type']['code'] ?? null) === 'S' && empty($csf['etat_valid'])) {
                                ++$stagesManquants;
                            }
                        }
                    }
                }
            }

            // Insérer dans la table
            QlicksenseExport::create([
                'marin_uuid' => $marin->uuid ?? '',
                'grade' => $marin->grade?->libelle_court ?? '',
                'nom' => $marin->nom ?? '',
                'prenom' => $marin->prenom ?? '',
                'specialite' => $marin->specialite?->libelle_long ?? '',
                'affectation' => $marin->unite?->libelle_long ?? '',
                'cohorte' => $marin->complementsFcm?->cohorte?->libelle_long ?? '',
                'parcours' => $marin->complementsFcm?->donnees_du_parcours?->parcours_serialise?->libelle_court ?? 'Non assigné',
                'gel_fcm' => $gelFcm ? Carbon::parse($gelFcm)->format('d/m/Y') : 'Non renseigné',
                'vert_ppa_depuis_le' => $vertPpa ? Carbon::parse($vertPpa)->format('d/m/Y') : 'Non renseigné',
                'temps_dans_le_ppa' => $tempsPpa,
                'progression_globale' => $progression,
                'stage_manquants' => $stagesManquants,
                'mentor_uuid' => $marin->complementsFcm?->mentor?->uuid ?? '',
                'mentor_grade' => $marin->complementsFcm?->mentor?->grade?->libelle_court ?? '',
                'mentor_nom' => $marin->complementsFcm?->mentor?->nom ?? '',
                'mentor_prenom' => $marin->complementsFcm?->mentor?->prenom ?? '',
                'mentor_unite' => $marin->complementsFcm?->mentor?->unite?->libelle_long ?? '',
                'mentor_email' => $marin->complementsFcm?->mentor?->email ?? '',
            ]);

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
        $this->info("✅ Export terminé : {$marins->count()} marins exportés dans fcmcentral_qlicksense_exports");

        return self::SUCCESS;
    }
}
