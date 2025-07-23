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
use Filament\Tables\Filters\SelectFilter;
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
use Modules\RH\Models\Specialite;
use Modules\RH\Models\Unite;

class MarinResource extends Resource
{
    protected static ?string $model = Marin::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Marins';
    protected static ?string $navigationLabel = "Marins";

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
                    ->maxLength(1500)
                    ->label('Nom'),
                Forms\Components\Select::make('grade_id')
                    ->relationship(name: 'grade', titleAttribute: 'libelle_long')
                    ->label('Grade'),
                Forms\Components\TextInput::make('prenom')
                    ->required()
                    ->maxLength(1000)
                    ->label('Prénom'),
                Forms\Components\Select::make('specialite_id')
                    ->relationship(name: 'specialite', titleAttribute: 'libelle_court')
                    ->label('Spécialité'),
                Forms\Components\TextInput::make('email')
                    ->unique(
                        table: 'rh_marins',
                        column: 'email',
                        ignoreRecord: true
                    )
                    ->required()
                    ->label('Email'),
                Forms\Components\Select::make('brevet_id')
                    ->relationship(name: 'brevet', titleAttribute: 'libelle_long')
                    ->label('Brevet'),
                Forms\Components\TextInput::make('nid')
                    ->maxLength(15)
                    ->required()
                    ->default('')
                    ->label('NID'),
                Forms\Components\Select::make('unite_id')
                    ->relationship(name: 'unite', titleAttribute: 'libelle_long')
                    ->label('Unité'),
                Forms\Components\TextInput::make('matricule')
                    ->maxLength(20)
                    ->default('')
                    ->label('Matricule'),
                // Forms\Components\DatePicker::make('date_embarq'),
                // Forms\Components\DatePicker::make('date_debarq'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('nom', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable()
                    ->hidden(true),
                Tables\Columns\TextColumn::make('grade.libelle_court')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('prenom')
                    ->searchable()
                    ->label('Prénom'),
                Tables\Columns\IconColumn::make('suiviEnFcm')
                    ->boolean()
                    ->label('Suivi FCM'),
                Tables\Columns\TextColumn::make('complements_fcm.cohorte.libelle_court')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('complements_fcm.mentor.nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('complements_fcm.donnees_du_parcours.parcours_serialise.libelle_court')
                    ->label("Parcours attribué")
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('complements_fcm.donnees_du_parcours.taux_global')
                    ->label("Taux global")
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('matricule')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('nid')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                // Tables\Columns\TextColumn::make('date_embarq')
                //     ->label('Date d\'embarquement')
                //     ->toggleable(isToggledHiddenByDefault: true)
                //     ->date()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('date_debarq')
                //     ->label('Date de débarquement')
                //     ->toggleable(isToggledHiddenByDefault: true)
                //     ->date()
                //     ->sortable(),
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
                Tables\Columns\TextColumn::make('email')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                // Tables\Columns\TextColumn::make('code_sirh_user')
                //     ->toggleable(isToggledHiddenByDefault: true)
                //     ->searchable(),
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
                    }),
                SelectFilter::make('specialite_id')
                    ->label('Spécialité')
                    ->options(Specialite::query()->orderBy('libelle_court')->pluck('libelle_court', 'id')),
                SelectFilter::make('unite_id')
                    ->label('Unité')
                    ->options(Unite::query()->orderBy('libelle_court')->pluck('libelle_court', 'id')),
               
            //////////////////////////////////////////
            /////////// Ces filtres sont à activer mais ne marchent pas en l'état car les données sont dans fcmmarin et pas marin
            //////////////////////////////////////////
                // SelectFilter::make('cohorte_id')
                //     ->label('Cohorte')
                //     ->options(Cohorte::query()->orderBy('libelle_long')->pluck('libelle_long', 'id')),
                // SelectFilter::make('mentor_id')
                //     ->label("Mentor")
                //     ->options(
                //         Marin::query()
                //             ->where('data->fcm->en_fcm', false)
                //             ->orderBy('nom')
                //             ->get()
                //             ->pluck('fullNameAndGrade', 'id')
                //     )
                //     ->query(function ($query, $value) {
                //         return $query->whereHas('fcmcentral_fcm_marin', function ($q) use ($value) {
                //             $q->where('mentor_id', $value);
                //         });
                //     }),
                // SelectFilter::make('parcoursserialise_id')
                //     ->label('Parcours')
                //     ->options(
                //         ParcoursSerialise::query()
                //             ->whereNull('date_fin')
                //             ->orderBy('libelle_court')
                //             ->get()
                //             ->pluck('libelle_court', 'id')
                //         ),

            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->icon('heroicon-m-pencil-square')
                    ->extraAttributes([
                        'title' => 'Modifier',
                        'class' => 'btn-modif',
                    ]),
                Tables\Actions\Action::make("suivre_en_fcm")
                    ->iconButton()
                    ->icon('heroicon-m-folder-plus')
                    ->extraAttributes([
                    'title' => 'Suivre en FCM',
                    'class' => 'btn-suivifcm',
                    ])
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
                    ->iconButton()
                    ->icon('heroicon-m-book-open')
                    ->extraAttributes([
                        'title' => 'Livret FCM',
                        'class' => 'btn-livret',
                    ])
                    ->visible(function ($record) {
                        return $record->suiviEnFcm && 
                                $record->complements_fcm && 
                                $record->complements_fcm->parcoursSerialises()->exists();
                    })
                    //->url(fn ($record): string => MarinResource::getUrl('livret-de-fcm', ['record' => $record->id])),
                    ->action(function ($record){
                        // $fcmMarinId = $record->complements_fcm->id;
                        // dd($record->complements_fcm);
                        // $url = static::getUrl('livret-de-fcm',['record' => $fcmMarinId]);
                        $url = static::getUrl('livret-de-fcm',['record' => $record->id]);
                        return redirect($url);
                       
                    }),
                Tables\Actions\Action::make("ne_plus_suivre_en_fcm")
                    ->iconButton()
                    ->icon('heroicon-m-folder-minus')
                    ->extraAttributes([
                    'title' => 'Ne plus suivre en FCM',
                    'class' => 'btn-suivifcm',
                    ])
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

                Tables\Actions\Action::make('attributs-fcm')
                    ->iconButton()
                    ->icon('heroicon-m-swatch')
                    ->modalHeading('Attributs FCM')
                    ->modalDescription('Selectionnez la cohorte, le mentor et le parcours à attribuer au marin.')
                    ->extraAttributes([
                        'title' => 'Attribuer cohorte - mentor - parcours',
                        'class' => 'btn-suivifcm',
                        ])
                    ->visible(fn ($record) => $record->suiviEnFcm)
                    ->requiresConfirmation()
                    ->form([
                        // MarkdownEditor::make('info')
                        // ->default('Sélectionnez la cohorte, le mentor et le parcours à attribuer au marin.')
                        // ->disabled(),
                        Forms\Components\Select::make('cohorte_id')
                            ->label("Choisir la cohorte")
                            ->options(Cohorte::query()->orderBy('libelle_long')->pluck('libelle_long', 'id')),
                            // ->options(Cohorte::all()->pluck('libelle_long', 'id')),
                        Forms\Components\Select::make('mentor_id')
                            ->label("Choisir le mentor")
                            ->options(
                                Marin::query()
                                    ->where('data->fcm->en_fcm', false)
                                    ->orderBy('nom')
                                    ->get()
                                    ->pluck('fullNameAndGrade', 'id')
                                )                              
                            // ->options(Marin::all()->pluck('fullNameAndGrade', 'id'))
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
                // Tables\Actions\Action::make('assignerParcours')
                //     ->label('Assigner Parcours')
                //     ->button()
                //     ->color('success')
                //     ->modalHeading('Assigner un Parcours à un Marin')
                //     ->modalWidth('lg')
                //     ->form([
                //         Forms\Components\Select::make('parcoursserialise_id')
                //             ->label('Choisir un Parcours')
                //             ->options(ParcoursSerialise::pluck('libelle_court', 'id'))
                //             ->searchable()
                //             ->required(),
                //     ])
                //     // Pour enlever le bouton si il exist dans MarinParcours
                //     ->visible(function ($record) {
                        
                //         return $record->suiviEnFcm && 
                //                 $record->complements_fcm && 
                //                 !$record->complements_fcm->parcoursSerialises()->exists();
                //     })
                //     ->action(function ($record, $data) {

                //          $eventdata  = [
                //             "parcoursserialise_id" =>$data["parcoursserialise_id"],
                //             "marinUuid" => $record->uuid
                //         ];
                //         // Utiliser le service pour déclencher l'événement
                //         EventTriggerService::triggerEvent($eventdata, $record);
                //     }),
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
            'create' => Pages\CreateMarin::route('/create'),
            'edit' => Pages\EditMarin::route('/{record}/edit'),
        ];
    }
}
