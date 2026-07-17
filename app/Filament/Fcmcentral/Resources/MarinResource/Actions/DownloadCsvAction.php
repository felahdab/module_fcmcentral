<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Actions;

use Carbon\Carbon;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Response;
use Modules\FcmCommun\Services\CalculHeuresParcoursService;

class DownloadCsvAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name)
            ->label('Exporter les marins')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('primary')
            ->action(function ($table) {
                $livewire = $table->getLivewire();
                $marins = $livewire->getFilteredTableQuery()
                    ->with([
                        'grade',
                        'specialite',
                        'unite',
                        'etats',
                        'complementsFcm.cohorte',
                        'complementsFcm.mentors.grade',
                        'complementsFcm.mentors.unite',
                    ])->get();

                $csvHeader = [
                    'UUID', 'GRADE', 'NOM', 'PRENOM', 'SPECIALITE', 'AFFECTATION', 'COHORTE',
                    'JALON', 'PARCOURS', 'GEL FCM', 'VERT PPA DEPUIS LE', 'TEMPS DANS LE PPA',
                    'PROGRESSION GLOBALE', 'STAGES MANQUANTS', 'EN ATTENTE',
                    'MENTOR_UUID', 'MENTOR_GRADE', 'MENTOR_NOM', 'MENTOR_PRENOM', 'MENTOR_UNITE', 'MENTOR_EMAIL',
                ];

                $callback = function () use ($csvHeader, $marins) {
                    $file = fopen('php://output', 'w');
                    fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                    fputcsv($file, $csvHeader, ';');

                    foreach ($marins as $marin) {
                        // GEL actif = etat sans date_fin
                        $gelActif = $marin->etats->whereNull('date_fin')->first();
                        $gelFcm = $gelActif?->date_debut;

                        // VERT PPA
                        $vertPpa = $marin->etats
                            ->filter(fn($e) => ($e->typeEtat->code ?? null) === 'FCM_ACT')
                            ->sortBy('date_debut')
                            ->first()?->date_debut;

                        // TEMPS PPA
                        $dateDebut = $marin->etats->sortBy('date_debut')->first()?->date_debut;
                        $tempsPpa = $dateDebut ? round(Carbon::parse($dateDebut)->diffInDays(now()), 0) : 0;

                        // PROGRESSION & STAGES MANQUANTS & EN ATTENTE
                        $parcoursmarin = $marin->complementsFcm?->donnees_du_parcours?->parcoursmarin;
                        $progression = null;
                        $stagesManquants = 0;
                        $enAttente = 0;

                        if ($parcoursmarin) {
                            $progression = (new CalculHeuresParcoursService())->calculerPourcentageEtatValid($parcoursmarin);
                            foreach ($parcoursmarin['affectations'] ?? [] as $affectation) {
                                foreach ($affectation['competences'] ?? [] as $competence) {
                                    foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                                        if (($csf['savoirfaire_type']['code'] ?? null) === 'S' && empty($csf['etat_valid'])) {
                                            ++$stagesManquants;
                                        }
                                        if (!empty($csf['date_proposition']) && empty($csf['etat_valid'])) {
                                            ++$enAttente;
                                        }
                                        foreach ($csf['objectifs'] ?? [] as $objectif) {
                                            if (!empty($objectif['date_proposition']) && empty($objectif['etat_valid'])) {
                                                ++$enAttente;
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        // MENTOR (premier de la liste)
                        $mentor = $marin->complementsFcm?->mentors->first();

                        fputcsv($file, [
                            $marin->uuid ?? '',
                            $marin->grade?->libelle_court ?? '',
                            $marin->nom ?? '',
                            $marin->prenom ?? '',
                            $marin->specialite?->libelle_long ?? '',
                            $marin->unite?->libelle_long ?? '',
                            $marin->complementsFcm?->cohorte?->libelle_long ?? '',
                            $marin->complementsFcm?->getJalonFcmLibelle() ?? '',
                            $marin->complementsFcm?->donnees_du_parcours?->parcours_serialise?->libelle_court ?? 'Non assigné',
                            $gelFcm ? Carbon::parse($gelFcm)->format('d/m/Y') : '',
                            $vertPpa ? Carbon::parse($vertPpa)->format('d/m/Y') : '',
                            $tempsPpa,
                            $progression ?? '',
                            $stagesManquants,
                            $enAttente,
                            $mentor?->uuid ?? '',
                            $mentor?->grade?->libelle_court ?? '',
                            $mentor?->nom ?? '',
                            $mentor?->prenom ?? '',
                            $mentor?->unite?->libelle_long ?? '',
                            $mentor?->email ?? '',
                        ], ';');
                    }
                    fclose($file);
                };

                return Response::stream($callback, 200, [
                    'Content-Type' => 'text/csv; charset=UTF-8',
                    'Content-Disposition' => 'attachment; filename=marins-fcm.csv',
                ]);
            });
    }
}
