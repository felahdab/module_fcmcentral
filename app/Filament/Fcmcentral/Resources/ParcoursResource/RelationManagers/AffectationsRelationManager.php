<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Modules\FcmCentral\Models\Affectation;


class AffectationsRelationManager extends RelationManager
{
    protected static string $relationship = 'affectations';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select ::make('recordId')
                ->label('Années')
                ->otpions(Affectation::all()->pluck('code','id'))
                ->searchable()
                ->required()
                ->disabled(fn ($context): bool => $context === 'edit'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('libelle_court')
            ->defaultSort('fcmcentral_affectation_parcours.ordre','asc')
            ->reorderable('fcmcentral_affectation_parcours.ordre') 
            ->columns([
                TextColumn::make('libelle_court')
                ->label('Libellé'),
                
                TextColumn::make('pivot.ordre')
                ->label('Ordre')
                ->numeric(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                ->color('primary')
                ->form(fn (AttachAction $action): array => [
                    $action->getRecordSelect(),
                    TextInput::make('pivot.ordre')
                        ->label('Ordre')
                        ->required()
                        ->numeric(),     
                ])
            ])
            ->recordActions([
                // Tables\Actions\EditAction::make()
                //     ->iconButton()
                //     ->icon('heroicon-m-pencil-square')
                //     ->extraAttributes([
                //         'title' => 'Modifier',
                //         'class' => 'btn-modif',
                //         ])
                //     ->form([
                //         Forms\Components\TextInput::make('ordre')
                //         ->label('Ordre')
                //         ->required()
                //         ->numeric(),
                //     ]),
                DetachAction::make()
                    ->iconButton()
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->extraAttributes([
                        'title' => 'Détacher',
                    ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                  //  Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
