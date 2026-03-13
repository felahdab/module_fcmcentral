<?php
namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\AffectationResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\CreateAction;
use Illuminate\Support\Str;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\FcmCentral\Helpers\OutilsHelper;

class CompetencesRelationManager extends RelationManager
{
    protected static string $relationship = 'competences';
    protected static ?string $title = 'Compétences';

    public function form(Schema $schema): Schema
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
                Textarea::make('libelle_long')
                    ->label('Libellé long')
                    ->rows(3),
                Select::make('parcours_type_id')
                    ->relationship('parcoursType', 'libelle_court')
                    ->label('Type de Parcours')
                    ->required(),
                Select::make('color')
                    ->options(OutilsHelper::selectColors())
                    ->label('Couleur du Badge'),
                TextInput::make('ordre')
                    ->numeric()
                    ->default(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->badge()
                    ->color(fn($record) => $record->color ?? 'info')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('libelle_court')
                    ->label('Libellé')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('parcoursType.libelle_court')
                    ->label('Type Parcours'),
                TextColumn::make('savoirfaires_count')
                    ->counts('savoirfaires')
                    ->label('SF')
                    ->badge()
                    ->color('info'),
                TextColumn::make('ordre')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Créer une compétence')
                    ->mutateDataUsing(function (array $data): array {
                        $data['uuid'] = Str::uuid()->toString();
                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('ordre')
            ->defaultSort('ordre', 'asc');
    }
}

