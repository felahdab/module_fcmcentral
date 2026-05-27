<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Actions;

use Carbon\Carbon;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Response;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCommun\Services\CalculHeuresParcoursService;

class DownloadCsvAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name)
            ->label('Exporter les marins')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('primary')
            ->action(function () {
                $marins = Marin::with([
                    'grade',
                    'specialite',
                    'unite',
                    'complementsFcm.cohorte',
                    'etats.typeEtat',
                ])->get();

                $csvHeader = [
                    'UUID', 'GRADE', 'NOM', 'PRENOM', 'SPECIALITE', 'AFFECTATION', 'COHORTE',
                    'PARCOURS','GEL FCM', 'VERT PPA DEPUIS LE', 'TEMPS DANS LE PPA',
                    'PROGRESSION GLOBALE', 'STAGE MANQUANTS',
                    'MENTOR_UUID','MENTOR_GRADE','MENTOR_NOM','MENTOR_PRENOM','MENTOR_UNITE','MENTOR_EMAIL'
                ];

                $callback = function () use ($csvHeader, $marins) {
                    $file = fopen('php://output', 'w');
                    fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
                    fputcsv($file, $csvHeader, ';');

                    foreach ($marins as $marin) {
                        // GEL FCM
                        $gelFcm = $marin->etats()
                            ->whereHas('typeEtat', fn ($q) => $q->where('code', 'FCM_ENP'))
                            ->orderBy('date_debut')
                            ->first()?->date_debut
                        ;

                        // VERT PPA
                        $vertPpa = $marin->etats()
                            ->whereHas('typeEtat', fn ($q) => $q->where('code', 'FCM_ACT'))
                            ->orderBy('date_debut')
                            ->first()?->date_debut
                        ;

                        // TEMPS PPA
                        $dateDebut = $marin->etats()->orderBy('date_debut')->first()?->date_debut;
                        $tempsPpa = $dateDebut ? Carbon::parse($dateDebut)->diffInDays(now()) : '0';
                        $tempsPpa = round($tempsPpa, 0);

                        // PROGRESSION
                        $parcours = $marin->complementsFcm?->donnees_du_parcours?->parcoursmarin;
                        $progression = $parcours ? (new CalculHeuresParcoursService())->calculerPourcentageEtatValid($parcours) : null;

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

                        fputcsv($file, [
                            $marin->uuid ?? '',
                            $marin->grade?->libelle_court ?? '',
                            $marin->nom ?? '',
                            $marin->prenom ?? '',
                            $marin->specialite?->libelle_long ?? '',
                            $marin->unite?->libelle_long ?? '',
                            $marin->complementsFcm?->cohorte?->libelle_long ?? '',
                            $marin->complementsFcm?->donnees_du_parcours?->parcours_serialise?->libelle_court ?? 'Non assigné',
                            $gelFcm ? Carbon::parse($gelFcm)->format('d/m/Y') : 'Non renseigné',
                            $vertPpa ? Carbon::parse($vertPpa)->format('d/m/Y') : 'Non renseigné',
                            $tempsPpa ?? '',
                            $progression ?? '',
                            $stagesManquants,
                            $marin->complementsFcm?->mentor?->uuid ?? '',
                            $marin->complementsFcm?->mentor?->grade?->libelle_court ?? '',
                            $marin->complementsFcm?->mentor?->nom ?? '',
                            $marin->complementsFcm?->mentor?->prenom ?? '',
                            $marin->complementsFcm?->mentor?->unite?->libelle_long ?? '',
                            $marin->complementsFcm?->mentor?->email ?? '',
                        ], ';');
                    }
                    fclose($file);
                };

                return Response::stream($callback, 200, [
                    'Content-Type' => 'text/csv; charset=UTF-8',
                    'Content-Disposition' => 'attachment; filename=marins-fcm.csv',
                ]);
            })
        ;
    }
}
