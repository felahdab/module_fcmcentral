<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\ObjectifResource\Pages;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ObjectifResource\RelationManagers;
use Modules\FcmCentral\Models\Objectif;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;

use Modules\FcmCentral\Traits\FilamentPermissionTrait;

use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

class ObjectifResource extends Resource
{
  
    use FilamentPermissionTrait;
    
    protected static ?string $model = Objectif::class;

    
  

    // Permissions marche avec le Trait
    protected static array $permissions = [
        'fcmcentral::objectif.index',
        'fcmcentral::admin',
        'fcmcentral::permission.fps',
        'fcmcentral::permission.bcf',
    ];


    public static function form(Form $form): Form
{
    return $form
        ->schema([
            TextInput::make('libelle_court')
                ->required()
                ->label('Libellé court')
                ->maxLength(150),
                
            TextInput::make('libelle_long')
                ->required()
                ->label('Libellé long')
                ->maxLength(1500),
            
                TextInput::make('code')
                    ->required()
                    ->label('Code')
                    ->maxLength(150),       
                
            //  Select avec validation stricte
            Forms\Components\Select::make('type_activite')
                ->options(Objectif::getTypesActivite())
                ->label('Type')
                ->required()
                ->in(Objectif::getValidTypes()) // Validation stricte
                //->default(Objectif::TYPE_OBJECTIF)
                ,

                
            Forms\Components\TextInput::make('url')
                ->label('URL')
                ->url()
                ->maxLength(1000),
                
            Forms\Components\TextInput::make('duree_validite')
                ->label('Durée de validité (jours)')
                ->numeric()
                ->minValue(0)
                ->suffix('jours'),
                
            Forms\Components\Textarea::make('prerequis')
                ->label('Prérequis')
                ->maxLength(500)
                ->rows(3),

            Forms\Components\TextInput::make('coeff')
                ->label('Coefficient')
                ->numeric()
                ->step(0.1)
                ->minValue(0)
                ->default(1.0),
                
            Forms\Components\TextInput::make('duree')
                ->label('Durée estimée')
                ->helperText('Format libre (ex: 2h, 1 jour, 1 semaine)')
                ->maxLength(50),
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
                    ->badge()
                    ->color(fn($record)=>$record->color ?? 'info')
                    ->searchable(),       
                TextColumn::make('libelle_court')
                    ->label('Libellé court')
                    ->limit(50)
                    ->sortable()
                    ->searchable()
                  //  ->description(fn ($record) => substr($record->libelle_long,0,40).'...')
                    ,  
                    TextColumn::make('anneeSavoirfaire.savoirfaire.code')
                    ->label('Savoir Faire')
                    ->badge()
                    
                    ->color(fn($record)=>
                     $record->anneeSavoirfaire->savoirfaire->competence->color
                    ?? 'info'
                ),     
                TextColumn::make('anneeSavoirfaire.savoirfaireType.code')
                ->label('Type')
                ->badge()
                ,
                TextColumn::make('anneeSavoirfaire.savoirfaire.competence.code')
                ->label('Competence')
                    ->badge()
                    
                    ->color(fn($record)=>
                     $record->anneeSavoirfaire->savoirfaire->competence->color
                    ?? 'info'
                ),     


               TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
                
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                ->iconButton()
                ->icon('heroicon-m-eye')
                ->modalHeading('Détail')
                ->extraAttributes([
                    'title' => 'Consulter',
                    'class' => 'btn-consult',
                    ]),
                Tables\Actions\EditAction::make()
                ->iconButton()
                ->icon('heroicon-m-pencil-square')
                ->extraAttributes([
                    'title' => 'Modifier',
                    'class' => 'btn-modif',
                    ]),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                 //   Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListObjectifs::route('/'),
            'create' => Pages\CreateObjectif::route('/create'),
            'edit' => Pages\EditObjectif::route('/{record}/edit'),
        ];
    }
}
