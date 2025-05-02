<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\FcmMarinResource\Pages;
use Modules\RH\Filament\RH\Resources\MarinResource\Pages as RhPages;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\FcmMarinResource\RelationManagers;
use Modules\FcmCentral\Models\FcmMarin;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Modules\FcmCentral\Models\ParcoursSerialise;

use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Arr;



use Modules\FcmCommun\Services\EventTriggerService;

class FcmMarinResource extends Resource
{
    
    
    protected static ?string $model = FcmMarin::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Marins en FCM Central';

    protected static ?string $navigationGroup = 'Marins';
    
    protected static bool $shouldRegisterNavigation = false;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nom')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('prenom')
                    ->required()
                    ->maxLength(100)
                    ->default(''),
                Forms\Components\TextInput::make('matricule')
                    ->maxLength(20)
                    ->default(''),
                Forms\Components\TextInput::make('nid')
                    ->maxLength(15)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('marin.nom')
                    ->label('Nom Prenom')
                    ->getStateUsing(function ($record) {
                        return $record->marin->nom . ' ' . $record->marin->prenom;
                    })
                    ->searchable(),
                TextColumn::make('cohorte.libelle_court')

                    ->label('Cohorte')
                    ->searchable(),
                
                TextColumn::make('mentor.nom')
                    ->label('Mentor')
                    ->getStateUsing(function ($record) {
                        return $record->mentor ? $record->mentor->nom . ' ' . $record->mentor->prenom : ' Aucun';
                    })
                    ->sortable(),

                    TextColumn::make('parcoursSerialise.libelle_court')
                    ->label('Parcours')
                    ->getStateUsing(function ($record){
                        // Recup premier parcours assigné
                        $parcours= $record->parcoursSerialises?->first();
                        if ($parcours){
                            return $parcours->libelle_court.' V'.$parcours->version;
                        }else{
                            return 'Aucun';
                        }
                    })
                    ->searchable(),

                    TextColumn::make('parcoursSerialise.taux_global')
                    ->label('Taux Global')
                    ->getStateUsing(function ($record){
                        // Recup premier parcours assigné
                        $parcours= $record->parcoursSerialises?->first();
                        if ($parcours){
                            return $parcours->pivot->taux_global.' %';
                        }else{
                            return 'Aucun';
                        }
                    })
                    ->searchable(),



            ])
            ->filters([
                // TODO: inclure un filtre ternary sur le flag record->data->fcm, active par defaut.
                SelectFilter::make('en-fcm')
                    ->label("En FCM")
                    ->options([
                        'true' => 'En FCM seulement',
                    ])
                    ->attribute('data->fcm->en_fcm'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                //Tables\Actions\EditAction::make(),
                Action::make('livret-de-fcm')
                    ->label("Livret de FCM")
                    ->button()
                    ->color('warning')
                    ->visible(function (FcmMarin $record) {
                        // return  Arr::get($record->marin->data, "fcm.en_fcm", false);
                        $isFollowed = Arr::get($record->marin->data, 'fcm.en_fcm', false);
                        return $isFollowed ? true : false;
                    })
                    ->url(fn (FcmMarin $record): string => static::getUrl('livret-de-fcm', ['record' => $record])),
                Action::make('suivre-en-fcm')
                    ->label("Suivre en FCM")
                    ->button()
                    ->color('success')
                    ->visible(function (FcmMarin $record) {
                        $isFollowed = Arr::get($record->marin->data, 'fcm.en_fcm', false);
                        return $isFollowed ? false : true;
                    })
                    ->requiresConfirmation()
                    // ->action(function(Marin $record){
                    //     $data = $record->data;
                    //     $data["fcm"] = ["en_fcm" => true ];
                    //     $record->data = $data;
                    //     $record->save();
                    // }),
                    ->action(function (FcmMarin $record) {
                        // Declencher Event

                        
                       //Data + Recuperer le prefix
                       $data["fcm"] = ["en_fcm" => true];
                       $data['marinUuid'] = $record->marin->uuid;
                      
                       // Utiliser le service pour déclencher l'événement
                       EventTriggerService::triggerEvent($data, $record);

                    }),
                Action::make('ne-plus-suivre-en-fcm')
                    ->label("Ne plus suivre en FCM")
                    ->button()
                    ->color('danger')
                    ->visible(function (FcmMarin $record) {
                        $isFollowed = Arr::get($record->marin->data, 'fcm.en_fcm', false);
                        return $isFollowed ? true : false;
                    })

                    ->requiresConfirmation()
                    ->action(function (FcmMarin $record) {
                        // Declencher Event
                        // Recherche dans FcmXXX pour envoyer dans event collection RHmarin pour  mettre a jour Flag (a voir avec commandant)

                        $eventdata  = [
                            "fcm" => ["en_fcm" => false],
                            "marinUuid" => $record->marin->uuid
                        ];
                       
                       // Utiliser le service pour déclencher l'événement
                       EventTriggerService::triggerEvent($eventdata, $record->marin);
                    }),

                // Assigner Parcours Marin
                Action::make('assignerParcours')
                    ->label('Assigner Parcours')
                    ->button()
                    ->color('success')
                    ->modalHeading('Assigner un Parcours à un Marin')
                    ->modalWidth('lg')
                    ->form([
                        Select::make('parcoursserialise_id')
                            ->label('Choisir un Parcours')
                            ->options(ParcoursSerialise::pluck('libelle_court', 'id'))
                            ->searchable()
                            ->required(),
                    ])
                    // Pour enlever le bouton si il exist dans MarinParcours
                    ->visible(function (FcmMarin $record){
                        return !$record->parcoursSerialises()->exists();
                    })
                    ->action(function (FcmMarin $record, $data) {

                         $eventdata  = [
                            "parcoursserialise_id" =>$data["parcoursserialise_id"],
                            "marinUuid" => $record->marin->uuid
                        ];
                        // Utiliser le service pour déclencher l'événement
                        EventTriggerService::triggerEvent($eventdata, $record->marin);
                    }),

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
            //ParcoursAttribuesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFcmMarins::route('/'),
            //'create' => Pages\CreateMarin::route('/create'),
            'create' => RhPages\CreateMarin::route('/create'),
            'view' => Pages\LivretDeFcm::route('/{record}'),
            
            //'edit' => Pages\EditFcmMarin::route('/{record}/edit'),
            'livret-de-fcm' => Pages\LivretDeFcm::route('/{record}/livret-de-fcm'),
        ];
    }
}
