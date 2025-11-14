<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\TypeNoteResource\Pages;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\TypeNoteResource\RelationManagers;
use Modules\FcmCentral\Models\TypeNote;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Modules\FcmCentral\Traits\FilamentPermissionTrait;



class TypeNoteResource extends Resource
{
   
    use FilamentPermissionTrait;
   
    protected static ?string $model = TypeNote::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


   // Permissions marche avec le Trait
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
                Forms\Components\TextInput::make('libelle_court')
                    ->required()
                    ->label('Libellé court')
                    ->maxLength(150),
                Forms\Components\TextInput::make('libelle_long')
                    ->required()
                    ->label('Libellé long')
                    ->maxLength(1500),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('libelle_court','asc')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
               
                Tables\Columns\TextColumn::make('libelle_court')
                    ->searchable()
                    ->sortable()
                    ->label('Libellé court'),
                Tables\Columns\TextColumn::make('libelle_long')
                    ->searchable()
                    ->label('Libellé long')
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTypeNotes::route('/'),
            'create' => Pages\CreateTypeNote::route('/create'),
            'edit' => Pages\EditTypeNote::route('/{record}/edit'),
        ];
    }
}
