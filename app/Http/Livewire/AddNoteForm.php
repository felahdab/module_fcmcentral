<?php

namespace Modules\FcmCentral\Http\Livewire;

use Log;
use Exception;
use Livewire\Component;
use Modules\FcmCentral\Models\Note;
use Modules\FcmCentral\Models\TypeNote;
use Modules\FcmCentral\Models\Marin;
use Filament\Notifications\Notification;

class AddNoteForm extends Component
{
    public $marinId;
    public $typeNote = '';
    public $libelle = '';
    public $redacteurId = '';
    public $date;
    public $lienUrl = '';
    public $valide_bcf = false;
   
    protected $rules = [
        'typeNote' => 'required|exists:fcmcentral_type_notes,id', // ✅ Corriger le nom de table
        'libelle' => 'required|min:10|max:1000',
        'date' => 'required|date|before_or_equal:today',
        'lienUrl' => 'nullable|url|max:255',
        'valide_bcf' => 'boolean',
    ];

    protected $messages = [
        'typeNote.required' => 'Le type de note est obligatoire.',
        'typeNote.exists' => 'Le type de note sélectionné n\'existe pas.',
        'libelle.required' => 'Le contenu de la note est obligatoire.',
        'libelle.min' => 'Le contenu doit contenir au moins 10 caractères.',
        'libelle.max' => 'Le contenu ne peut pas dépasser 1000 caractères.',
        'date.required' => 'La date est obligatoire.',
        'date.date' => 'La date doit être valide.',
        'date.before_or_equal' => 'La date ne peut pas être dans le futur.',
        'lienUrl.url' => 'Le lien doit être une URL valide.',
        'lienUrl.max' => 'Le lien ne peut pas dépasser 255 caractères.',
    ];

    public function mount($marinId)
    {
        $this->marinId = $marinId;
        $this->date = now()->format('Y-m-d');
       
        if (!Marin::find($marinId)) {
            abort(404, 'Marin introuvable');
        }
    }

    public function save()
    {
        $this->validate();

        try {
            // ✅ Ajouter des logs pour déboguer
            Log::info('Tentative de création de note', [
                'rh_marin_id' => $this->marinId,
                'type_note_id' => $this->typeNote,
                'user_id' => auth()->id(),
                'redacteur_id' => $this->redacteurId,
                'data' => [
                    'libelle' => $this->libelle,
                    'date' => $this->date,
                    'lien_url' => $this->lienUrl,
                    'valide_bcf' => $this->valide_bcf,
                ]
            ]);

            $note = Note::create([
                'rh_marin_id' => $this->marinId,
                'type_note_id' => $this->typeNote,
                'libelle' => trim($this->libelle),
                'date' => $this->date,
                'lien_url' => $this->lienUrl ?: null,
                //'redacteur_id' => auth()->id(),
                'redacteur_id' => $this->redacteurId,
                'valide_bcf' => $this->valide_bcf,
            ]);

            Log::info('Note créée avec succès', ['note_id' => $note->id]);

            $this->reset(['typeNote', 'libelle', 'lienUrl', 'valide_bcf']);
            $this->date = now()->format('Y-m-d');
        
            $this->dispatch('note-added');
            $this->dispatch('close-modal', id: 'ajouterNote');
        
            Notification::make()
                ->title('Note ajoutée avec succès!')
                ->success()
                ->send();

        } catch (Exception $e) {
            // ✅ Log détaillé de l'erreur
            Log::error('Erreur lors de l\'ajout de la note', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'data' => [
                    'rh_marin_id' => $this->marinId,
                    'type_note_id' => $this->typeNote,
                    'user_id' => auth()->id(),
                ]
            ]);
        
            Notification::make()
                ->title('Erreur lors de l\'ajout de la note')
                ->body('Détail : ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }


    public function cancel()
    {
        $this->reset(['typeNote', 'libelle', 'lienUrl', 'valide_bcf']);
        $this->date = now()->format('Y-m-d');
        $this->dispatch('close-modal', id: 'ajouterNote');
    }

    public function render()
    {
        $typesNotes = TypeNote::orderBy('libelle_court')->get();
        $marin = Marin::with(['grade'])->find($this->marinId);

        // Ajout : récupération des rédacteurs
        $redacteurs = Marin::orderBy('nom')
            ->get()
            ->mapWithKeys(function ($marin) {
                return [$marin->id => $marin->grade?->libelle_court . ' ' . $marin->prenom . ' ' . $marin->nom];
            })
            ->toArray();

        return view('fcmcentral::filament.fcmcentral.livewire.add-note-form', [
            'typesNotes' => $typesNotes,
            'marin' => $marin,
            'redacteurs' => $redacteurs, // ✅ Passe la variable à la vue
        ]);
    }


}
