<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\TypeGelResource\Pages;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\TypeGelResource\RelationManagers;
use Modules\FcmCentral\Models\TypeEtat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Modules\FcmCentral\Traits\FilamentPermissionTrait;

class TypeGelResource extends Resource
{
    
    use FilamentPermissionTrait;
    
    protected static ?string $model = TypeEtat::class;
    protected static ?string $navigationLabel = "Type de  Gels" ;
    protected static ?string $label = "Type de Gel" ;
    protected static ?string $plurialLabel = "Type de Gels" ;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // Permissions marche avec le Trait
    protected static array $permissions = [
        'fcmcentral::typeEtat.index',
        'fcmcentral::admin',
        'fcmcentral::permission.fps',
        'fcmcentral::permission.bcf',
    ];

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


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTypeGels::route('/'),
            'create' => Pages\CreateTypeGel::route('/create'),
            'edit' => Pages\EditTypeGel::route('/{record}/edit'),
        ];
    }
}
