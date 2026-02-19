<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\NoteResource\Pages\ListNotes;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\NoteResource\Pages\CreateNote;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\NoteResource\Pages\EditNote;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\NoteResource\Pages;
use Modules\FcmCentral\Models\Note;
use Modules\FcmCentral\Models\TypeNote;
use Modules\FcmCentral\Models\Marin;

 
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NoteResource extends Resource
{
    protected static ?string $model = Note::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-group';
    protected static string | \UnitEnum | null $navigationGroup = "Gestion";


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

                Group::make([
                    Select::make('type_note_id')
                        ->label('Type de note')
                        ->relationship('typeNote', 'libelle_court')
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->createOptionForm([
                            TextInput::make('code')
                                ->required()
                                ->label('code')
                                ->maxLength(150),
                            Textarea::make('libelle_court')
                                ->required()
                                ->label('Libellé')
                                ->rows(3)
                                ->maxLength(150),
                        ])
                        ->createOptionUsing(function (array $data) {
                            return TypeNote::create($data)->id;
                        }),
                ])->columnSpan(1),

                DatePicker::make('date')
                    ->label('Date')
                    ->default(now())
                    ->nullable(),

                Select::make('redacteur_id')
                    ->label('Rédacteur')
                    ->relationship('redacteur', 'nom')
                    ->searchable()
                    ->preload()
                    ->required(),

                Toggle::make('valide_bcf')
                    ->label('Validé BCF')
                    ->default(false),

                TextInput::make('lien_url')
                    ->label('Lien URL')
                    ->url()
                    ->nullable(),

                // Champs cachés pour objet_type et objet_id
                Hidden::make('objet_type'),
                Hidden::make('objet_id')
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),

                    TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('libelle')
                    ->searchable()
                    ->sortable()
                    ->label('Libellé')
                    ->limit(100)
                    ->wrap(),
                    

                TextColumn::make('marin.nom')
                    ->label('Marin')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('typeNote.libelle_court')
                    ->label('Type')
                    ->searchable()
                    ->sortable()
                    ->badge(),

                TextColumn::make('date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('redacteur.nom')
                    ->label('Rédacteur')
                    ->searchable()
                    ->toggleable(),

                IconColumn::make('valide_bcf')
                    ->label('Validé BCF')
                    ->boolean()
                    ->toggleable(),

                TextColumn::make('lien_url')
                    ->label('Lien')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->url(fn ($record) => $record->lien_url)
                    ->openUrlInNewTab()
                    ->limit(30),
            ])
            ->filters([
                SelectFilter::make('type_note_id')
                    ->label('Type de note')
                    ->relationship('typeNote', 'libelle_court'),

                SelectFilter::make('marin_id')
                    ->label('Marin')
                    ->relationship('marin', 'nom')
                    ->searchable(),

                SelectFilter::make('redacteur_id')
                    ->label('Rédacteur')
                    ->relationship('redacteur', 'nom')
                    ->searchable(),

                TernaryFilter::make('valide_bcf')
                    ->label('Validé BCF'),

                Filter::make('date')
                    ->schema([
                        DatePicker::make('date_from')
                            ->label('Date de début'),
                        DatePicker::make('date_to')
                            ->label('Date de fin'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['date_from'], fn ($query) => $query->whereDate('date', '>=', $data['date_from']))
                            ->when($data['date_to'], fn ($query) => $query->whereDate('date', '<=', $data['date_to']));
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNotes::route('/'),
            'create' => CreateNote::route('/create'),
            'edit' => EditNote::route('/{record}/edit'),
            //'view' => Pages\ViewNote::route('/{record}'),
        ];
    }
}
