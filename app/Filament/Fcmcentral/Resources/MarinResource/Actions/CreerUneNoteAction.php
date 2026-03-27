<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Actions;

use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextArea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\Note;

class CreerUneNoteAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name)
            ->iconButton()
            ->icon('heroicon-m-newspaper')
            ->color('danger')
            ->extraAttributes([
                'title' => 'Créer une note',
                'class' => 'btn-note',
            ])
            ->modalHeading('Créer une note')
            ->modalDescription(fn ($record) => "Créer une note pour {$record->prenom} {$record->nom}")
            ->modalWidth('lg')
            ->requiresConfirmation()
            ->modalSubmitActionLabel('Créer la note')
            ->modalCancelActionLabel('Annuler')
            ->schema([
                TextArea::make('libelle')
                    ->required()
                    ->label('Contenu de la note')
                    ->rows(4)
                    ->maxLength(1000)
                    ->placeholder('Saisissez le contenu de votre note...'),

                Hidden::make('marin_id')
                    ->default(fn ($record) => $record->id),

                Placeholder::make('marin_selected')
                    ->label('Marin concerné')
                    ->content(fn ($record) => $record->grade?->libelle_court.' '.$record->prenom.' '.$record->nom),

                /*
                Forms\Components\Select::make('type_note_id')
                    ->label('Type de note')
                    ->options(function() {
                        return \Modules\FcmCentral\Models\TypeNote::pluck('libelle_court', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->createOptionForm([
                        Forms\Components\Textarea::make('code')
                            ->required()
                            ->label('code'),
                        Forms\Components\TextInput::make('libelle_court')
                            ->required()
                            ->label('Libellé court')
                            ->maxLength(150),
                    ])
                    ->createOptionUsing(function (array $data) {
                        return \Modules\FcmCentral\Models\TypeNote::create($data)->id;
                    }),
                */

                DatePicker::make('date')
                    ->label('Date')
                    ->default(now())
                    ->nullable(),

                /*
                Forms\Components\Select::make('redacteur_id')
                    ->label('Rédacteur')
                    ->options(function () {
                        return \Modules\FcmCentral\Models\Marin::orderBy('nom')
                            ->get()
                            ->mapWithKeys(fn($marin) => [
                                $marin->id => ($marin->grade?->libelle_court . ' ' . $marin->prenom . ' ' . $marin->nom)
                            ])
                            ->toArray();
                    })
                    ->searchable()
                    ->required()
                    ->default(function () {
                        $currentMarin = \Modules\FcmCentral\Models\Marin::fromCurrentUser();
                        return $currentMarin ? $currentMarin->id : auth()->user()->id;
                    }),
                */

                /*
                Forms\Components\Toggle::make('valide_bcf')
                    ->label('Validé BCF')
                    ->default(false)
                    ->visible(fn () => auth()->user()->can("fcmcentral::note.valider")),
                */

                TextInput::make('lien_url')
                    ->label('Lien URL (optionnel)')
                    ->url()
                    ->nullable()
                    ->placeholder('https://...'),

                Hidden::make('objet_type')
                    ->default('marin'),

                Hidden::make('objet_id')
                    ->default(fn ($record) => $record->id),
            ])
            ->action(function ($record, array $data) {
                try {
                    \Log::info('Tentative de création de note', [
                        'record_id' => $record->id,
                        'data' => $data,
                    ]);

                    // Détermination du rédacteur (user connecté)
                    $currentMarin = Marin::fromCurrentUser();
                    // $redacteurId =  auth()->user()->id;

                    $redacteur = auth()->user()->display_name;
                    // dd( $redacteur);
                    // Créer la note
                    $note = Note::create([
                        'libelle' => $data['libelle'],
                        'rh_marin_id' => $record->id,
                        // 'marin_id' => $record->complementsFcm->id,
                        'type_note_id' => $data['type_note_id'] ?? null, // Champ commenté
                        'date' => $data['date'] ?? now(),
                        // 'redacteur_id' => $redacteurId, // Utilisateur connecté
                        'redacteur' => $redacteur,
                        'valide_bcf' => false, // Champ commenté mais valeur prise en compte
                        'lien_url' => $data['lien_url'] ?? null,
                        'objet_type' => 'marin',
                        'objet_id' => $record->id,
                    ]);

                    Notification::make()
                        ->title('Note créée avec succès')
                        ->body("La note pour {$record->prenom} {$record->nom} a été créée.")
                        ->success()
                        ->send()
                    ;
                } catch (\Exception $e) {
                    \Log::error('Erreur lors de la création de la note', [
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString(),
                        'record_id' => $record->id,
                        'data' => $data,
                    ]);

                    Notification::make()
                        ->title('Erreur lors de la création')
                        ->body('Une erreur est survenue lors de la création de la note.')
                        ->danger()
                        ->send()
                    ;
                }
            })
        ;
    }
}
