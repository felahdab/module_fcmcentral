<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Forms\Components\Toggle;
use Filament\Tables\Filters\Filter;

use Modules\FcmCommun\Models\Cohorte;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\RelationManagers;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\FcmMarinResource;


use Modules\FcmCommun\Services\EventTriggerService;


class MarinResource extends Resource
{

    

    protected static ?string $model = Marin::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Marins';

    protected static ?string $navigationLabel = "Liste des marins";

    public static function getNavigationBadge(): ?string
    {
        return Marin::enFcm()->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nom')
                    ->required()
                    ->autofocus()
                    ->maxLength(255),
                Forms\Components\TextInput::make('prenom')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->unique(
                        table: 'rh_marins',
                        column: 'email',
                        ignoreRecord: true
                    )
                    ->required(),
                Forms\Components\TextInput::make('nid')
                    ->maxLength(15)
                    ->required()
                    ->default(''),
                Forms\Components\TextInput::make('matricule')
                    ->maxLength(20)
                    ->default(''),
                Forms\Components\DatePicker::make('date_embarq'),
                Forms\Components\DatePicker::make('date_debarq'),
                Forms\Components\Select::make('grade_id')
                    ->relationship(name: 'grade', titleAttribute: 'libelle_long'),
                Forms\Components\Select::make('specialite_id')
                    ->relationship(name: 'specialite', titleAttribute: 'libelle_court'),
                Forms\Components\Select::make('brevet_id')
                    ->relationship(name: 'brevet', titleAttribute: 'libelle_long'),
                Forms\Components\Select::make('unite_id')
                    ->relationship(name: 'unite', titleAttribute: 'libelle_long'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable()
                    ->hidden(true),
                Tables\Columns\TextColumn::make('grade.libelle_court')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('nom')
                    ->searchable(),
                Tables\Columns\TextColumn::make('prenom')
                    ->searchable(),
                Tables\Columns\IconColumn::make('suiviEnFcm')
                    ->boolean()
                    ->searchable(),
                Tables\Columns\TextColumn::make('complements_fcm.cohorte.libelle_court')
                    ->searchable(),
                Tables\Columns\TextColumn::make('complements_fcm.mentor.nom')
                    ->searchable(),
                Tables\Columns\TextColumn::make('complements_fcm.donnees_du_parcours.parcours_serialise.libelle_court')
                    ->label("Parcours attribué")
                    ->searchable(),
                Tables\Columns\TextColumn::make('complements_fcm.donnees_du_parcours.taux_global')
                    ->label("Taux global")
                    ->searchable(),
                Tables\Columns\TextColumn::make('matricule')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('nid')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('date_embarq')
                    ->label('Date d\'embarquement')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_debarq')
                    ->label('Date de débarquement')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('specialite.libelle_court')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->badge(),
                Tables\Columns\TextColumn::make('brevet.libelle_court')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->badge(),
                Tables\Columns\TextColumn::make('unite.libelle_court')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('email')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('code_sirh_user')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
            ])
            ->filters([
                Filter::make('en_fcm')
                    ->form([
                        Toggle::make('en_fcm')
                            ->default(true),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['en_fcm'],
                                fn (Builder $query, $date): Builder => $query->enFcm(),
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['en_fcm']) {
                            return null;
                        }

                        return 'Marins en FCM seulement';
                    })

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make("suivre_en_fcm")
                    ->label("Suivre en FCM")
                    ->hidden(function ($record) {
                        return $record->suiviEnFcm;
                    })
                    ->action(function ($record) {

                        //Data + Recuperer le prefix
                        $data['marinUuid'] = $record->uuid;
                        $data["fcm"] = ["en_fcm" => true];
                    
                       

                        // Utiliser le service pour déclencher l'événement
                        EventTriggerService::triggerEvent($data, $record);
                    }),

                Tables\Actions\Action::make('livret-de-fcm')
                    ->label("Livret de FCM")
                    ->button()
                    ->color('warning')
                    ->visible(function ($record) {
                        return $record->suiviEnFcm && $record->complements_fcm;
                    })
                    ->url(fn ($record): string => MarinResource::getUrl('livret-de-fcm', ['record' => $record->complements_fcm])),

                Tables\Actions\Action::make("ne_plus_suivre_en_fcm")
                    ->label("Ne plus suivre en FCM")
                    ->button()
                    ->color('danger')
                    ->visible(function ($record) {
                        return $record->suiviEnFcm;
                    })
                    ->action(function ($record) {

                        //Data + Recuperer le prefix
                        $data['marinUuid'] = $record->uuid;
                        $data["fcm"] = ["en_fcm" => false];
                       

                        // Utiliser le service pour déclencher l'événement
                        EventTriggerService::triggerEvent($data, $record);
                    }),

                Tables\Actions\Action::make('attribuer-cohorte-et-mentor')
                    ->label("Attribuer cohorte et mentor")
                    ->button()
                    ->visible(fn ($record) => $record->suiviEnFcm)
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Select::make('cohorte_id')
                            ->label("Cohorte de ce marin")
                            ->options(Cohorte::all()->pluck('libelle_long', 'id')),
                        Forms\Components\Select::make('mentor_id')
                            ->label("Choisir un mentor")
                            ->options(Marin::all()->pluck('fullNameAndGrade', 'id'))
                            ->searchable(),
                        Forms\Components\Select::make('parcoursserialise_id')
                            ->label('Choisir un Parcours')
                            ->options(ParcoursSerialise::pluck('libelle_court', 'id'))
                            ->searchable()
                    ])
                    ->action(function ($record, $data) {

                        //Data + Recuperer le prefix
                        $data['marinUuid'] = $record->uuid;
                       
                        
                        
                        // Utiliser le service pour déclencher l'événement
                        EventTriggerService::triggerEvent($data, $record);
                    }),
                Tables\Actions\Action::make('assignerParcours')
                    ->label('Assigner Parcours')
                    ->button()
                    ->color('success')
                    ->modalHeading('Assigner un Parcours à un Marin')
                    ->modalWidth('lg')
                    ->form([
                        Forms\Components\Select::make('parcoursserialise_id')
                            ->label('Choisir un Parcours')
                            ->options(ParcoursSerialise::pluck('libelle_court', 'id'))
                            ->searchable()
                            ->required(),
                    ])
                    // Pour enlever le bouton si il exist dans MarinParcours
                    // ->visible(function (FcmMarin $record){
                    //     return !$record->parcoursSerialises()->exists();
                    // })
                    ->action(function ($record, $data) {

                         $eventdata  = [
                            "parcoursserialise_id" =>$data["parcoursserialise_id"],
                            "marinUuid" => $record->uuid
                        ];
                        // Utiliser le service pour déclencher l'événement
                        EventTriggerService::triggerEvent($eventdata, $record);
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMarins::route('/'),
            'livret-de-fcm' => Pages\LivretDeFcm::route('/{record}/livret-de-fcm'),
            //'create' => Pages\CreateMarin::route('/create'),
            //'edit' => Pages\EditMarin::route('/{record}/edit'),
        ];
    }
}
