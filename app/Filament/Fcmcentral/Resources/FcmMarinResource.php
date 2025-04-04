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

use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Arr;
use Modules\FcmCentral\Events\SuivreMarinFcmEvent;

class FcmMarinResource extends Resource
{
    protected static ?string $model = FcmMarin::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Marins en FCM Central';

    protected static ?string $navigationGroup = 'Marins';


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
                TextColumn::make('data')
                    ->searchable(),
                TextColumn::make('mentor.nom')
                    ->label('Mentor')
                    ->getStateUsing(function ($record) {
                        return $record->mentor ? $record->mentor->nom . ' ' . $record->mentor->prenom : ' Aucun';
                    })
                    ->sortable(),

                TextColumn::make('marin.uuid')
                    ->label('UUID')
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

                        $data["fcm"] = ["en_fcm" => true];
                        event(new SuivreMarinFcmEvent($record->marin, $data));
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

                        $data["fcm"] = ["en_fcm" => false];
                        event(new SuivreMarinFcmEvent($record->marin, $data));
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
            'view' => Pages\ViewFcmMarin::route('/{record}'),
            'edit' => Pages\EditFcmMarin::route('/{record}/edit'),
            'livret-de-fcm' => Pages\LivretDeFcm::route('/{record}/livret-de-fcm'),
        ];
    }
}
