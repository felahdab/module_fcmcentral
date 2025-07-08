<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursSerialiseResource\Pages;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursSerialiseResource\RelationManagers;
use Modules\FcmCentral\Models\ParcoursSerialise;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Modules\FcmCentral\Http\Livewire\ParcoursDetails;
use Filament\Forms\Components\Placeholder;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursSerialiseResource\Pages\RelationManagers\FcmMarinsRelationManager;

class ParcoursSerialiseResource extends Resource
{
    protected static ?string $model = ParcoursSerialise::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 10;

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
                            Forms\Components\TextInput::make('version')
                                ->required()
                                ->maxLength(255)
                                ->default(1),
                            Forms\Components\DatePicker::make('date_debut'),
                            Forms\Components\DatePicker::make('date_fin'),
                        // Forms\Components\Textarea::make('parcours')
                        // ->formatStateUsing(function ($record){
                        //     return json_encode($record->parcours, JSON_PRETTY_PRINT);
                        // })
                        //     ->columnSpanFull()
                        //     ->rows(20)
                        //     ->disabled(),
                   
                    
                    // Section "Parcours Serialisés"
                    Section::make('Parcours Serialisés')
                    ->schema([
     
                        Placeholder::make('')
                        ->content(fn ($record) => view('fcmcentral::filament.fcmcentral.livewire.parcours.parcours-details',[
                            //'parcours' =>json_decode($record?->parcours ?? '{}', true),
                            'parcours' =>$record->parcours ,
                        ])),
                  
                    ]),
                    
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
                Tables\Columns\TextColumn::make('version')
                    ->searchable(),
                Tables\Columns\TextColumn::make('libelle_court')
                    ->searchable(),
                Tables\Columns\TextColumn::make('libelle_long')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('date_debut')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_fin')
                    ->date()
                    ->sortable(),
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
            FcmMarinsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListParcoursSerialises::route('/'),
            'create' => Pages\CreateParcoursSerialise::route('/create'),
            'edit' => Pages\EditParcoursSerialise::route('/{record}/edit'),
        ];
    }
}
