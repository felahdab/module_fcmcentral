<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Carbon\Carbon;

use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;

use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Services\ParcoursService;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\RelationManagers\FonctionsRelationManager;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\Pages;
use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\FcmCentral\Models\Parcours;

class ParcoursResource extends Resource
{
    protected static ?string $model = Parcours::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 11;

    protected static ?string $navigationGroup = 'Parcours';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('libelle_long')
                    ->required()
                    ->maxLength(255),
                TextInput::make('libelle_court')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        $parcours_service = new ParcoursService();

        return $table
            ->columns([
               TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('libelle_long')
                    ->searchable(),
                TextColumn::make('libelle_court')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Action::make('serializeParcours')
                    ->label('Figer')
                    ->button()
                    ->color('success')
                    ->modalHeading('Figer le Parcours')
                    ->modalWidth('lg')
                    ->form([
                        TextInput::make('version')
                            ->label('Version')
                            ->numeric()
                            ->required()
                            ->disabled()
                            ->dehydrated(),
                        DatePicker::make('date de debut')
                            ->label('Date')
                            ->required()
                            ->minDate(fn ($record) => Carbon::now()->today()->max($parcours_service->firstPossibleNewVersionDate($record))),
                    ])
                    ->fillForm(function ($record) use ($parcours_service){
                        return 
                        [
                            'date de debut' => Carbon::now()->tomorrow()->max($parcours_service->firstPossibleNewVersionDate($record)),
                            'version' => $record->getLatestSerializedVersion() + 1,
                        ];
                    })
                    ->action(function ($record, $data){
                        $event = new UserGeneratedEvent(
                            UserGeneratedEventTypes::CREATION_PARCOURS_SERIALISE,
                            auth()->user()->uuid,
                            ObjectClasses::PARCOURS,
                            $record->uuid,
                            [
                                "date_de_debut" => $data["date de debut"], 
                                "version"=> $data["version"], 
                            ]
                        );

                        event($event);
                    })
                ])
                ->bulkActions([
                    Tables\Actions\BulkActionGroup::make([
                        Tables\Actions\DeleteBulkAction::make(),
                    ]),
                ]);
    }

    public static function getRelations(): array
    {
        return [
            FonctionsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListParcours::route('/'),
            'create' => Pages\CreateParcours::route('/create'),
            'edit' => Pages\EditParcours::route('/{record}/edit'),
            'view' => Pages\ViewParcours::route('/{record}/view'),
        ];
    }
}
