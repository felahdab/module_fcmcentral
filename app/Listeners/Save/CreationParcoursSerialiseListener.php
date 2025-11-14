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
            ParcoursSerialise::create([
                'parcours_id'   => $parcours->id,
                'parcours'      => $serializedData,
                'date_debut'    => $date,
                'libelle_court' => $parcours->libelle_court,
                'libelle_long'  => $parcours->libelle_long,
                'version'       => $version,
            ]);

            Log::info("Parcours {$parcours->libelle_court} sérialisé (version={$version}) avec {$totalObjectifs} objectifs");

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
            'libelle_court' => $parcours->libelle_court,
            'libelle_long' => $parcours->libelle_long,
            'parcours_type_id' => $parcours->parcours_type_id,
            'color' => $parcours->color,
            'affectations' => [],
        ];

        foreach ($parcours->affectations as $aff) {
            $affData = [
                'id' => $aff->id,
                'uuid' => $aff->uuid,
                'libelle_court' => $aff->libelle_court,
                'libelle_long' => $aff->libelle_long,
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
                    'libelle_court' => $competence->libelle_court,
                    'libelle_long' => $competence->libelle_long,
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
                        'libelle_court' => $csf->libelle_court ?? null,
                        'savoirfaire' => null,
                        'savoirfaire_type' => $csf->savoirfaireType ? [
                            'id' => $csf->savoirfaireType->id,
                            'code' => $csf->savoirfaireType->code ?? null,
                            'libelle_court' => $csf->savoirfaireType->libelle_court ?? null,
                        ] : null,
                        'objectifs' => [],
                    ];

                    if ($csf->savoirfaire) {
                        $sf = $csf->savoirfaire;
                        $csfData['savoirfaire'] = [
                            'id' => $sf->id,
                            'uuid' => $sf->uuid,
                            'code' => $sf->code,
                            'libelle_court' => $sf->libelle_court,
                            'libelle_long' => $sf->libelle_long,
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
                                'libelle_court' => $o->libelle_court,
                                'description' => $o->description ?? null,
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
