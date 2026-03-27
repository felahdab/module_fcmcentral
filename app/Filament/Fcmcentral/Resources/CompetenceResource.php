<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceResource\Pages\CreateCompetence;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceResource\Pages\EditCompetence;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceResource\Pages\ListCompetences;
use Modules\FcmCentral\Helpers\OutilsHelper;
use Modules\FcmCentral\Models\Competence;

class CompetenceResource extends Resource
{
    protected static ?string $model = Competence::class;
    protected static string|\UnitEnum|null $navigationGroup = 'Catalogue';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-star';
    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('libelle_court')
                    ->required()
                    ->label('Libellé court')
                    ->maxLength(500),
                TextInput::make('libelle_long')
                    ->required()
                    ->label('Libellé long')
                // ->maxLength(1500)
                ,
                TextInput::make('code')
                    ->required()
                    ->label('Code')
                // ->maxLength(150)
                ,
                Select::make('parcours_type_id')
                    ->relationship(name: 'parcoursType', titleAttribute: 'libelle_court')
                    ->label('Type de Parcours')
                    ->required(),
                Select::make('color')
                    ->options(OutilsHelper::selectColors())
                    ->label('Couleur du Badge'),
            ])
        ;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('ordre', 'asc')
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
                    ->color(fn ($record) => $record->color ?? 'info')
                    ->searchable(),
                TextColumn::make('libelle_court')
                    ->label('Libellé court')
                    // ->limit(50)
                    ->sortable()
                    ->searchable()
                    ->description(fn ($record) => substr($record->libelle_long, 0, 40).'...'),

                TextColumn::make('savoirfaires.code')
                    ->label('Savoir Faire')
                    ->badge()
                    ->color(fn ($record) => $record->savoirfaires->first()->color ?? $record->color ?? 'info'),
                TextColumn::make('parcoursType.libelle_court')
                    ->label('Type Parcours')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ordre'),
                // TextColumn::make('created_at')
                //     ->date()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
                // TextColumn::make('updated_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                // ExportAction::make()
                //     ->exporter(CompetenceExporter::class)
                //     ->label('Exportation data'),
                // ImportAction::make()
                // ->importer(CompetenceImporter::class)
                // ->label('Importation data'),
            ])

            ->filters([
            ])
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->icon('heroicon-m-eye')
                    ->modalHeading('Détail')
                    ->extraAttributes([
                        'title' => 'Consulter',
                        'class' => 'btn-consult',
                    ]),
                EditAction::make()
                    ->iconButton()
                    ->icon('heroicon-m-pencil-square')
                    ->extraAttributes([
                        'title' => 'Modifier',
                        'class' => 'btn-modif',
                    ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
        ;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('savoirfaires');
    }

    public static function getRelations(): array
    {
        return [
            // SavoirfairesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCompetences::route('/'),
            'create' => CreateCompetence::route('/create'),
            'edit' => EditCompetence::route('/{record}/edit'),
        ];
    }
}
