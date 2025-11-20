<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\EtatResource\Pages;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\EtatResource\RelationManagers;
use Modules\FcmCentral\Models\Etat;
use Modules\FcmCentral\Models\TypeEtat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Modules\FcmCentral\Traits\FilamentPermissionTrait;

class EtatResource extends Resource
{
    
    use FilamentPermissionTrait;

    protected static ?string $model = Etat::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

     // Permissions marche avec le Trait
     protected static array $permissions = [
        'fcmcentral::admin',
        'fcmcentral::permission.fps',
        'fcmcentral::permission.bcf',
    ];

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Textarea::make('libelle')
                ->required()
                ->label('Libellé')
                ->rows(4)
                ->maxLength(1000),

            Forms\Components\Select::make('marin_id')
                ->label('Marin')
                ->relationship('marin', 'nom')
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\Group::make([
                Forms\Components\Select::make('type_etat_id')
                    ->label('Type Etat')
                    ->relationship('typeEtat', 'libelle_court')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('libelle_court')
                            ->required()
                            ->label('Libellé court')
                            ->maxLength(150),
                        
                    ])
                    ->createOptionUsing(function (array $data) {
                        return TypeEtat::create($data)->id;
                    }),
            ])->columnSpan(1),


            Forms\Components\DatePicker::make('date_debut')
            ->label('Debut')
            ->default(now())
            ->nullable(),


            Forms\Components\DatePicker::make('date_fin')
            ->label('Fin')
            ->default(now())
            ->nullable(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->defaultSort('date', 'desc')
        ->columns([
            Tables\Columns\TextColumn::make('id')
                ->label('ID')
                ->toggleable(isToggledHiddenByDefault: true)
                ->searchable(),

            Tables\Columns\TextColumn::make('libelle')
                ->searchable()
                ->sortable()
                ->label('Libellé')
                ->limit(100)
                ->wrap(),

            Tables\Columns\TextColumn::make('marin.nom')
                ->label('Marin')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('typeEtat.libelle_court')
                ->label('Type')
                ->searchable()
                ->sortable()
                ->badge(),

            Tables\Columns\TextColumn::make('date_debut')
                ->label('Debut')
                ->date('d/m/Y')
                ->sortable(),

            Tables\Columns\TextColumn::make('date_fin')
                ->label('Fin')
                ->date('d/m/Y')
                ->sortable(),    
                //
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type_etat_id')
                ->label('Type Etat')
                ->relationship('typeEtat', 'libelle_court'),

            Tables\Filters\SelectFilter::make('marin_id')
                ->label('Marin')
                ->relationship('marin', 'nom')
                ->searchable(),

            

            

            Tables\Filters\Filter::make('date_debut')
                ->form([
                    Forms\Components\DatePicker::make('date_from')
                        ->label('Date de début'),
                    Forms\Components\DatePicker::make('date_to')
                        ->label('Date de fin'),
                ])
                ->query(function ($query, array $data) {
                    return $query
                        ->when($data['date_from'], fn ($query) => $query->whereDate('date_debut', '>=', $data['date_from']))
                        ->when($data['date_to'], fn ($query) => $query->whereDate('date_debut', '<=', $data['date_to']));
                }),
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
            'index' => Pages\ListEtats::route('/'),
            'create' => Pages\CreateEtat::route('/create'),
            'edit' => Pages\EditEtat::route('/{record}/edit'),
        ];
    }
}
