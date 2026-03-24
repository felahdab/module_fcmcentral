<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursSerialiseResource\Pages\ListParcoursSerialises;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursSerialiseResource\Pages\EditParcoursSerialise;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursSerialiseResource\Pages;

use Modules\FcmCentral\Models\ParcoursSerialise;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\Filter;

use Modules\FcmCentral\Http\Livewire\ParcoursDetails;
use Filament\Forms\Components\Placeholder;
 


use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\RelationManagers\MarinsRelationManager;

class ParcoursSerialiseResource extends Resource
{
    protected static ?string $model = ParcoursSerialise::class;

    protected static string | \UnitEnum | null $navigationGroup = "Catalogue";
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-lock-closed';
    
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                
                TextInput::make('libelle_court')
                    ->required()
                    ->label('Libellé court')
                    //->maxLength(150)
                    ->disabled(),
                TextInput::make('libelle_long')
                    ->required()
                    ->label('Libellé long')
                    //->maxLength(1500)
                    ->disabled(),
                TextInput::make('version')
                    ->required()
                    ->label('Version')
                    //->maxLength(5)
                    ->default(1)
                    ->disabled(),
                DatePicker::make('date_debut')
                    ->label('Date début')
                    ->required()
                    ->disabled(),
                DatePicker::make('date_fin')
                    ->label('Date fin')
                    ->disabled(),
                // Section "Parcours Serialisés"
                Section::make('Composition du PPA')
                    ->schema([    
                        Placeholder::make('composition_ppa')
                        ->content(fn ($record) => view('fcmcentral::filament.fcmcentral.livewire.parcours.parcours-details',[
                            //'parcours' =>json_decode($record?->parcours ?? '{}', true),
                            'parcours' =>$record->parcours ,
                        ])),
                    ]),
                // Forms\Components\Textarea::make('parcours')
                // ->formatStateUsing(function ($record){
                //     return json_encode($record->parcours, JSON_PRETTY_PRINT);
                // })
                //     ->columnSpanFull()
                //     ->rows(20)
                //     ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('version')
                    ->searchable(),
                TextColumn::make('libelle_court')
                    ->searchable()
                    ->label('Libellé court'),
                TextColumn::make('libelle_long')
                    ->searchable()
                    ->label('Libellé long')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('date_debut')
                    ->date()
                    ->label('Date début')
                    ->sortable(),
                TextColumn::make('date_fin')
                    ->date()
                    ->label('Date fin')
                    ->sortable(),
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
                Filter::make('date_fin')
                ->query(fn (Builder $query): Builder => $query->whereNull('date_fin'))
                ->label('PPA en cours')
                ->default(),
            ])
            ->recordActions([
                // Tables\Actions\ViewAction::make()
                // ->iconButton()
                // ->icon('heroicon-m-eye')
                // ->modalHeading('Détail')
                // ->extraAttributes([
                //     'title' => 'Consulter',
                //     'class' => 'btn-consult',
                //     ]),

                EditAction::make()
                ->iconButton()
                ->icon('heroicon-m-eye')
                ->modalHeading('Détail')
                ->extraAttributes([
                    'title' => 'Consulter',
                    'class' => 'btn-consult',
                    ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ;
    }

    public static function getRelations(): array
    {
        return [
            MarinsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListParcoursSerialises::route('/'),
            // 'view' => Pages\ViewParcoursSerialise::route('/{record}/view'),
            // 'create' => Pages\CreateParcoursSerialise::route('/create'),
            'edit' => EditParcoursSerialise::route('/{record}/edit'),
        ];
    }
}
