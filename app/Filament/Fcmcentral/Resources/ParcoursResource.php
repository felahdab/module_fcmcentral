<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\Pages\ListParcours;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\Pages\CreateParcours;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\Pages\ViewParcours;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\Pages\EditParcours;
use Carbon\Carbon;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;

use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\RelationManagers\AffectationsRelationManager;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\Pages;
use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Models\ParcoursType;
 
use Modules\FcmCentral\Services\ParcoursService;

class ParcoursResource extends Resource
{

    protected static ?string $model = Parcours::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string | \UnitEnum | null $navigationGroup = "Catalogue";

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('libelle_court')
                    ->required()
                    ->label('Libellé court')
                    ->maxLength(150),
                    
                TextInput::make('libelle_long')
                    ->required()
                    ->label('Libellé long')
                    ->maxLength(1500),
                    TextInput::make('code')
                    ->required()
                    ->label('Code')
                    ->maxLength(150),    
                    Select::make('parcours_type_id')
                    ->label('Type de Parcours')
                    ->required()
                    ->searchable()
                    ->options(ParcoursType::all()->pluck('libelle_court','id')),    

            ]);
    }

    public static function table(Table $table): Table
    {
       
        $parcours_service = new ParcoursService();

        return $table
            ->defaultSort('ordre','asc')
            ->reorderable('ordre')
            ->columns([
               TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('libelle_court')
                    ->label('Libellé')
                    ->limit(50)
                    ->sortable()
                    ->searchable()
                    ->description(fn ($record) => substr($record->libelle_long,0,40).'...'),
                    TextColumn::make('code')
                    ->label('Code')
                    ->toggleable(isToggledHiddenByDefault: true),
                    TextColumn::make('parcoursType.libelle_court')
                    ->label('Parcours Type')
                    ->badge()
                    //->color('danger') 
                    ->color(fn($record)=>$record->parcoursType->color ?? 'info')  ,  
                    TextColumn::make('affectations.libelle_court')
                    ->label('Affectations')
                    ->badge()
                    ->listWithLineBreaks()
                    ->color(fn($state, $record) =>
                        $record->affectations->pluck('color')->map(fn($color) => $color ?? 'info')->first()
                    ),    
                  
                TextColumn::make('affections.competences.code')
                    ->label('Competences')
                    ->badge(),
                    TextColumn::make('affections.competences.savoirfaires.code')
                    ->label('Competences')
                    ->badge(),    
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
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->icon('heroicon-m-pencil-square')
                    ->extraAttributes([
                        'title' => 'Modifier',
                        'class' => 'btn-modif',
                    ]),
                ViewAction::make()
                    ->iconButton()
                    ->icon('heroicon-m-queue-list')
                    ->extraAttributes([
                        'title' => 'Parcours détaillé',
                        'class' => 'btn-livret',
                    ]),                    
                Action::make('serializeParcours')
                    ->label('Figer')
                    ->button()
                    ->visible(fn() => auth()->user()->can("fcmcentral::parcours.figer"))
                    ->color('success')
                    ->modalHeading('Figer le Parcours')
                    ->modalWidth('lg')
                    ->schema([
                        TextInput::make('version')
                            ->label('Version')
                            ->numeric()
                            ->required()
                            ->disabled()
                            ->dehydrated(),
                        DatePicker::make('date de debut')
                            ->label('Date')
                            ->required()
                            //->minDate(fn ($record) => Carbon::now()->today()->max($parcours_service->firstPossibleNewVersionDate($record))),
                    ])
                    ->fillForm(function ($record) use ($parcours_service){
                        return 
                        [
                            'date de debut' => Carbon::now()->tomorrow()->max($parcours_service->firstPossibleNewVersionDate($record)),
                            'version' => $record->getLatestSerializedVersion() + 1,
                        ];
                    })
                    ->action(function ($record, $data) {
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
            ->toolbarActions([
                BulkActionGroup::make([
                   // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    
    /**
     * Obtenir la première date possible pour une nouvelle version
     */
    protected static function getFirstPossibleNewVersionDate(Parcours $parcours): Carbon
    {
        $previous = ParcoursSerialise::where('uuid', $parcours->uuid)
            ->orderBy('version')
            //->get()
            ->last();

        if ($previous) {
            return (new Carbon($previous->date_debut))->addDay()->startOfDay();
        } else {
            return Carbon::now()->addDay()->startOfDay();
        }
    }

    public static function getRelations(): array
    {
        return [
            AffectationsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListParcours::route('/'),
            'create' => CreateParcours::route('/create'),
            'view' => ViewParcours::route('/{record}/view'),
            'edit' => EditParcours::route('/{record}/edit'),
        ];
    }
}
