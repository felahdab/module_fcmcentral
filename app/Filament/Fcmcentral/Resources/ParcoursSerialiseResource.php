<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursSerialiseResource\Pages;

use Modules\FcmCentral\Models\ParcoursSerialise;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\Filter;

use Modules\FcmCentral\Http\Livewire\ParcoursDetails;
use Filament\Forms\Components\Placeholder;
 


use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\RelationManagers\MarinsRelationManager;

class ParcoursSerialiseResource extends Resource
{
    protected static ?string $model = ParcoursSerialise::class;

    protected static ?string $navigationGroup = "Catalogue";
    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                
                Forms\Components\TextInput::make('libelle_court')
                    ->required()
                    ->label('Libellé court')
                    ->maxLength(150)
                    ->disabled(),
                Forms\Components\TextInput::make('libelle_long')
                    ->required()
                    ->label('Libellé long')
                    ->maxLength(1500)
                    ->disabled(),
                Forms\Components\TextInput::make('version')
                    ->required()
                    ->label('Version')
                    ->maxLength(5)
                    ->default(1)
                    ->disabled(),
                Forms\Components\DatePicker::make('date_debut')
                    ->label('Date début')
                    ->required()
                    ->disabled(),
                Forms\Components\DatePicker::make('date_fin')
                    ->label('Date fin')
                    ->disabled(),
                // Section "Parcours Serialisés"
                Section::make('Composition du PPA')
                    ->schema([    
                        Placeholder::make('')
                        ->content(fn ($record) => view('fcmcentral::filament.fcmcentral.livewire.parcours.parcours-details',[
                            //'parcours' =>json_decode($record?->parcours ?? '{}', true),
                            'parcours' =>$record->parcours ,
                        ])),
                    ]),
                // Forms\Components\Textarea::make('parcours')
                // ->formatStateUsing(function ($record){
                //     return json_encode($record->parcours, JSON_PRETTY_PRINT);
                // })
                //     ->columnSpanFull()
                //     ->rows(20)
                //     ->disabled(),
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
                    ->searchable()
                    ->label('Libellé court'),
                Tables\Columns\TextColumn::make('libelle_long')
                    ->searchable()
                    ->label('Libellé long')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('date_debut')
                    ->date()
                    ->label('Date début')
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_fin')
                    ->date()
                    ->label('Date fin')
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
                Filter::make('date_fin')
                ->query(fn (Builder $query): Builder => $query->whereNull('date_fin'))
                ->label('PPA en cours')
                ->default(),
            ])
            ->actions([
                // Tables\Actions\ViewAction::make()
                // ->iconButton()
                // ->icon('heroicon-m-eye')
                // ->modalHeading('Détail')
                // ->extraAttributes([
                //     'title' => 'Consulter',
                //     'class' => 'btn-consult',
                //     ]),

                Tables\Actions\EditAction::make()
                ->iconButton()
                ->icon('heroicon-m-eye')
                ->modalHeading('Détail')
                ->extraAttributes([
                    'title' => 'Consulter',
                    'class' => 'btn-consult',
                    ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ;
    }

    public static function getRelations(): array
    {
        return [
            MarinsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListParcoursSerialises::route('/'),
            // 'view' => Pages\ViewParcoursSerialise::route('/{record}/view'),
            // 'create' => Pages\CreateParcoursSerialise::route('/create'),
            'edit' => Pages\EditParcoursSerialise::route('/{record}/edit'),
        ];
    }
}
