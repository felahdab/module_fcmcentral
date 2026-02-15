<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\TypeNoteResource\Pages\ListTypeNotes;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\TypeNoteResource\Pages\CreateTypeNote;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\TypeNoteResource\Pages\EditTypeNote;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\TypeNoteResource\Pages;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\TypeNoteResource\RelationManagers;
use Modules\FcmCentral\Models\TypeNote;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

 



class TypeNoteResource extends Resource
{
    protected static ?string $model = TypeNote::class;
    protected static string | \UnitEnum | null $navigationGroup = "Gestion";
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-group';

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
    
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required()
                    ->label('Code')
                    ->maxLength(150),

                TextInput::make('libelle_court')
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
                TextColumn::make('id')
                    ->label('ID')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
               
                TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->label('Code')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('libelle_court')
                    ->searchable()
                    ->label('Libellé'),
                    
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => ListTypeNotes::route('/'),
            'create' => CreateTypeNote::route('/create'),
            'edit' => EditTypeNote::route('/{record}/edit'),
        ];
    }
}
