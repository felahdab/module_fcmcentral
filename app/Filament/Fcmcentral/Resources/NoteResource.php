<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\NoteResource\Pages;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\NoteResource\Pages\CreateNote;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\NoteResource\Pages\EditNote;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\NoteResource\Pages\ListNotes;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\Note;
use Modules\FcmCentral\Models\TypeNote;

class NoteResource extends Resource
{
    /** Barre d'outils de l'éditeur riche du libellé (partagée avec le modal de création depuis la liste des marins). */
    public const TOOLBAR_LIBELLE = [
        ['bold', 'italic', 'underline', 'strike', 'highlight'],
        ['h2', 'h3'],
        ['alignStart', 'alignCenter', 'alignEnd'],
        ['bulletList', 'orderedList', 'blockquote', 'horizontalRule'],
        ['link', 'clearFormatting'],
        ['undo', 'redo'],
    ];

    protected static ?string $model = Note::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-user-group';
    protected static string|\UnitEnum|null $navigationGroup = 'Gestion';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                RichEditor::make('libelle')
                    ->required()
                    ->label('Libellé')
                    ->toolbarButtons(self::TOOLBAR_LIBELLE)
                    ->extraInputAttributes(['style' => 'min-height: 300px'])
                    ->columnSpanFull(),

                Select::make('rh_marin_id')
                    ->label('Marin')
                    ->relationship('marin', 'nom')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->disabled(fn () => filled(request()->query('marin_id')))
                    ->dehydrated(),

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
                            // ->maxLength(150)
                            ,
                            Textarea::make('libelle_court')
                                ->required()
                                ->label('Libellé')
                                ->rows(3)
                            // ->maxLength(150)
                            ,
                        ])
                        ->createOptionUsing(function (array $data) {
                            return TypeNote::create($data)->id;
                        }),
                ])->columnSpan(1),

                DatePicker::make('date')
                    ->label('Date')
                    ->default(now())
                    ->nullable(),

                Select::make('redacteur')
                    ->label('Rédacteur')
                    ->options(fn (?Note $record) => self::redacteurOptions($record?->redacteur))
                    ->searchable()
                    ->default(fn () => auth()->user()?->display_name)
                    ->required(),

                Toggle::make('valide_bcf')
                    ->label('Validé BCF')
                    ->default(false),

                TextInput::make('lienurl')
                    ->label('Lien URL')
                    ->url()
                    ->maxLength(255)
                    ->nullable(),

                // Champs cachés pour objet_type et objet_id
                Hidden::make('objet_type'),
                Hidden::make('objet_id')
                    ->default(0),
            ])
        ;
    }

    /**
     * Options du rédacteur, indexées par le libellé stocké en base (colonne texte).
     * On y ajoute l'utilisateur connecté et la valeur actuelle de la note, pour qu'elle
     * reste sélectionnée en édition même si elle ne correspond à aucun marin.
     */
    public static function redacteurOptions(?string $current = null): array
    {
        $options = Marin::with('grade')
            ->orderBy('nom')
            ->get()
            ->map(fn (Marin $marin) => trim($marin->grade?->libelle_court.' '.$marin->prenom.' '.$marin->nom))
            ->filter()
            ->mapWithKeys(fn (string $libelle) => [$libelle => $libelle])
            ->all();

        foreach ([auth()->user()?->display_name, $current] as $valeur) {
            if (filled($valeur) && ! isset($options[$valeur])) {
                $options = [$valeur => $valeur] + $options;
            }
        }

        return $options;
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
                    ->html()
                    ->limit(150)
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

                TextColumn::make('redacteur')
                    ->label('Rédacteur')
                    ->searchable()
                    ->toggleable(),

                IconColumn::make('valide_bcf')
                    ->label('Validé BCF')
                    ->boolean()
                    ->toggleable(),

                TextColumn::make('lienurl')
                    ->label('Lien')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->url(fn ($record) => $record->lienurl)
                    ->openUrlInNewTab()
                // ->limit(30)
                ,
            ])
            ->filters([
                SelectFilter::make('type_note_id')
                    ->label('Type de note')
                    ->relationship('typeNote', 'libelle_court'),

                SelectFilter::make('marin_id')
                    ->label('Marin')
                    ->relationship('marin', 'nom')
                    ->searchable(),

                // SelectFilter::make('redacteur')
                //     ->label('Rédacteur')
                //     ->options(fn () => Note::query()->whereNotNull('redacteur')->distinct()->orderBy('redacteur')->pluck('redacteur', 'redacteur')->all())
                //     ->searchable(),

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
                            ->when($data['date_to'], fn ($query) => $query->whereDate('date', '<=', $data['date_to']))
                        ;
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
            ])
        ;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNotes::route('/'),
            'create' => CreateNote::route('/create'),
            'edit' => EditNote::route('/{record}/edit'),
            // 'view' => Pages\ViewNote::route('/{record}'),
        ];
    }
}
