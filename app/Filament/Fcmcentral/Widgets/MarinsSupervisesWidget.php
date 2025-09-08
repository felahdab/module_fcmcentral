<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\FcmMarin;
use Modules\FcmCentral\Traits\FcmAccessControl;

class MarinsSupervisesWidget extends BaseWidget
{
    
    use FcmAccessControl;
    protected static ?string $heading = 'Marins supervisés';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return static::canAccessMentorFeatures();
    }

    public function table(Table $table): Table
    {
        $currentMarin = Marin::fromCurrentUser();

        // Si pas de marin connecté, retourner une query vide
        if (!$currentMarin) {
            return $table->query(Marin::whereRaw('1 = 0'));
        }

        // Récupérer les IDs des marins supervisés via FcmMarin
        $marinIds = FcmMarin::where('mentor_id', $currentMarin->id)
            ->pluck('rh_marin_id')
            ->toArray();

        return $table
            ->query(
                // Utiliser la vraie relation au lieu de l'attribut
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
                    
                    Tables\Columns\TextColumn::make('progression')
    ->label('Progression')
    ->state(function (Marin $record): string {
        $progression = $this->calculateProgression($record);
        return $progression['completed'] . '/' . $progression['total'] . ' (' . $progression['progress'] . '%)';
    })
    ->badge()
    ->color(function (Marin $record): string {
        $progression = $this->calculateProgression($record);
        $progress = $progression['progress'];
        
        if ($progress >= 80) return 'success';
        if ($progress >= 50) return 'warning';
        return 'danger';
    }),

                
                    
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
                    ->url(fn (Marin $record): string => 
                    \Modules\FcmCentral\Filament\Fcmcentral\Pages\LivretMentorConsultation::getUrl(['marinId' => $record->id])
                    )
                    ->openUrlInNewTab(),
            
                Tables\Actions\Action::make('valider_activites')
                    ->label('Valider')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->visible(fn (Marin $record): bool => $this->calculateEnAttente($record) > 0)
                    ->url(fn (Marin $record): string => 
                    \Modules\FcmCentral\Filament\Fcmcentral\Pages\LivretMentorConsultation::getUrl(['marinId' => $record->id])
                    )
                    ->openUrlInNewTab(),
            ])
            
            ->defaultSort('nom')
            ->striped()
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(10);
    }

    private function calculateProgression(Marin $marin): array
    {
        // Utiliser l'attribut complements_fcm (qui fonctionne car c'est un getter)
        if (!$marin->complements_fcm) {
            return ['progress' => 0, 'completed' => 0, 'total' => 0];
        }

        $parcours = $marin->complements_fcm->donnees_du_parcours->parcoursmarin ?? null;
        if (!$parcours) {
            return ['progress' => 0, 'completed' => 0, 'total' => 0];
        }

        $totalActivites = 0;
        $activitesValidees = 0;
        
        foreach ($parcours['fonctions'] ?? [] as $fonction) {
            foreach ($fonction['competences'] ?? [] as $competence) {
                foreach ($competence['savoirfaires'] ?? [] as $savoirfaire) {
                    foreach ($savoirfaire['activites'] ?? [] as $activite) {
                        $totalActivites++;
                        if (($activite['etat_valid'] ?? false) === true) {
                            $activitesValidees++;
                        }
                    }
                }
            }
        }

        $progress = $totalActivites > 0 ? round(($activitesValidees / $totalActivites) * 100) : 0;

        return [
            'progress' => $progress,
            'completed' => $activitesValidees,
            'total' => $totalActivites
        ];
    }

    private function calculateEnAttente(Marin $marin): int
{
    if (!$marin->complements_fcm) return 0;

    $parcours = $marin->complements_fcm->donnees_du_parcours->parcoursmarin ?? null;
    if (!$parcours) return 0;

    $enAttente = 0;
    
    foreach ($parcours['fonctions'] ?? [] as $fonction) {
        foreach ($fonction['competences'] ?? [] as $competence) {
            foreach ($competence['savoirfaires'] ?? [] as $savoirfaire) {
                foreach ($savoirfaire['activites'] ?? [] as $activite) {
                    // Activité proposée mais pas encore validée par le mentor
                    $estProposee = !empty($activite['date_proposition']);
                    $estValidee = ($activite['etat_valid'] ?? false) === true;
                    $aDateValidation = !empty($activite['date_validation']);
                    
                    // En attente = proposée par le marin mais pas encore validée par le mentor
                    if ($estProposee && !$estValidee && !$aDateValidation) {
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
        if (!$marin->complements_fcm) return null;

        $parcours = $marin->complements_fcm->donnees_du_parcours->parcoursmarin ?? null;
        if (!$parcours) return null;

        $derniereDate = null;
        
        foreach ($parcours['fonctions'] ?? [] as $fonction) {
            foreach ($fonction['competences'] ?? [] as $competence) {
                foreach ($competence['savoirfaires'] ?? [] as $savoirfaire) {
                    foreach ($savoirfaire['activites'] ?? [] as $activite) {
                        if (($activite['etat_valid'] ?? false) === true && !empty($activite['date_validation'])) {
                            $dateValidation = \Carbon\Carbon::parse($activite['date_validation']);
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
