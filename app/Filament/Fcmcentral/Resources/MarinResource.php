<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Illuminate\Database\Eloquent\Builder;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Filters\Filter;

use Modules\FcmCommun\Models\Cohorte;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\FcmCentral\Models\Parcours;
//use Modules\FcmCentral\Filament\Fcmcentral\Resources\FcmMarinResource;

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
                                array_key_exists('en_fcm', $data) && $data['en_fcm'],
                                fn (Builder $query, $date): Builder => $query->enFcm(),
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (! array_key_exists('en_fcm', $data) || ! $data['en_fcm']) {
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

                        $event = new UserGeneratedEvent(
                            UserGeneratedEventTypes::MARIN_EN_FCM,
                            auth()->user()->uuid,
                            ObjectClasses::MARIN,
                            $record->uuid,
                            []
                        );
                        event($event);
                        
                    }),

                Tables\Actions\Action::make('livret-de-fcm')
                    ->label("Livret de FCM")
                    ->button()
                    ->color('warning')
                    ->visible(function ($record) {
                        
                        return $record->suiviEnFcm && 
                                $record->complements_fcm && 
                                $record->complements_fcm->parcoursSerialises()->exists();
                    })
                    //->url(fn ($record): string => MarinResource::getUrl('livret-de-fcm', ['record' => $record->id])),
                    ->action(function ($record){
                        // Recupere le premier parcours Sérialisés
                        $fcmMarinId = $record->complements_fcm->id;
                        
                        $url = static::getUrl('livret-de-fcm',['record' => $fcmMarinId]);
                        return redirect($url);
                       
                    }),

                Tables\Actions\Action::make("ne_plus_suivre_en_fcm")
                    ->label("Ne plus suivre en FCM")
                    ->button()
                    ->color('danger')
                    ->visible(function ($record) {
                        return $record->suiviEnFcm;
                    })
                    ->action(function ($record) {
                        $event = new UserGeneratedEvent(
                            UserGeneratedEventTypes::MARIN_PAS_FCM,
                            auth()->user()->uuid,
                            ObjectClasses::MARIN,
                            $record->uuid,
                            []
                        );
                        event($event);
                    }),

                Tables\Actions\Action::make('attribuer-cohorte-et-mentor')
                    ->label("Attribuer cohorte et mentor")
                    ->button()
                    ->visible(fn ($record) => $record->suiviEnFcm)
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Select::make('cohorte_id')
                            ->label("Cohorte de ce marin")
                            ->options(Cohorte::all()->pluck('libelle_long', 'uuid')),
                        Forms\Components\Select::make('mentor_id')
                            ->label("Choisir un mentor")
                            ->options(Marin::all()->pluck('fullNameAndGrade', 'uuid'))
                            ->searchable(),
                        Forms\Components\Select::make('parcours_id')
                            ->label('Choisir un Parcours')
                            ->options(Parcours::whereHas('parcoursSerialise')->pluck('libelle_court', 'uuid'))
                            ->searchable()
                            ->visible(function ($record) {
                                $visible = $record->suiviEnFcm && 
                                    $record->complements_fcm && 
                                    !$record->complements_fcm->parcoursSerialises()->exists();
                                return $visible;
                            })
                    ])
                    ->action(function ($record, $data) {
                        
                        if (array_key_exists("cohorte_id", $data) && $data["cohorte_id"] != null){
                            $event = new UserGeneratedEvent(
                                UserGeneratedEventTypes::ATTRIBUER_COHORTE_MARIN,
                                auth()->user()->uuid,
                                ObjectClasses::MARIN,
                                $record->uuid,
                                [
                                    "cohorte" => $data["cohorte_id"],
                                ]
                            );
                            event($event);
                        }
                        
                        if (array_key_exists("mentor_id", $data) && $data["mentor_id"] != null){
                            $event = new UserGeneratedEvent(
                                UserGeneratedEventTypes::ATTRIBUER_MENTOR_MARIN,
                                auth()->user()->uuid,
                                ObjectClasses::MARIN,
                                $record->uuid,
                                [
                                    "mentor" => $data["mentor_id"],
                                ]
                            );
                            event($event);
                        }

                        if (array_key_exists("parcours_id", $data) && $data["parcours_id"] != null){
                            $event = new UserGeneratedEvent(
                                UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN,
                                auth()->user()->uuid,
                                ObjectClasses::MARIN,
                                $record->uuid,
                                [
                                    "parcours" =>  $data["parcours_id"],
                                ]
                            );
                            event($event);
                        }
                    }),
                Tables\Actions\Action::make('assignerParcours')
                    ->label('Changer le parcours de ce marin')
                    ->button()
                    ->color('success')
                    ->modalHeading('Changer le parcours de ce marin')
                    ->modalWidth('lg')
                    ->form([
                        Forms\Components\Select::make('parcours_id')
                            ->label('Choisir un Parcours')
                            ->options(Parcours::whereHas('parcoursSerialise')->pluck('libelle_court', 'uuid'))
                            ->searchable()
                    ])
                    // Pour enlever le bouton si il exist dans MarinParcours
                    ->visible(function ($record) {
                        $hidden = $record->suiviEnFcm && 
                                $record->complements_fcm && 
                                !$record->complements_fcm->parcoursSerialises()->exists();
                        return ! $hidden;
                    })
                    ->action(function ($record, $data) {
                        $event = new UserGeneratedEvent(
                            UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN,
                            auth()->user()->uuid,
                            ObjectClasses::MARIN,
                            $record->uuid,
                            [
                                "parcours" =>  $data["parcours_id"],
                            ]
                        );
                        event($event);
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
