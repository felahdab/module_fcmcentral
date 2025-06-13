<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursSerialiseResource\RelationManagers;

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
use Modules\FcmCommun\Services\CalculTauxParcoursService;

class FcmMarinsRelationManager extends RelationManager
{
    protected static string $relationship = 'fcmMarins';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('recordId')
                    ->label('Marin')
                    ->options(FcmMarin::all()->pluck('marin.nom', 'id'))
                    ->searchable()
                    ->required()
                    ->disabled(fn ($context): bool => $context === 'edit'),

                TextInput::make('taux_global')
                    ->label('Taux Global (Lecture Seule)')
                    ->numeric()
                    ->readOnly(),

                TextInput::make('taux_stage')
                    ->label('Taux Stage (Lecture Seule)')
                    ->numeric()
                    ->readOnly(),

                TextInput::make('taux_activite')
                    ->label('Taux Activite (Lecture Seule)')
                    ->numeric()
                    ->readOnly(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('')
            ->columns([
                TextColumn::make('marin.nom')
                    ->label('Marin'),

                TextColumn::make('pivot.taux_global')
                    ->label('Taux Global')
                    ->sortable()
                    ->suffix('%')
                    ->color(fn ($state) => $state >= 80 ? 'success' : ($state >= 50 ? 'warning' : 'danger')),

                TextColumn::make('pivot.taux_stage')
                    ->label('Taux Stage')
                    ->sortable()
                    ->suffix('%')
                    ->color(fn ($state) => $state >= 80 ? 'success' : ($state >= 50 ? 'warning' : 'danger')),

                TextColumn::make('pivot.taux_activite')
                    ->label('Taux Activite')
                    ->sortable()
                    ->suffix('%')
                    ->color(fn ($state) => $state >= 80 ? 'success' : ($state >= 50 ? 'warning' : 'danger')),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),

                        TextInput::make('taux_global')
                            ->label('Taux Global (Lecture Seule)')
                            ->numeric()
                            ->readOnly(),

                        TextInput::make('taux_stage')
                            ->label('Taux Stage (Lecture Seule)')
                            ->numeric()
                            ->readOnly(),

                        TextInput::make('taux_activite')
                            ->label('Taux Activite (Lecture Seule)')
                            ->numeric()
                            ->readOnly(),
                    ]),

                // BOUTON PRINCIPAL : Recalculer tous les taux
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
                // BOUTON PAR LIGNE : Recalculer le taux individuel
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
                    // BOUTON BULK : Recalculer les taux sélectionnés
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

            $calculTauxService = app(CalculTauxParcoursService::class);

            foreach ($marins as $marin) {
                try {
                    $pivotData = $marin->pivot;
                    $parcoursMarin = json_decode($pivotData->parcoursmarin, true);

                    if ($calculTauxService->mettreAJourTauxDansJson($parcoursMarin)) {
                        // Mettre à jour les données
                        $pivotData->parcoursmarin = json_encode($parcoursMarin);
                        $pivotData->taux_global = $parcoursMarin['taux_global'] ?? 0;
                        $pivotData->taux_stage = $parcoursMarin['taux_stages'] ?? 0;
                        $pivotData->taux_activite = $parcoursMarin['taux_activites'] ?? 0;
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

            // Notification avec résultats
            $message = "Recalcul terminé : $success succès";
            if ($errors > 0) {
                $message .= ", $errors erreurs";
            }

            Notification::make()
                ->title('Recalcul des taux terminé')
                ->success($errors === 0)
                ->danger($errors > 0)
                ->body($message)
                ->persistent()
                ->send();

            // Rafraîchir la table
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
            $calculTauxService = app(CalculTauxParcoursService::class);
            $pivotData = $marin->pivot;
            $parcoursMarin = json_decode($pivotData->parcoursmarin, true);

            if ($calculTauxService->mettreAJourTauxDansJson($parcoursMarin)) {
                $pivotData->parcoursmarin = json_encode($parcoursMarin);
                $pivotData->taux_global = $parcoursMarin['taux_global'] ?? 0;
                $pivotData->taux_stage = $parcoursMarin['taux_stages'] ?? 0;
                $pivotData->taux_activite = $parcoursMarin['taux_activites'] ?? 0;
                $pivotData->save();

                Notification::make()
                    ->title('Taux recalculés avec succès')
                    ->success()
                    ->body('Taux mis à jour pour ' . $marin->marin->nom)
                    ->send();

                $this->dispatch('$refresh');
            } else {
                throw new \Exception('Échec du recalcul');
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
        $calculTauxService = app(CalculTauxParcoursService::class);

        foreach ($marins as $marin) {
            try {
                $pivotData = $marin->pivot;
                $parcoursMarin = json_decode($pivotData->parcoursmarin, true);

                if ($calculTauxService->mettreAJourTauxDansJson($parcoursMarin)) {
                    $pivotData->parcoursmarin = json_encode($parcoursMarin);
                    $pivotData->taux_global = $parcoursMarin['taux_global'] ?? 0;
                    $pivotData->taux_stage = $parcoursMarin['taux_stages'] ?? 0;
                    $pivotData->taux_activite = $parcoursMarin['taux_activites'] ?? 0;
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
