<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceResource\Pages;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceResource\RelationManagers;
use Modules\FcmCentral\Models\Competence;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Modules\FcmCentral\Traits\FcmAccessControl;
use Modules\FcmCentral\Traits\FilamentPermissionTrait;



use Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceResource\RelationManagers\SavoirfairesRelationManager;
use Modules\FcmCentral\Helpers\OutilsHelper;

class CompetenceResource extends Resource
{
    use FcmAccessControl;
    use FilamentPermissionTrait;
    
    protected static ?string $model = Competence::class;

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
                Select::make('parcours_type_id')
                    ->relationship(name: 'parcoursType', titleAttribute: 'libelle_court')
                    ->label('Type de Parcours')
                    ->required(),    
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
                    ->badge()
                    ->color(fn($record)=>$record->color ?? 'info')
                    ->searchable(),       
                TextColumn::make('libelle_court')
                    ->label('Libellé court')
                    ->limit(50)
                    ->sortable()
                    ->searchable()
                    ->description(fn ($record) => substr($record->libelle_long,0,40).'...')
                    ,   
               
                    TextColumn::make('savoirfaires.code')
                    ->label('Savoir Faire')
                    ->badge()
                    ->color(fn($record)=>$record->savoirfaires->first()->color ?? $record->color ?? 'info'),    
                TextColumn::make('parcoursType.libelle_court')
                    ->label('Type Parcours')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ordre')
                
                   ,    
                // TextColumn::make('created_at')
                //     ->date()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
                // TextColumn::make('updated_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
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
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }

    public static function getEloquentQuery() : Builder
    {
        return parent::getEloquentQuery()->withCount('savoirfaires');
    }

    public static function getRelations(): array
    {
        return [
            //SavoirfairesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompetences::route('/'),
            'create' => Pages\CreateCompetence::route('/create'),
            'edit' => Pages\EditCompetence::route('/{record}/edit'),
        ];
    }
}
