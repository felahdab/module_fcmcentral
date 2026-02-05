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

 



class TypeNoteResource extends Resource
{
    protected static ?string $model = TypeNote::class;
    protected static ?string $navigationGroup = "Gestion";
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    // ✅ Vérifier si l'utilisateur est admin
    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    // ✅ Optionnel : Masquer aussi dans la navigation si pas admin
    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->label('Code')
                    ->maxLength(150),

                Forms\Components\TextInput::make('libelle_court')
                    ->required()
                    ->label('Libellé court')
                    ->maxLength(150),
               
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
               
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->label('Code')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('libelle_court')
                    ->searchable()
                    ->label('Libellé'),
                    
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
