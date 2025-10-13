<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\AnneeResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\FcmCentral\Models\Savoirfaire;
use Modules\FcmCentral\Models\SavoirfaireType;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;

class SavoirfairesRelationManager extends RelationManager
{
    protected static string $relationship = 'savoirfaires';
    protected static ?string $title = 'Relations avec les Savoirs Faires';
    protected static ?string $description = 'Gérez les savoir-faire associés à cette année. Vous pouvez associer plusieurs fois le même savoir-faire avec des paramètres différents.';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('recordId')
                    ->label('Savoirfaire')
                    ->options(Savoirfaire::all()->pluck('code', 'id'))
                    ->searchable()
                    ->required()
                    ->disabled(fn ($context): bool => $context === 'edit'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('libelle_court')
            ->defaultSort('fcmcentral_annee_savoirfaires.ordre', 'asc')
            ->reorderable('fcmcentral_annee_savoirfaires.ordre')
            ->columns([
                TextColumn::make('pivot.id')
                ->label('ID Pivot')
                ->toggleable(isToggledHiddenByDefault:true)
                ,
                TextColumn::make('libelle_court')
                    ->label('Libellé court')
                    ->limit(50)
                    ->sortable()
                    ->searchable()
                    ->description(fn ($record) => substr($record->libelle_long,0,40).'...')
                    ,   

                TextColumn::make('code')
                    ->label('Code')
                    ->badge()
                    ->color(fn($record) => $record->color ?? 'info'),

                TextColumn::make('pivot.ordre')
                    ->label('Ordre')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('pivot.heures')
                    ->label('Heures')
                    ->numeric(),

                TextColumn::make('pivot.jours')
                    ->label('Jours')
                    ->numeric(),

                TextColumn::make('pivot.libelle_court')
                    ->label('Intitulé'),

                TextColumn::make('savoirfaireType.libelle_court')
                    ->label('Type')
                    ->badge()
                    ->color(fn($record) =>$record->pivot->savoirfaireType->color ?? 'info')
                    ->getStateUsing(fn($record) =>
                        SavoirfaireType::find($record->pivot->savoirfaire_type_id)?->libelle_court
                    ),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\AttachAction::make()
                //     ->color('primary')
                //     ->form(fn (Tables\Actions\AttachAction $action): array => [
                //         $action->getRecordSelect(),
                //         Forms\Components\TextInput::make('ordre')
                //             ->label('Ordre')
                //             ->required()
                //             ->numeric()
                //             ->default(1),
                       
                //         Forms\Components\TextInput::make('heures')
                //             ->label('Heures')
                //             ->numeric(),
                       
                //         Forms\Components\TextInput::make('jours')
                //             ->label('Jours')
                //             ->numeric(),
                       
                //         Forms\Components\TextInput::make('libelle_court')
                //             ->label('Intitulé')
                //             ->maxLength(255),
                       
                //         Select::make('savoirfaire_type_id')
                //             ->label('Type de Savoir-faire')
                //             ->options(SavoirfaireType::all()->pluck('libelle_court', 'id'))
                //             ->searchable(),
                //     ])
            ])
            ->actions([
                Tables\Actions\Action::make('manageDetails')
                    ->label('Gérer')
                    ->icon('heroicon-m-cog-6-tooth')
                    ->url(fn () => \Modules\FcmCentral\Filament\Fcmcentral\Resources\AnneeSavoirfaireResource::getUrl('index', [
                        'tableFilters' => ['annee_id' => ['value' => $this->ownerRecord->id]]
                    ])),
            

            
            
                   

                // Tables\Actions\DetachAction::make()
                //     ->iconButton()
                //     ->icon('heroicon-m-arrow-top-right-on-square')
                //     ->extraAttributes([
                //         'title' => 'Détacher',
                //     ]),

                
        //         Tables\Actions\Action::make('detachPivot')
        // ->label('Détacher')
        // ->iconButton()
        // ->icon('heroicon-m-arrow-top-right-on-square')
        // ->extraAttributes([
        //     'title' => 'Détacher',
        // ])
        // ->color('danger')
        // ->requiresConfirmation()
        // ->modalHeading(fn ($record) => 'Détacher ' . $record->code)
        // ->modalDescription('Êtes-vous sûr de vouloir détacher ce savoir-faire de cette année ?')
        // ->action(function ($record) {
        //     \Modules\FcmCentral\Models\AnneeSavoirfaire::find($record->pivot->id)?->delete();
        // }),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}

