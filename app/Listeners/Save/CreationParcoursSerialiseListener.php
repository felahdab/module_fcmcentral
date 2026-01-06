<?php

namespace Modules\FcmCentral\Listeners\Save;

use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use Modules\FcmCommun\Enums\Constants;
use Modules\FcmCommun\Traits\LoadsModelsDynamically;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\FcmCentral\Helpers\OutilsHelper;
use Modules\FcmCommun\Jobs\MajPpaMarinfcm;

class CreationParcoursSerialiseListener
{
    use LoadsModelsDynamically;

    /**
     * Gere l'evenement.
     */
    public function handle(UserGeneratedEvent $event): void
    {
        if (! $event->isOfType(UserGeneratedEventTypes::CREATION_PARCOURS_SERIALISE)) {
            return;
        }

        try {
            $parcours = Parcours::where('uuid', $event->object_uuid)->first();

            if (! $parcours) {
                Log::error("Parcours non trouvé avec UUID: {$event->object_uuid}");
                return;
            }

            $data = $event->detail;
            $date = $data['date_de_debut'] ?? now()->toDateString();
            $version = $data['version'] ?? $parcours->getLatestSerializedVersion() + 1;

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
                'affectations.competences.competenceSavoirfaires.objectifs' // si objectifs relation existe
            ]);

            // Construire la structure sérialisée
            $serializedData = $this->buildCompleteParcoursData($parcours);

            $totalObjectifs = $this->countObjectifsInSerializedData($serializedData);
            Log::info("Total objectifs sérialisés: {$totalObjectifs}");

            // Enregistrer la sérialisation
            $nouveauParcoursSerialise=ParcoursSerialise::create([
                'parcours_id'   => $parcours->id,
                'parcours'      => $serializedData,
                'date_debut'    => $date,
                'libelle_court' => OutilsHelper::sanitizeForAlpine($parcours->libelle_court),
                'libelle_long'  => OutilsHelper::sanitizeForAlpine($parcours->libelle_long),
                'version'       => $version,
            ]);

            Log::info("Parcours {$parcours->libelle_court} sérialisé (version={$version}) avec {$totalObjectifs} objectifs");


            // Partie si ancienne version existe (mettre une date de fin au parcours precedent +  recloner les parcours existant avec les datas anciennes)
            $ancienneVersion = ParcoursSerialise::where('parcours_id', $parcours->id)
            //->whereNull('date_fin')
            ->where('version','<',$version)
            ->orderByDesc('version')
            ->first();

            Log::info('RECH ANCIENNE VERSION', [
                'parcours_id' => $parcours->id,
                'type' => $ancienneVersion ? $ancienneVersion->version :  'AUCUNE',
                'New version' => $version,
            ]);

            if ($ancienneVersion) {
                $versionN1 = $ancienneVersion->version;
                $marinParcoursList = \Modules\FcmCentral\Models\MarinParcours::where('parcoursserialise_id', $ancienneVersion->id)->get();
                
                Log::info('DEBUT MODIFICATION', [
                    
                    'nb_marins' => $marinParcoursList->count(),
                    'Ancienne version' => $versionN1,
                ]);

                foreach ($marinParcoursList as $marinParcours) {
                    $marinUuid = $marinParcours->marin?->uuid ?? $marinParcours->fcmMarin?->marin?->uuid ?? null;

                    
                    $parcoursMarinJson = is_string($marinParcours->parcoursmarin)
                        ? json_decode($marinParcours->parcoursmarin, true)
                        : $marinParcours->parcoursmarin;

                       
                        Log::info('AVANT BACKUP', [
                            'marinuuid' => $marinUuid,
                            'type' => gettype($parcoursMarinJson),
                            'version' => $versionN1,
                        ]);
                    \Modules\FcmCommun\Services\ParcoursMarinBackupService::backup(
                        $marinUuid,
                        $parcoursMarinJson,
                        $versionN1,
                        'FcmCentral',
                        now()->format('Y-m-d_His')
                    );
                    Log::info('FIN BACKUP');
                }
            
                $ancienneVersion->date_fin = $date;
                $ancienneVersion->save();
            
                if ($marinParcoursList->isNotEmpty()) {
                    Log::info('AVANT DISPATCH JOB', [
                        'uuid' => $nouveauParcoursSerialise->uuid
                    ]);
                    MajPpaMarinfcm::dispatchSync($nouveauParcoursSerialise->uuid);
                    Log::info('APRES DISPATCH JOB');
                }
            }
            
            // Fin partie ancienne

            $notification = Constants::getMess(Constants::PARCOURS, $parcours->libelle_court);

            Notification::make()
                ->title($notification['title'] ?? 'Parcours sérialisé')
                ->success()
                ->body($notification['message'] ?? 'Sérialisation terminée')
                ->send();

                
        } catch (\Exception $e) {
            Log::error("Erreur lors de la sérialisation du parcours", [
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
     * Construit la structure complète du parcours (affectations -> competences -> competenceSavoirfaires -> objectifs)
     */
    private function buildCompleteParcoursData(Parcours $parcours): array
    {
        $result = [
            'id' => $parcours->id,
            'uuid' => $parcours->uuid,
            'code' => $parcours->code,
            'libelle_court' => OutilsHelper::sanitizeForAlpine($parcours->libelle_court),
            'libelle_long' => OutilsHelper::sanitizeForAlpine($parcours->libelle_long),
            'parcours_type_id' => $parcours->parcours_type_id,
            'color' => $parcours->color,
            'affectations' => [],
        ];

        foreach ($parcours->affectations as $aff) {
            $affData = [
                'id' => $aff->id,
                'uuid' => $aff->uuid,
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
                    'id' => $competence->id,
                    'uuid' => $competence->uuid,
                    'code' => $competence->code,
                    'libelle_court' => OutilsHelper::sanitizeForAlpine($competence->libelle_court),
                    'libelle_long' => OutilsHelper::sanitizeForAlpine($competence->libelle_long),
                    'color' => $competence->color,
                    'ordre' => $competence->ordre,
                    'competenceSavoirfaires' => [],
                ];

                // liaisons competence -> savoirfaire (table competenceSavoirfaires)
                $csfs = $competence->competenceSavoirfaires->sortBy('ordre');
                foreach ($csfs as $csf) {
                    $csfData = [
                        'id' => $csf->id,
                        'uuid' => $csf->uuid ?? null,
                        'ordre' => $csf->ordre ?? null,
                        'heures' => $csf->heures ?? null,
                        'jours' => $csf->jours ?? null,
                        'libelle_court' => OutilsHelper::sanitizeForAlpine($csf->libelle_court) ?? null,
                        'savoirfaire' => null,
                        'savoirfaire_type' => $csf->savoirfaireType ? [
                            'id' => $csf->savoirfaireType->id,
                            'code' => $csf->savoirfaireType->code ?? null,
                            'libelle_court' => OutilsHelper::sanitizeForAlpine($csf->savoirfaireType->libelle_court) ?? null,
                        ] : null,
                        'objectifs' => [],
                    ];

                    if ($csf->savoirfaire) {
                        $sf = $csf->savoirfaire;
                        $csfData['savoirfaire'] = [
                            'id' => $sf->id,
                            'uuid' => $sf->uuid,
                            'code' => $sf->code,
                            'libelle_court' => OutilsHelper::sanitizeForAlpine($sf->libelle_court),
                            'libelle_long' => OutilsHelper::sanitizeForAlpine($sf->libelle_long),
                            'competence_id' => $sf->competence_id,
                            'color' => (($sf->color<>'')?$sf->color:$competence->color),
                        ];
                    }
                    $color_ob = (($sf->color<>'')?$sf->color:$competence->color);
                    // objectifs (si relation chargée)
                    if ($csf->relationLoaded('objectifs')) {
                        $objs = $csf->objectifs->sortBy('ordre')->map(function ($o,$color_ob) {
                            return [
                                'id' => $o->id,
                                'uuid' => $o->uuid,
                                'libelle_court' => OutilsHelper::sanitizeForAlpine($o->libelle_court),
                                'description' => OutilsHelper::sanitizeForAlpine($o->description) ?? null,
                                'ordre' => $o->ordre ?? null,
                                'code' => $o->code,
                                
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
