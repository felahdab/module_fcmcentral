<?php

namespace Modules\FcmCentral\Listeners\Save;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventNotifications;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\FcmCentral\Models\MarinParcours;
use Modules\FcmCentral\Helpers\OutilsHelper;
use Modules\FcmCommun\Jobs\MajPpaMarinfcm;
use Modules\FcmCommun\Services\MigrationParcoursMarinService;

class CreationParcoursSerialiseListener
{
    
    public function __construct(
        private MigrationParcoursMarinService $migrationService
    ) {}

    
    /**
     * Gere l'evenement.
     */
    public function handle(UserGeneratedEvent $event): void
    {
        if (! $event->isOfType(UserGeneratedEventTypes::CREATION_PARCOURS_SERIALISE)) {
            return;
        }

        DB::beginTransaction();

        try {
            $parcours = Parcours::where('uuid', $event->object_uuid)->first();

            if (! $parcours) {
                Log::error("Parcours non trouvé avec UUID: {$event->object_uuid}");
                return;
            }

            $data = $event->detail;
            $date = $data['date_de_debut'] ?? now()->toDateString();
            $version = $data['version'] ?? $parcours->getLatestSerializedVersion() + 1;

            Log::info("=== DÉBUT CRÉATION PARCOURS SÉRIALISÉ ===", [
                'parcours_id' => $parcours->id,
                'parcours_code' => $parcours->code,
                'version' => $version,
            ]);

            // Charger explicitement les relations nécessaires
            $parcours->load([
                'affectations' => function ($q) {
                    $q->orderBy('fcmcentral_affectation_parcours.ordre');
                },
                'affectations.competences' => function ($q) {
                    $q->orderBy('ordre');
                },
                'affectations.competences.competenceSavoirfaires' => function ($q) {
                    $q->orderBy('ordre');
                },
                'affectations.competences.competenceSavoirfaires.savoirfaire',
                'affectations.competences.competenceSavoirfaires.savoirfaireType',
                'affectations.competences.competenceSavoirfaires.objectifs' => function ($q) {
                    $q->orderBy('ordre');
                }
            ]);

            // Construire la structure sérialisée
            $serializedData = $this->buildCompleteParcoursData($parcours);

            $totalObjectifs = $this->countObjectifsInSerializedData($serializedData);
            Log::info("Total objectifs sérialisés: {$totalObjectifs}");

            // Enregistrer la sérialisation
            $nouveauParcoursSerialise = ParcoursSerialise::create([
                'parcours_id'   => $parcours->id,
                'parcours'      => $serializedData,
                'date_debut'    => $date,
                'libelle_court' => OutilsHelper::sanitizeForAlpine($parcours->libelle_court),
                'libelle_long'  => OutilsHelper::sanitizeForAlpine($parcours->libelle_long),
                'version'       => $version,
            ]);

            Log::info("✅ Nouveau ParcoursSerialise créé", [
                'id' => $nouveauParcoursSerialise->id,
                'uuid' => $nouveauParcoursSerialise->uuid,
                'version' => $version,
                'objectifs_count' => $totalObjectifs,
            ]);

            // ✅ Récupérer TOUTES les anciennes versions de CE PARCOURS uniquement
            $anciensVersions = ParcoursSerialise::where('parcours_id', $parcours->id)
                ->where('version', '<', $version)
                ->orderBy('version')
                ->get();

            Log::info('🔍 Recherche anciennes versions', [
                'parcours_id' => $parcours->id,
                'parcours_code' => $parcours->code,
                'nb_versions_trouvees' => $anciensVersions->count(),
                'versions' => $anciensVersions->pluck('version')->toArray(),
                'nouvelle_version' => $version,
            ]);

            if ($anciensVersions->isNotEmpty()) {
                // ✅ Récupérer les IDs des anciennes versions de CE PARCOURS
                $anciennesVersionIds = $anciensVersions->pluck('id');
                
                // ✅ Récupérer SEULEMENT les MarinParcours liés à ces versions spécifiques
                $marinParcoursList = MarinParcours::whereIn('parcoursserialise_id', $anciennesVersionIds)
                    ->with(['fcmMarin.marin', 'parcours_serialise'])
                    ->get();

                Log::info('📋 MarinParcours à traiter', [
                    'parcours_id' => $parcours->id,
                    'parcours_code' => $parcours->code,
                    'nb_versions_anciennes' => $anciensVersions->count(),
                    'versions_concernees' => $anciensVersions->pluck('version')->toArray(),
                    'nb_marins_total' => $marinParcoursList->count(),
                    'ids_versions_anciennes' => $anciennesVersionIds->toArray(),
                ]);

                // ✅ Backup des parcoursmarin avant migration
                $nbBackups = 0;
                foreach ($marinParcoursList as $marinParcours) {
                    $marinUuid = $marinParcours->marin?->uuid ?? $marinParcours->fcmMarin?->marin?->uuid ?? null;

                    if (!$marinUuid) {
                        Log::warning("⚠️ UUID marin introuvable", [
                            'marin_parcours_id' => $marinParcours->id,
                        ]);
                        continue;
                    }

                    // ✅ Vérifier que le parcoursSerialise appartient bien à CE parcours
                    if ($marinParcours->parcours_serialise?->parcours_id !== $parcours->id) {
                        Log::warning("⚠️ MarinParcours ne correspond pas au parcours", [
                            'marin_parcours_id' => $marinParcours->id,
                            'parcours_id_attendu' => $parcours->id,
                            'parcours_id_trouve' => $marinParcours->parcours_serialise?->parcours_id,
                        ]);
                        continue;
                    }

                    $parcoursMarinJson = is_string($marinParcours->parcoursmarin)
                        ? json_decode($marinParcours->parcoursmarin, true)
                        : $marinParcours->parcoursmarin;

                    $versionOriginale = $marinParcours->parcours_serialise?->version ?? 'unknown';

                    Log::info('💾 Backup parcours marin', [
                        'marin_uuid' => $marinUuid,
                        'marin_parcours_id' => $marinParcours->id,
                        'version_originale' => $versionOriginale,
                    ]);

                    // try {
                    //     \Modules\FcmCommun\Services\ParcoursMarinBackupService::backup(
                    //         $marinUuid,
                    //         $parcoursMarinJson,
                    //         $versionOriginale,
                    //         'FcmCentral',
                    //         now()->format('Y-m-d_His')
                    //     );
                    //     $nbBackups++;
                    // } catch (\Exception $e) {
                    //     Log::error("❌ Erreur backup", [
                    //         'marin_uuid' => $marinUuid,
                    //         'error' => $e->getMessage(),
                    //     ]);
                    // }
                }

                Log::info("📊 Backups terminés", [
                    'nb_backups_reussis' => $nbBackups,
                    'nb_total' => $marinParcoursList->count(),
                ]);

                // ✅ Clôturer TOUTES les anciennes versions de CE PARCOURS
                foreach ($anciensVersions as $ancienneVersion) {
                    $ancienneVersion->date_fin = $date;
                    $ancienneVersion->save();
                    
                    Log::info('🔒 Version clôturée', [
                        'parcours_id' => $parcours->id,
                        'version' => $ancienneVersion->version,
                        'date_fin' => $date,
                    ]);
                }

                // ✅ Dispatch job pour migration avec le service
                if ($marinParcoursList->isNotEmpty()) {
                    if (app()->environment('testing')) {
                        // Appel direct du service en mode test
                        Log::info('🧪 Mode TEST - Migration synchrone via service');
                        $this->migrationService->migrerVersNouvelleVersion($nouveauParcoursSerialise->uuid);
                    } else {
    
                    Log::info('🚀 Dispatch job MAJ PPA (migration avec service)', [
                        'parcours_id' => $parcours->id,
                        'nouveau_parcours_serialise_uuid' => $nouveauParcoursSerialise->uuid,
                        'nb_marins' => $marinParcoursList->count(),
                    ]);
                    
                    MajPpaMarinfcm::dispatchSync($nouveauParcoursSerialise->uuid);
                    
                    Log::info('✅ Job MAJ PPA terminé');
                }
                }
            } else {
                Log::info("ℹ️ Aucune version existante pour ce parcours");
            }

            DB::commit();

            Log::info("=== ✅ FIN CRÉATION PARCOURS SÉRIALISÉ ===", [
                'parcours_id' => $parcours->id,
                'nouvelle_version' => $version,
            ]);

            $notification = UserGeneratedEventNotifications::getMess(
                UserGeneratedEventNotifications::PARCOURS,
                $parcours->libelle_court
            );

            Notification::make()
                ->title($notification['title'] ?? 'Parcours sérialisé')
                ->success()
                ->body($notification['message'] ?? "Version {$version} créée avec succès")
                ->send();

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("❌ ERREUR SÉRIALISATION PARCOURS", [
                'uuid' => $event->object_uuid,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            Notification::make()
                ->title('Erreur de sérialisation')
                ->danger()
                ->body('Impossible de sérialiser le parcours: ' . $e->getMessage())
                ->send();
        }
    }

    /**
     * Construit la structure complète du parcours
     */
    public function buildCompleteParcoursData(Parcours $parcours): array
    {
        $result = [
            //'id' => $parcours->id,
            'uuid' => $parcours->uuid,
            'code' => $parcours->code,
            'libelle_court' => OutilsHelper::sanitizeForAlpine($parcours->libelle_court),
            'libelle_long' => OutilsHelper::sanitizeForAlpine($parcours->libelle_long),
            'parcours_type_uuid' => $parcours->parcoursType->uuid,
            'color' => $parcours->color,
            'affectations' => [],
        ];

        foreach ($parcours->affectations as $aff) {
            $affData = [
                //'id' => $aff->id,
                'uuid' => $aff->uuid,
                'code' => $aff->code,
                'libelle_court' => OutilsHelper::sanitizeForAlpine($aff->libelle_court),
                'libelle_long' => OutilsHelper::sanitizeForAlpine($aff->libelle_long),
                'color' => $aff->color,
                'pivot' => [
                    'ordre' => $aff->pivot->ordre ?? null,
                ],
                'competences' => [],
            ];

            foreach ($aff->competences as $competence) {
                $compData = [
                    //'id' => $competence->id,
                    'uuid' => $competence->uuid,
                    'code' => $competence->code,
                    'libelle_court' => OutilsHelper::sanitizeForAlpine($competence->libelle_court),
                    'libelle_long' => OutilsHelper::sanitizeForAlpine($competence->libelle_long),
                    'color' => $competence->color,
                    'ordre' => $competence->ordre,
                    'competenceSavoirfaires' => [],
                ];

                $csfs = $competence->competenceSavoirfaires->sortBy('ordre');
                foreach ($csfs as $csf) {
                    $sf = $csf->savoirfaire;
                    $color_ob = ($sf && $sf->color) ? $sf->color : $competence->color;

                    $csfData = [
                       // 'id' => $csf->id,
                        'uuid' => $csf->uuid ?? null,
                        'ordre' => $csf->ordre ?? null,
                        'heures' => $csf->heures ?? null,
                        'jours' => $csf->jours ?? null,
                        'libelle_court' => OutilsHelper::sanitizeForAlpine($csf->libelle_court) ?? null,
                        'libelle_long' => OutilsHelper::sanitizeForAlpine($csf->libelle_long) ?? null,
                        'savoirfaire' => null,
                        'savoirfaire_type' => $csf->savoirfaireType ? [
                            //'id' => $csf->savoirfaireType->id,
                            'uuid' => $csf->savoirfaireType->id,
                            'code' => $csf->savoirfaireType->code ?? null,
                            'libelle_court' => OutilsHelper::sanitizeForAlpine($csf->savoirfaireType->libelle_court) ?? null,
                        ] : null,
                        'objectifs' => [],
                    ];

                    if ($sf) {
                        $csfData['savoirfaire'] = [
                            //'id' => $sf->id,
                            'uuid' => $sf->uuid,
                            'code' => $sf->code,
                            'libelle_court' => OutilsHelper::sanitizeForAlpine($sf->libelle_court),
                            'libelle_long' => OutilsHelper::sanitizeForAlpine($sf->libelle_long),
                            'competence_uuid' => $sf->competence_uuid,
                            'color' => $color_ob,
                        ];
                    }

                    if ($csf->relationLoaded('objectifs')) {
                        $objs = $csf->objectifs->sortBy('ordre')->map(function ($o) use ($color_ob) {
                            return [
                                //'id' => $o->id,
                                'uuid' => $o->uuid,
                                'libelle_court' => OutilsHelper::sanitizeForAlpine($o->libelle_court),
                                'description' => OutilsHelper::sanitizeForAlpine($o->description) ?? null,
                                'ordre' => $o->ordre ?? null,
                                'code' => $o->code,
                                'color' => $color_ob,
                            ];
                        })->values()->toArray();
                        $csfData['objectifs'] = $objs;
                    }

                    $compData['competenceSavoirfaires'][] = $csfData;
                }

                $affData['competences'][] = $compData;
            }

            $result['affectations'][] = $affData;
        }

        return $result;
    }

    /**
     * Compte le nombre d'objectifs dans la structure sérialisée.
     */
    private function countObjectifsInSerializedData(array $data): int
    {
        $count = 0;
        foreach ($data['affectations'] ?? [] as $aff) {
            foreach ($aff['competences'] ?? [] as $comp) {
                foreach ($comp['competenceSavoirfaires'] ?? [] as $csf) {
                    $count += count($csf['objectifs'] ?? []);
                }
            }
        }
        return $count;
    }
}
