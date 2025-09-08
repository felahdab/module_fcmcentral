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
use Modules\FcmCentral\Traits\FcmAccessControl;

class ParcoursResource extends Resource
{
    use FcmAccessControl;
    
    protected static ?string $model = Parcours::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 11;

    protected static ?string $navigationGroup = 'Parcours';
    protected static ?string $navigationLabel = 'Parcours';


    public static function canAccess(array $parameters = []): bool
    {
        // Seuls les admins peuvent accéder à cette resource
        return static::canAccessAdminMenus();
    }

    public static function canViewAny(): bool
    {
        return static::canAccessAdminMenus();
    }

    public static function canCreate(): bool
    {
        return static::canAccessAdminMenus();
    }

    public static function canEdit($record): bool
    {
        return static::canAccessAdminMenus();
    }

    public static function canDelete($record): bool
    {
        return static::canAccessAdminMenus();
    }

    public static function canDeleteAny(): bool
    {
        return static::canAccessAdminMenus();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('libelle_court')
                    ->required()
                    ->label('Libellé court')
                    ->maxLength(150),
                TextInput::make('libelle_long')
                    ->required()
                    ->label('Libellé long')
                    ->maxLength(1500),
            ]);
    }

    public static function table(Table $table): Table
    {
        $parcours_service = new ParcoursService();

        return $table
            ->defaultSort('libelle_court','asc')
            ->columns([
               TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('libelle_court')
                    ->searchable()
                    ->sortable()
                    ->label('Libellé court'),
                TextColumn::make('libelle_long')
                    ->searchable()
                    ->label('Libellé long')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordUrl(fn ($record) => static::getUrl('edit',['record'=>$record]))
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->icon('heroicon-m-pencil-square')
                    ->extraAttributes([
                        'title' => 'Modifier',
                        'class' => 'btn-modif',
                        ]), 
                Tables\Actions\ViewAction::make()
                    ->iconButton()
                    ->icon('heroicon-m-queue-list')
                    ->extraAttributes([
                        'title' => 'Parcours détaillé',
                        'class' => 'btn-livret',
                        ]),                    
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
            'view' => Pages\ViewParcours::route('/{record}/view'),
            'edit' => Pages\EditParcours::route('/{record}/edit'),
            
        ];
    }
}
