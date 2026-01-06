<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\KeyValue;
use Filament\Notifications\Notification;
use Modules\FcmCentral\Models\FcmMarin;
use Modules\FcmCommun\Services\CalculHeuresParcoursService;

class MarinsRelationManager extends RelationManager
{
    protected static string $relationship = 'fcmMarins';

    public function table(Table $table): Table
    {
        return $table
        ->recordTitleAttribute('')
        ->modifyQueryUsing(fn (Builder $query) => $query->with([
            'marin',
            'marin.grade',
            'marin.specialite',
            'marin.unite'
        ]))
        ->columns([
            TextColumn::make('marin.display_name')
                ->label('Marin')
                ->searchable(['nom', 'prenom'])
                ->sortable()
                ->formatStateUsing(function ($record) {
                    $marin = $record->marin;
                    if (!$marin) return 'N/A';
                   
                    $grade = $marin->grade?->libelle_court ?? '';
                    $nom = $marin->nom ?? '';
                    $prenom = $marin->prenom ?? '';
                   
                    return trim("$grade $prenom $nom");
                }),

                TextColumn::make('pivot.taux_global')
                    ->label('Taux Global')
                    ->sortable()
                    ->suffix('%')
                    ->color(fn ($state) => $state >= 80 ? 'success' : ($state >= 50 ? 'warning' : 'danger')),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Ajouter un marin')
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->label('Sélectionner un marin FCM')
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search): array {
                                return FcmMarin::query()
                                    ->with(['marin', 'marin.grade', 'marin.unite'])
                                    ->whereHas('marin', function ($query) use ($search) {
                                        $query->where('nom', 'like', "%{$search}%")
                                              ->orWhere('prenom', 'like', "%{$search}%");
                                    })
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(function ($fcmMarin) {
                                        $marin = $fcmMarin->marin;
                                        $label = ($marin->grade?->libelle_court ?? '') . ' ' .
                                                $marin->prenom . ' ' .
                                                $marin->nom .
                                                ($marin->unite ? ' (' . $marin->unite->libelle_court . ')' : '');
                                        return [$fcmMarin->id => trim($label)];
                                    })
                                    ->toArray();
                            })
                            ->getOptionLabelUsing(function ($value): ?string {
                                $fcmMarin = FcmMarin::with(['marin', 'marin.grade', 'marin.unite'])->find($value);
                                if (!$fcmMarin || !$fcmMarin->marin) return null;
                               
                                $marin = $fcmMarin->marin;
                                return ($marin->grade?->libelle_court ?? '') . ' ' .
                                       $marin->prenom . ' ' .
                                       $marin->nom .
                                       ($marin->unite ? ' (' . $marin->unite->libelle_court . ')' : '');
                            })
                            ->placeholder('Rechercher un marin...'),

                        TextInput::make('taux_global')
                            ->label('Taux Global')
                            ->numeric()
                            ->default(0)
                            ->suffix('%')
                            ->helperText('Sera recalculé automatiquement'),

                        TextInput::make('taux_stage')
                            ->label('Taux Stage')
                            ->numeric()
                            ->default(0)
                            ->suffix('%')
                            ->helperText('Sera recalculé automatiquement'),

                        TextInput::make('taux_activite')
                            ->label('Taux Activité')
                            ->numeric()
                            ->default(0)
                            ->suffix('%')
                            ->helperText('Sera recalculé automatiquement'),
                    ])
                    ->after(function ($record, array $data) {
                        $this->recalculerTauxMarin($record);
                    }),

                Tables\Actions\Action::make('recalculer_tous_taux')
                    ->label('Recalculer tous les taux')
                    ->icon('heroicon-o-calculator')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Recalcul de tous les taux')
                    ->modalDescription('Cette action va recalculer les taux pour tous les marins de ce parcours. Cette opération peut prendre quelques minutes.')
                    ->modalSubmitActionLabel('Lancer le recalcul')
                    ->modalIcon('heroicon-o-calculator')
                    ->action(function () {
                        $this->recalculerTousLesTaux();
                    })
                    ->extraAttributes([
                        'x-data' => '{ loading: false }',
                        'x-on:click' => 'loading = true',
                        'x-bind:disabled' => 'loading',
                    ])
                    ->badge(function () {
                        return $this->getOwnerRecord()->fcmMarins()->count();
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('recalculer_taux_marin')
                    ->label('Recalculer')
                    ->icon('heroicon-o-arrow-path')
                    ->size('sm')
                    ->color('warning')
                    ->tooltip('Recalculer les taux pour ce marin')
                    ->requiresConfirmation()
                    ->modalHeading('Recalcul des taux du marin')
                    ->modalDescription(fn ($record) => 'Recalculer les taux pour ' . $record->marin->nom . ' ?')
                    ->action(function ($record) {
                        $this->recalculerTauxMarin($record);
                    }),

                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('recalculer_taux_selection')
                        ->label('Recalculer les taux sélectionnés')
                        ->icon('heroicon-o-calculator')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Recalcul des taux sélectionnés')
                        ->modalDescription('Recalculer les taux pour les marins sélectionnés ?')
                        ->action(function ($records) {
                            $this->recalculerTauxMarins($records);
                        }),

                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Recalcule les taux pour tous les marins du parcours
     */
    protected function recalculerTousLesTaux(): void
    {
        try {
            $parcours = $this->getOwnerRecord();
            $marins = $parcours->fcmMarins;
            $totalMarins = $marins->count();
            $success = 0;
            $errors = 0;

            foreach ($marins as $marin) {
                try {
                    $pivotData = $marin->pivot;
                    $parcoursMarin = json_decode($pivotData->parcoursmarin, true);

                    if ($parcoursMarin) {
                        // ✅ Mise à jour des heures
                        CalculHeuresParcoursService::mettreAJourHeures($parcoursMarin);
                        
                        // ✅ Calcul du taux de validation (% d'éléments validés)
                        $pivotData->taux_global = CalculHeuresParcoursService::calculerPourcentageEtatValid($parcoursMarin);
                        $pivotData->parcoursmarin = json_encode($parcoursMarin);
                        $pivotData->save();

                        $success++;
                    } else {
                        $errors++;
                    }
                } catch (\Exception $e) {
                    $errors++;
                    \Illuminate\Support\Facades\Log::error('Erreur recalcul taux marin', [
                        'marin_id' => $marin->id,
                        'erreur' => $e->getMessage()
                    ]);
                }
            }

            $message = "Recalcul terminé : $success succès";
            if ($errors > 0) {
                $message .= ", $errors erreurs";
            }

            Notification::make()
                ->title('Recalcul des taux terminé')
                ->success($errors === 0)
                //->danger($errors > 0)
                ->body($message)
                ->persistent()
                ->send();

            $this->dispatch('$refresh');

        } catch (\Exception $e) {
            Notification::make()
                ->title('Erreur lors du recalcul global')
                ->danger()
                ->body('Une erreur s\'est produite : ' . $e->getMessage())
                ->persistent()
                ->send();
        }
    }

    /**
     * Recalcule les taux pour un marin spécifique
     */
    protected function recalculerTauxMarin($marin): void
    {
        try {
            $pivotData = $marin->pivot;
            $parcoursMarin = json_decode($pivotData->parcoursmarin, true);

            if ($parcoursMarin) {
                // ✅ Mise à jour des heures
                CalculHeuresParcoursService::mettreAJourHeures($parcoursMarin);
                
                // ✅ Calcul du taux de validation (% d'éléments validés)
                $pivotData->taux_global = CalculHeuresParcoursService::calculerPourcentageEtatValid($parcoursMarin);
                $pivotData->parcoursmarin = json_encode($parcoursMarin);
                $pivotData->save();

                Notification::make()
                    ->title('Taux recalculés avec succès')
                    ->success()
                    ->body('Taux mis à jour pour ' . $marin->marin->nom)
                    ->send();

                $this->dispatch('$refresh');
            } else {
                throw new \Exception('Données de parcours invalides');
            }

        } catch (\Exception $e) {
            Notification::make()
                ->title('Erreur lors du recalcul')
                ->danger()
                ->body('Impossible de recalculer les taux pour ' . $marin->marin->nom)
                ->send();
        }
    }

    /**
     * Recalcule les taux pour plusieurs marins sélectionnés
     */
    protected function recalculerTauxMarins($marins): void
    {
        $success = 0;
        $errors = 0;

        foreach ($marins as $marin) {
            try {
                $pivotData = $marin->pivot;
                $parcoursMarin = json_decode($pivotData->parcoursmarin, true);

                if ($parcoursMarin) {
                    // ✅ Mise à jour des heures
                    CalculHeuresParcoursService::mettreAJourHeures($parcoursMarin);
                    
                    // ✅ Calcul du taux de validation (% d'éléments validés)
                    $pivotData->taux_global = CalculHeuresParcoursService::calculerPourcentageEtatValid($parcoursMarin);
                    $pivotData->parcoursmarin = json_encode($parcoursMarin);
                    $pivotData->save();
                    
                    $success++;
                } else {
                    $errors++;
                }
            } catch (\Exception $e) {
                $errors++;
            }
        }

        $message = "Recalcul terminé : $success succès";
        if ($errors > 0) {
            $message .= ", $errors erreurs";
        }

        Notification::make()
            ->title('Recalcul des taux sélectionnés')
            ->success($errors === 0)
            ->warning($errors > 0)
            ->body($message)
            ->send();

        $this->dispatch('$refresh');
    }
}
