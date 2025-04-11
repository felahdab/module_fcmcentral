<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\FcmMarinResource\Widgets;

use Illuminate\Database\Eloquent\Model;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;

use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\FcmCentral\Models\MarinParcours;

use Modules\FcmCentral\Services\ParcoursService;

class SelectionDeParcours extends BaseWidget
{
    protected int | string | array $columnSpan = 2;

    public ?Model $record = null;

    public function table(Table $table): Table
    {
        $liste_des_parcours_deja_attribues = MarinParcours::where('marin_id', $this->record->id)
                                                ->get()
                                                ->pluck('parcours_id');

        return $table
            ->heading(function() use ($table) 
            { 
                $filter_state = $table->getFilter('est_attribue')->getState()["value"];

                return match($filter_state) {
                    null => "Tous les parcours", 
                    ""   => "Tous les parcours",
                    "1"  => "Parcours déjà attribués à cet utilisateur",
                    "0"  => "Parcours non attribués à cet utilisateur"
                }; 
            })
            ->query(
                ParcoursSerialise::query()
            )
            ->columns([
                TextColumn::make('version'),
                TextColumn::make('libelle_court'),
            ])
            ->filters([
                TernaryFilter::make('est_attribue')
                    ->label("Parcours affichés")
                    ->placeholder('Tous les parcours')
                    ->selectablePlaceholder(false)
                    ->default("1")
                    ->trueLabel('Parcours attribués à cet utilisateur')
                    ->falseLabel('Parcours non attribués à cet utilisateur')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereIn('id', $liste_des_parcours_deja_attribues), 
                        false: fn (Builder $query): Builder => $query->whereNotIn('id', $liste_des_parcours_deja_attribues),
                        blank: fn (Builder $query): Builder => $query
                    )
            ])
            ->actions([
                Action::make("Attribuer ce parcours au marin")
                        ->requiresConfirmation()
                        ->visible(function(Table $table){
                            $filter_state = $table->getFilter('est_attribue')->getState()["value"];
                            
                            return match($filter_state) {
                                null => false,
                                ""   => false,
                                "1"  => false,
                                "0"  => true
                            }; 
                        })
                        ->action(function($record)
                        {
                            $data = $this->record->data;
                            Arr::set($data, "fcm.en_fcm", true);
                            $this->record->data = $data;
                            $this->record->save();
                            (new ParcoursService())->attribuer_parcours_a_un_user($this->record, $record);
                        }),
                Action::make("Retirer ce parcours au marin")
                        ->requiresConfirmation()
                        ->visible(function(Table $table){
                            $filter_state = $table->getFilter('est_attribue')->getState()["value"];
                            
                            return match($filter_state) {
                                null => false,
                                ""   => false,
                                "1"  => true,
                                "0"  => false
                            }; 
                        })
                        ->action(function($record)
                        {
                            (new ParcoursService())->retirer_parcours_a_un_user($this->record, $record);
                        })
            ]);
    }
}
