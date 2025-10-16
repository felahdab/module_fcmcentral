<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\AnneeResource\Pages;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\AnneeResource\RelationManagers;
use Modules\FcmCentral\Models\Annee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\AnneeResource\RelationManagers\SavoirfairesRelationManager;
use Modules\FcmCentral\Traits\FcmAccessControl;
use Modules\FcmCentral\Traits\FilamentPermissionTrait;

use Modules\FcmCentral\Helpers\OutilsHelper;


class AnneeResource extends Resource
{
    use FcmAccessControl;
    use FilamentPermissionTrait;
    
    protected static ?string $model = Annee::class;

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
                    ->maxLength(150)
                    ->label('Libellé court'),
                TextInput::make('libelle_long')
                    ->required()
                    ->maxLength(1500)
                    ->label('Libellé long'),
                TextInput::make('code')
                    ->label('code')  
                    ->required()                 
                    ->maxLength(1000),  
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
            ->columns([
               TextColumn::make('id')
                    ->label('ID')
                    ->toggleable(isToggledHiddenByDefault: true),
                    TextColumn::make('code')
                    ->label('Code')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color(fn($record)=>$record->color ??   'info') 
                    ,      
                TextColumn::make('libelle_court')
                    ->searchable()
                    ->sortable()
                    ->label('Libellé court'),
                    TextColumn::make('savoirfaires_count')
                    ->counts('savoirfaires')
                    ->label('Nb Savoir-faire')
                    ->badge()
                    ->color('info'),   
                TextColumn::make('savoirfaires.code')
                    ->label('Savoir Faire')
                    ->badge()
                    
                    ->color(fn($record)=>
                    $record->savoirfaires->first()->color
                    ??  $record->savoirfaires->first()->competence->color
                    ?? 'info'
                ),    

                // TextColumn::make('savoirfairescodes')
                //     ->label('Savoirs-Faire')
                //     ->formatStateUsing(function ($state, $record) {
                //         return $record->savoirfaires->map(function ($sf) {
                //             $color = $sf->color ?? $sf->competence?->color ?? 'info';
                //             $type = $sf->pivot?->savoirfaireType?->libelle_court ?? '';
                //             return "<span style='color:{$color};'>{$sf->competence?->code}-{$sf->code}" . ($type ? " ({$type})" : "") . "</span>";
                //         })->implode(', ');
                //     })
                //     ->html(),
                    

                // TextColumn::make('libelle_long')
                //     ->searchable()
                //     ->sortable()
                //     ->label('Libellé long')
                //     ->toggleable(isToggledHiddenByDefault: true),
                // TextColumn::make('url')
                //     ->formatStateUsing(function ($state) {
                //         return strlen($state) > 20 ? substr($state, 0, 20) . '...' : $state;
                //     })
                //     ->tooltip(fn ($state) => $state)                
                //     ->label('URL'),
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
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            SavoirfairesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnnees::route('/'),
            'create' => Pages\CreateAnnee::route('/create'),
            'edit' => Pages\EditAnnee::route('/{record}/edit'),
        ];
    }
}
