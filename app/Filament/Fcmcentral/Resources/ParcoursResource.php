<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\Pages;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\RelationManagers;
use Modules\FcmCentral\Models\Parcours;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Illuminate\Validation\Rule;
use Modules\FcmCentral\Events\SerializeParcoursEvent;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\RelationManagers\FonctionsRelationManager;
use Modules\FcmCentral\Models\ParcoursSerialise;

use Modules\FcmCommun\Services\UserGeneratedEventService;
use Modules\FcmCommun\Services\EventDataBuilderService;
use Modules\FcmCommun\Services\EventTypeService;
use Modules\FcmCentral\Traits\HasTablePrefix;
use Modules\FcmCommun\Services\EventTriggerService;

class ParcoursResource extends Resource
{

    use HasTablePrefix;

    protected static ?string $model = Parcours::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 11;

    protected static ?string $navigationGroup = 'Parcours';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('libelle_long')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('libelle_court')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('libelle_long')
                    ->searchable(),
                Tables\Columns\TextColumn::make('libelle_court')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
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
                    ->required()
                    
                    // va chercher dans ParcoursSerialize de FcmCentral , a etudier si FcmUnite
                    ->default(function ($record){
                        // Verification su un parcours existe
                        if ($record){
                            $maxVersion = ParcoursSerialise::where('parcours_id',$record->id)
                                            ->max('version');
                            // Incrementation
                            return $maxVersion ? $maxVersion +1 : 1;
                        }
                        // Si aucun record
                        return 1;
                    })
                    ,
                    DatePicker::make('date_debut')
                    ->label('Date')
                    ->required()
                    ->default(today())
                    //->minDate(today())
                    ,
                    TextInput::make('libelle_court')
                    ->label('Libelle Court')
                    ->default(function ($record){
                        return $record->libelle_court;
                    })
                    ->required(),
                    Textarea::make('libelle_long')
                    ->label('Libelle Long')
                    ->rows(6)
                    ->default(function ($record){
                        return $record->libelle_long;
                    })
                    ,
                ])

                ->action(function ($record,$data){

                   
                   // event (new SerializeParcoursEvent($record,$data));

                    //Data + Recuperer le prefix
                    $data['marinUuid'] = null;
                    //$prefix = (new static())->getTablePrefix();

                   
                    
                    // Utiliser le service pour déclencher l'événement
                    EventTriggerService::triggerEvent($data, $record);
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
