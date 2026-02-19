<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\GelResource\Pages\ListGels;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\GelResource\Pages\CreateGel;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\GelResource\Pages\EditGel;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\GelResource\Pages;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\GelResource\RelationManagers;
use Modules\FcmCentral\Models\Etat;
use Modules\FcmCentral\Models\TypeEtat;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
 

class GelResource extends Resource
{
    protected static ?string $model = Etat::class;
    protected static ?string $navigationLabel = "Gels" ;
    protected static ?string $label = "Gel" ;
    protected static ?string $plurialLabel = "Gels" ;

    protected static string | \UnitEnum | null $navigationGroup = "Gestion";
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-group';


    public static function form(Schema $schema): Schema
    {
        return $schema
        ->components([
            Textarea::make('libelle')
                ->required()
                ->label('Libellé')
                ->rows(4)
                ->maxLength(1000),

            Select::make('marin_id')
                ->label('Marin')
                ->relationship('marin', 'nom')
                ->searchable()
                ->preload()
                ->required(),

                Select::make('type_etat_id')
                ->label('Type Gel')
                ->relationship('typeEtat', 'libelle_court')
                ->searchable()
                ->preload()
                ->nullable()
                ->createOptionForm([
                    TextInput::make('libelle_court')
                        ->required()
                        ->label('Libellé court')
                        ->maxLength(150),
                    TextInput::make('code')
                        ->required()
                        ->label('Code')
                        ->maxLength(50),
                ])
                ->createOptionUsing(function (array $data) {
                    return TypeEtat::create($data)->id;
                }),
            
            

            DatePicker::make('date_debut')
            ->label('Debut')
            ->default(now())
            ->nullable(),


            DatePicker::make('date_fin')
            ->label('Fin')
            ->default(now())
            ->nullable(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->defaultSort('date_debut', 'desc')
        ->columns([
            TextColumn::make('id')
                ->label('ID')
                ->toggleable(isToggledHiddenByDefault: true)
                ->searchable(),

           

            TextColumn::make('marin.nom')
                ->label('Marin')
                ->searchable()
                ->sortable(),

            TextColumn::make('typeEtat.libelle_court')
                ->label('Type')
                ->searchable()
                ->sortable()
                ->badge(),

            TextColumn::make('date_debut')
                ->label('Debut')
                ->date('d/m/Y')
                ->sortable(),

            TextColumn::make('date_fin')
                ->label('Fin')
                ->date('d/m/Y')
                ->sortable(),    
                //
            ])
            ->filters([
                SelectFilter::make('type_etat_id')
                ->label('Type Gel')
                ->relationship('typeEtat', 'libelle_court'),

            SelectFilter::make('marin_id')
                ->label('Marin')
                ->relationship('marin', 'nom')
                ->searchable(),

            

            

            Filter::make('date_debut')
                ->schema([
                    DatePicker::make('date_from')
                        ->label('Date de début'),
                    DatePicker::make('date_to')
                        ->label('Date de fin'),
                ])
                ->query(function ($query, array $data) {
                    return $query
                        ->when($data['date_from'], fn ($query) => $query->whereDate('date_debut', '>=', $data['date_from']))
                        ->when($data['date_to'], fn ($query) => $query->whereDate('date_debut', '<=', $data['date_to']));
                }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => ListGels::route('/'),
            'create' => CreateGel::route('/create'),
            'edit' => EditGel::route('/{record}/edit'),
        ];
    }
}
