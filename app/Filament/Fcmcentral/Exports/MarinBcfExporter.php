<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Exports;

use Carbon\Carbon;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCommun\Services\CalculHeuresParcoursService;

class MarinBcfExporter extends Exporter
{
    protected static ?string $model = Marin::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('uuid')->label('UUID'),
            ExportColumn::make('grade.libelle_court')->label('GRADE'),
            ExportColumn::make('nom')->label('NOM'),
            ExportColumn::make('prenom')->label('PRENOM'),
            ExportColumn::make('specialite.libelle_long')->label('SPECIALITE'),
            // ExportColumn::make('complementsFcm.dette_formation')->label('DETTE DE FORMATION'),
            ExportColumn::make('unite.libelle_long')->label('AFFECTATION'),
            // ExportColumn::make('complementsFcm.dfa')->label('DFA'),
            ExportColumn::make('complementsFcm.cohorte.libelle_long')->label('COHORTE'),

            // GEL FCM : date du premier état FCM_ENP
            ExportColumn::make('gel_fcm')->label('GEL FCM')
                ->state(function (Marin $marin) {
                    $date = $marin->etats()
                        ->whereHas('typeEtat', fn ($q) => $q->where('code', 'FCM_ENP'))
                        ->orderBy('date_debut')
                        ->first()?->date_debut
                    ;

                    return $date ? Carbon::parse($date)->format('d/m/Y') : 'Non renseigné';
                }),

            // VERT PPA : date du premier état FCM_ACT
            ExportColumn::make('vert_ppa')->label('VERT PPA DEPUIS LE')
                ->state(function (Marin $marin) {
                    $date = $marin->etats()
                        ->whereHas('typeEtat', fn ($q) => $q->where('code', 'FCM_ACT'))
                        ->orderBy('date_debut')
                        ->first()?->date_debut
                    ;

                    return $date ? Carbon::parse($date)->format('d/m/Y') : 'Non renseigné';
                }),

            // TEMPS DANS LE PPA : nb jours depuis le premier état
            ExportColumn::make('temps_ppa')->label('TEMPS DANS LE PPA')
                ->state(function (Marin $marin) {
                    $dateDebut = $marin->etats()->orderBy('date_debut')->first()?->date_debut;

                    return $dateDebut ? Carbon::parse($dateDebut)->diffInDays(now()) : null;
                }),

            // PROGRESSION GLOBALE : via service
            ExportColumn::make('progression_globale')->label('PROGRESSION GLOBALE')
                ->state(function (Marin $marin) {
                    $parcours = $marin->complementsFcm?->donnees_du_parcours?->parcoursmarin;

                    return $parcours ? (new CalculHeuresParcoursService())->calculerPourcentageEtatValid($parcours) : null;
                }),

            // STAGES MANQUANTS : savoirfaire_type.code = S et non validé
            ExportColumn::make('stages_manquants')->label('STAGE MANQUANTS')
                ->state(function (Marin $marin) {
                    $count = 0;
                    $parcours = $marin->complementsFcm?->donnees_du_parcours?->parcoursmarin;
                    if ($parcours) {
                        foreach ($parcours['affectations'] ?? [] as $affectation) {
                            foreach ($affectation['competences'] ?? [] as $competence) {
                                foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                                    if (($csf['savoirfaire_type']['code'] ?? null) === 'S' && empty($csf['etat_valid'])) {
                                        ++$count;
                                    }
                                }
                            }
                        }
                    }

                    return $count;
                }),

            // ExportColumn::make('complementsFcm.tendance')->label('TENDANCE'),
            // ExportColumn::make('complementsFcm.selection_bs')->label('SELECTION BS'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Export des marins terminé : '.number_format($export->successful_rows).' '.str('ligne')->plural($export->successful_rows).' exportée(s).';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('ligne')->plural($failedRowsCount).' en échec.';
        }

        return $body;
    }
}
