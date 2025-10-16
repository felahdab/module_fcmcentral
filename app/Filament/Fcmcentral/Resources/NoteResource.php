<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\NoteResource\Pages;
use Modules\FcmCentral\Models\Note;
use Modules\FcmCentral\Models\TypeNote;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Traits\FcmAccessControl;
use Modules\FcmCentral\Traits\FilamentPermissionTrait;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NoteResource extends Resource
{
    use FcmAccessControl;
    use FilamentPermissionTrait;

    protected static ?string $model = Note::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';


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
                    Forms\Components\Select::make('type_note_id')
                        ->label('Type de note')
                        ->relationship('typeNote', 'libelle_court')
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('libelle_court')
                                ->required()
                                ->label('Libellé court')
                                ->maxLength(150),
                            Forms\Components\Textarea::make('libelle_long')
                                ->required()
                                ->label('Libellé long')
                                ->rows(3)
                                ->maxLength(1500),
                        ])
                        ->createOptionUsing(function (array $data) {
                            return TypeNote::create($data)->id;
                        }),
                ])->columnSpan(1),

                Forms\Components\DatePicker::make('date')
                    ->label('Date')
                    ->default(now())
                    ->nullable(),

                Forms\Components\Select::make('redacteur_id')
                    ->label('Rédacteur')
                    ->relationship('redacteur', 'nom')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\Toggle::make('valide_bcf')
                    ->label('Validé BCF')
                    ->default(false),

                Forms\Components\TextInput::make('lien_url')
                    ->label('Lien URL')
                    ->url()
                    ->nullable(),

                // Champs cachés pour objet_type et objet_id
                Forms\Components\Hidden::make('objet_type'),
                Forms\Components\Hidden::make('objet_id')
                    ->default(0),
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

                Tables\Columns\TextColumn::make('typeNote.libelle_court')
                    ->label('Type')
                    ->searchable()
                    ->sortable()
                    ->badge(),

                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('redacteur.nom')
                    ->label('Rédacteur')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('valide_bcf')
                    ->label('Validé BCF')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('lien_url')
                    ->label('Lien')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->url(fn ($record) => $record->lien_url)
                    ->openUrlInNewTab()
                    ->limit(30),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type_note_id')
                    ->label('Type de note')
                    ->relationship('typeNote', 'libelle_court'),

                Tables\Filters\SelectFilter::make('marin_id')
                    ->label('Marin')
                    ->relationship('marin', 'nom')
                    ->searchable(),

                Tables\Filters\SelectFilter::make('redacteur_id')
                    ->label('Rédacteur')
                    ->relationship('redacteur', 'nom')
                    ->searchable(),

                Tables\Filters\TernaryFilter::make('valide_bcf')
                    ->label('Validé BCF'),

                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('Date de début'),
                        Forms\Components\DatePicker::make('date_to')
                            ->label('Date de fin'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['date_from'], fn ($query) => $query->whereDate('date', '>=', $data['date_from']))
                            ->when($data['date_to'], fn ($query) => $query->whereDate('date', '<=', $data['date_to']));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotes::route('/'),
            'create' => Pages\CreateNote::route('/create'),
            'edit' => Pages\EditNote::route('/{record}/edit'),
            //'view' => Pages\ViewNote::route('/{record}'),
        ];
    }
}
