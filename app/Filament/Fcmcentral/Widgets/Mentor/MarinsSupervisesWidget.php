<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Widgets\Mentor;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\FcmMarin;
use Illuminate\Support\Facades\Log;

class MarinsSupervisesWidget extends BaseWidget
{
    protected static ?string $heading = 'Marins supervisés';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        // Ce widget ne doit être visible que si l'utilisateur est un mentor.
        $currentMarin = Marin::fromCurrentUser();
        if ( $currentMarin == null ) return false;

        return $currentMarin->est_mentor ;
    }

    public function table(Table $table): Table
    {
        $currentMarin = Marin::fromCurrentUser();

        if (!$currentMarin) {
            return $table->query(Marin::whereRaw('1 = 0'));
        }

        $marinIds = FcmMarin::where('mentor_id', $currentMarin->id)
            ->pluck('rh_marin_id')
            ->toArray();

        return $table
            ->query(
                Marin::whereIn('id', $marinIds)->with('lignes_dans_fcm_marin')
            )
            ->columns([
                Tables\Columns\TextColumn::make('nom')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('grade.libelle_long')
                    ->label('Grade')
                    ->badge(),

                Tables\Columns\TextColumn::make('cohorte')
                    ->label('Cohorte')
                    ->state(function (Marin $record): ?string {
                        $fcmMarin = $record->lignes_dans_fcm_marin->first();
                        return $fcmMarin?->cohorte?->libelle_long ?? 'Non assigné';
                    })
                    ->badge()
                    ->color('info'),

                    Tables\Columns\TextColumn::make('complements_fcm.donnees_du_parcours.taux_global')
                    ->label('Taux')
                    ->sortable()
                    ->suffix('%')
                    ->formatStateUsing(fn ($state) => intval(round($state)))
                    ->placeholder('0'),

                Tables\Columns\TextColumn::make('activites_en_attente')
                    ->label('En attente')
                    ->state(function (Marin $record): int {
                        return $this->calculateEnAttente($record);
                    })
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'warning' : 'success'),

                Tables\Columns\TextColumn::make('derniere_validation')
                    ->label('Dernière validation')
                    ->state(function (Marin $record): ?string {
                        return $this->getDerniereValidation($record);
                    })
                    ->since()
                    ->placeholder('Aucune validation'),
            ])
            ->actions([
                Tables\Actions\Action::make('voir_parcours')
                    ->label('Voir parcours')
                    ->icon('heroicon-m-eye')
                    // ->url(fn (Marin $record): string =>
                    //     $this->hasParcours($record)
                    //         ? \Modules\FcmCentral\Filament\Fcmcentral\Pages\MonLivretDeFcm::getUrl(['marinId' => $record->id])
                    //         : '#'
                    // )
                    ->url(fn ($record) =>
                        route('filament.FCM Central.resources.marins.livret-de-fcm',
                        ['record' =>$record])
                    )
                    //->openUrlInNewTab()
                    ->visible(fn (Marin $record) => $this->hasParcours($record)),

                // Tables\Actions\Action::make('valider_activites')
                //     ->label('Valider')
                //     ->icon('heroicon-m-check-circle')
                //     ->color('success')
                //     ->visible(fn (Marin $record): bool =>
                //         $this->calculateEnAttente($record) > 0 && $this->hasParcours($record)
                //     )
                //     ->url(fn (Marin $record): string =>
                //         \Modules\FcmCentral\Filament\Fcmcentral\Pages\MonLivretDeFcm::getUrl(['marinId' => $record->id])
                //     )
                //     ->openUrlInNewTab(),
            ])
            ->defaultSort('nom')
            ->striped()
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(10);
    }

    private function hasParcours(Marin $marin): bool
    {
        return $marin->complements_fcm
            && $marin->complements_fcm->donnees_du_parcours
            && !empty($marin->complements_fcm->donnees_du_parcours->parcoursmarin['affectations'] ?? []);
    }

    private function calculateProgression(Marin $marin): array
    {
        if (!$this->hasParcours($marin)) {
            return ['progress' => 0, 'completed' => 0, 'total' => 0];
        }

        $parcours = $marin->complements_fcm->donnees_du_parcours->parcoursmarin;
        $total = 0;
        $completed = 0;

        foreach ($parcours['affectations'] ?? [] as $affectation) {
            foreach ($affectation['competences'] ?? [] as $competence) {
                // ✅ Essayer les deux variantes de clés
                $savoirfaires = $competence['competenceSavoirfaires'] 
                    ?? $competence['competencesSavoirfaires'] 
                    ?? [];

                foreach ($savoirfaires as $savoirfaire) {
                    // ✅ Compter aussi les savoir-faire
                    $total++;
                    if (($savoirfaire['etat_valid'] ?? false) === true) {
                        $completed++;
                    }

                    // ✅ Compter les objectifs
                    foreach ($savoirfaire['objectifs'] ?? [] as $objectif) {
                        $total++;
                        if (($objectif['etat_valid'] ?? false) === true) {
                            $completed++;
                        }
                    }
                }
            }
        }

        $progress = $total > 0 ? round(($completed / $total) * 100) : 0;
        
        // ✅ Debug
        Log::info("Progression {$marin->nom}: total={$total}, completed={$completed}, progress={$progress}%");

        return [
            'progress' => $progress,
            'completed' => $completed,
            'total' => $total
        ];
    }

    private function calculateEnAttente(Marin $marin): int
    {
        if (!$this->hasParcours($marin)) return 0;

        $parcours = $marin->complements_fcm->donnees_du_parcours->parcoursmarin;
        $enAttente = 0;

        foreach ($parcours['affectations'] ?? [] as $affectation) {
            foreach ($affectation['competences'] ?? [] as $competence) {
                $savoirfaires = $competence['competenceSavoirfaires'] 
                    ?? $competence['competencesSavoirfaires'] 
                    ?? [];

                foreach ($savoirfaires as $savoirfaire) {
                    // ✅ Vérifier aussi le savoir-faire lui-même
                    $estProposee = !empty($savoirfaire['date_proposition']);
                    $estValidee = ($savoirfaire['etat_valid'] ?? false) === true;
                    if ($estProposee && !$estValidee) {
                        $enAttente++;
                    }

                    // ✅ Vérifier les objectifs
                    foreach ($savoirfaire['objectifs'] ?? [] as $objectif) {
                        $estProposee = !empty($objectif['date_proposition']);
                        $estValidee = ($objectif['etat_valid'] ?? false) === true;
                        if ($estProposee && !$estValidee) {
                            $enAttente++;
                        }
                    }
                }
            }
        }

        return $enAttente;
    }

    private function getDerniereValidation(Marin $marin): ?string
    {
        if (!$this->hasParcours($marin)) return null;

        $parcours = $marin->complements_fcm->donnees_du_parcours->parcoursmarin;
        $derniereDate = null;

        foreach ($parcours['affectations'] ?? [] as $affectation) {
            foreach ($affectation['competences'] ?? [] as $competence) {
                $savoirfaires = $competence['competenceSavoirfaires'] 
                    ?? $competence['competencesSavoirfaires'] 
                    ?? [];

                foreach ($savoirfaires as $savoirfaire) {
                    // ✅ Vérifier la date de validation du savoir-faire
                    if (($savoirfaire['etat_valid'] ?? false) === true && !empty($savoirfaire['date_validation'])) {
                        $dateValidation = \Carbon\Carbon::parse($savoirfaire['date_validation']);
                        if (!$derniereDate || $dateValidation->gt($derniereDate)) {
                            $derniereDate = $dateValidation;
                        }
                    }

                    // ✅ Vérifier les objectifs
                    foreach ($savoirfaire['objectifs'] ?? [] as $objectif) {
                        if (($objectif['etat_valid'] ?? false) === true && !empty($objectif['date_validation'])) {
                            $dateValidation = \Carbon\Carbon::parse($objectif['date_validation']);
                            if (!$derniereDate || $dateValidation->gt($derniereDate)) {
                                $derniereDate = $dateValidation;
                            }
                        }
                    }
                }
            }
        }

        return $derniereDate?->toISOString();
    }
}
