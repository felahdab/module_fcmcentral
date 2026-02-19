<?php
namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\AffectationResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\FcmCentral\Helpers\OutilsHelper;

class CompetencesRelationManager extends RelationManager
{
    protected static string $relationship = 'competences';
    protected static ?string $title = 'Compétences';

    public function form(Form $form): Form
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
                Forms\Components\Textarea::make('libelle_long')
                    ->label('Libellé long')
                    ->rows(3),
                Forms\Components\Select::make('parcours_type_id')
                    ->relationship('parcoursType', 'libelle_court')
                    ->label('Type de Parcours')
                    ->required(),
                Forms\Components\Select::make('color')
                    ->options(OutilsHelper::selectColors())
                    ->label('Couleur du Badge'),
                Forms\Components\TextInput::make('ordre')
                    ->numeric()
                    ->default(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->badge()
                    ->color(fn($record) => $record->color ?? 'info')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('libelle_court')
                    ->label('Libellé')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('parcoursType.libelle_court')
                    ->label('Type Parcours'),
                Tables\Columns\TextColumn::make('savoirfaires_count')
                    ->counts('savoirfaires')
                    ->label('SF')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('ordre')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Créer une compétence')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['uuid'] = \Illuminate\Support\Str::uuid()->toString();
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('ordre')
            ->defaultSort('ordre', 'asc');
    }
}

