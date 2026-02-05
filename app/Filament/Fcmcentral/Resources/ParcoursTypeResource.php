<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursTypeResource\Pages;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursTypeResource\RelationManagers;
use Modules\FcmCentral\Models\ParcoursType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Modules\FcmCentral\Helpers\OutilsHelper;
 


class ParcoursTypeResource extends Resource
{
    protected static ?string $model = ParcoursType::class;

    protected static ?string $navigationGroup = "Catalogue";
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


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
                TextInput::make('code')
                ->required()
                ->maxLength(150)
                ->label('code'),
                TextInput::make('libelle_court')
                ->required()
                ->maxLength(150)
                ->label('Libellé court'),
            TextInput::make('libelle_long')
                ->required()
                ->maxLength(1500)
                ->label('Libellé long'),
            Select::make('color')
                ->options(OutilsHelper::selectColors())
                ->label('Couleur du Badge'),         
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->defaultSort('ordre','asc')
            ->reorderable('ordre') 
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('id')
                ->label('ID')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
            
                TextColumn::make('code')
                ->label('Code')
                ->sortable()
                ->searchable()
                ->badge()
                ->color(fn($record)=>$record->color ??  $record->competence->color ?? 'info') 
                ,  
               
            TextColumn::make('libelle_court')
                ->label('Libellé court')
                ->limit(50)
                ->sortable()
                ->searchable()
                ->description(fn ($record) => substr($record->libelle_long,0,40).'...')
                ,
                TextColumn::make('ordre')

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
            'index' => Pages\ListParcoursTypes::route('/'),
            'create' => Pages\CreateParcoursType::route('/create'),
            'edit' => Pages\EditParcoursType::route('/{record}/edit'),
        ];
    }
}
