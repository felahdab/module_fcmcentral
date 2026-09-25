<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\NoteResource;
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
            ->modalWidth('4xl')
            ->modalSubmitActionLabel('Créer la note')
            ->modalCancelActionLabel('Annuler')
            ->schema([
                Placeholder::make('marin_selected')
                    ->label('Marin concerné')
                    ->content(fn ($record) => $record->grade?->libelle_court.' '.$record->prenom.' '.$record->nom),

                RichEditor::make('libelle')
                    ->required()
                    ->label('Contenu de la note')
                    ->toolbarButtons(NoteResource::TOOLBAR_LIBELLE)
                    ->extraInputAttributes(['style' => 'min-height: 300px']),

                DatePicker::make('date')
                    ->label('Date')
                    ->default(now())
                    ->nullable(),

                Hidden::make('redacteur')
                    ->default(fn () => auth()->user()?->display_name)
                    ->required(),

                TextInput::make('lienurl')
                    ->label('Lien URL (optionnel)')
                    ->url()
                    ->nullable()
                    ->placeholder('https://...'),
            ])
            ->action(function ($record, array $data) {
                try {
                    Note::create([
                        'libelle' => $data['libelle'],
                        'rh_marin_id' => $record->id,
                        'date' => $data['date'] ?? now(),
                        'redacteur' => $data['redacteur'],
                        'valide_bcf' => false,
                        'lienurl' => $data['lienurl'] ?? null,
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
