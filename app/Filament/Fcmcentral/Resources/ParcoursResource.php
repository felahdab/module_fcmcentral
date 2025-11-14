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
use Filament\Forms\Components\Select;

use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\RelationManagers\AffectationsRelationManager;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\Pages;
use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Models\ParcoursType;
use Modules\FcmCentral\Traits\FilamentPermissionTrait;
use Modules\FcmCentral\Services\ParcoursService;

class ParcoursResource extends Resource
{
    
    use FilamentPermissionTrait;
   
    protected static ?string $model = Parcours::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    protected static array $permissions = [
        'fcmcentral::gerer_parcours',
        'fcmcentral::droits_admin',
        'fcmcentral::droits_bcf',
        'fcmcentral::droits_supadmin',
    ];

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
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                   // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Créer un parcours sérialisé
     */
    protected static function createParcoursSerialise(Parcours $parcours, array $parcours_data, $date_de_debut, $version): ?ParcoursSerialise
    {
        // Vérifier la version précédente
        $previous = ParcoursSerialise::where('uuid', $parcours->uuid)
            ->orderBy('version')
            ->get()
            ->last();

        if ($previous) {
            if ((new Carbon($previous->date_debut))->isAfter((new Carbon($date_de_debut)))) {
                return null;
            }
        } else {
            if (Carbon::now()->isAfter((new Carbon($date_de_debut)))) {
                return null;
            }
        }

        // Créer le nouveau parcours sérialisé
        $parcours_serialise = ParcoursSerialise::create([
            "uuid" => $parcours->uuid,
            "libelle_long" => $parcours->libelle_long,
            "libelle_court" => $parcours->libelle_court,
            "version" => $version,
            "date_debut" => $date_de_debut,
            "date_fin" => null,
            "parcours_id" => $parcours->id,
            "parcours" => $parcours_data // Structure complète avec années
        ]);

        // Mettre à jour les versions précédentes
        ParcoursSerialise::where('uuid', $parcours->uuid)
            ->where('version', '<', $version)
            ->whereNull('date_fin')
            ->update(['date_fin' => Carbon::parse($date_de_debut)->subDay()]);

        return $parcours_serialise;
    }

    /**
     * Obtenir la première date possible pour une nouvelle version
     */
    protected static function getFirstPossibleNewVersionDate(Parcours $parcours): Carbon
    {
        $previous = ParcoursSerialise::where('uuid', $parcours->uuid)
            ->orderBy('version')
            ->get()
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
            'index' => Pages\ListParcours::route('/'),
            'create' => Pages\CreateParcours::route('/create'),
            'view' => Pages\ViewParcours::route('/{record}/view'),
            'edit' => Pages\EditParcours::route('/{record}/edit'),
        ];
    }
}
