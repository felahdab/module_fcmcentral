<?php

namespace Modules\FcmCentral\Http\Livewire;

use Carbon\Carbon;
use Filament\Notifications\Notification;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Modules\FcmCentral\Models\Etat;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\TypeEtat;

class AddEtatForm extends Component
{
    public $marinId;
    public $typeEtatId = '';
    public $dateDebut;
    public $dateFin;
    public $etatId;

    protected $listeners = [
        'loadEtatForEdit' => 'loadEtat',
    ];

    protected $rules = [
        'typeEtatId' => 'required|exists:fcmcentral_type_etats,id',
        'dateDebut' => 'required|date',
        'dateFin' => 'nullable|date|after_or_equal:dateDebut',
    ];

    protected $messages = [
        'typeEtatId.required' => 'Le type d\'état est obligatoire.',
        'typeEtatId.exists' => 'Le type d\'état sélectionné n\'existe pas.',
        'dateDebut.required' => 'La date de début est obligatoire.',
        'dateDebut.date' => 'La date de début doit être valide.',
        'dateFin.date' => 'La date de fin doit être valide.',
        'dateFin.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début.',
    ];

    #[Computed]
    public function canCreateEtat(): bool
    {
        return auth()->user()->can('fcmcentral::etat.create');
    }

    public function mount($marinId, $etatId = null)
    {
        $this->marinId = $marinId;
        $this->dateDebut = now()->format('Y-m-d');

        if (!Marin::find($marinId)) {
            abort(404, 'Marin introuvable');
        }

        if (!$this->canCreateEtat) {
            abort(403, 'Vous n\'avez pas les droits pour créer/modifier un état gel');
        }
    }

    public function loadEtat($etatId)
    {
        $this->etatId = $etatId;
        $etat = Etat::find($etatId);

        if ($etat && $etat->rh_marin_id == $this->marinId) {
            $this->typeEtatId = $etat->type_etat_id;
            $this->dateDebut = $etat->date_debut ? Carbon::parse($etat->date_debut)->format('Y-m-d') : now()->format('Y-m-d');
            $this->dateFin = $etat->date_fin ? Carbon::parse($etat->date_fin)->format('Y-m-d') : null;
        }
    }

    public function cancel()
    {
        $this->reset(['typeEtatId', 'dateDebut', 'dateFin', 'etatId']);
        $this->dateDebut = now()->format('Y-m-d');
        $this->dispatch('close-modal', id: 'ajouterEtat');
    }

    public function save()
    {
        if (!$this->canCreateEtat) {
            Notification::make()
                ->title('Accès refusé')
                ->body('Vous n\'avez pas les droits pour effectuer cette action')
                ->danger()
                ->send()
            ;

            return;
        }

        $this->validate();

        try {
            $marin = Marin::find($this->marinId);

            if ($this->etatId) {
                // Modification
                $etat = Etat::find($this->etatId);
                $etat->update([
                    'type_etat_id' => $this->typeEtatId,
                    'date_debut' => $this->dateDebut,
                    'date_fin' => $this->dateFin,
                ]);

                \Log::info('État Gel modifié', ['etat_id' => $etat->id]);

                Notification::make()
                    ->title('État Gel modifié avec succès!')
                    ->success()
                    ->send()
                ;
            } else {
                // Création
                $etatActuel = $marin->etats()->latest('date_debut')->first();
                if ($etatActuel && !$etatActuel->date_fin) {
                    $etatActuel->update(['date_fin' => $this->dateDebut]);
                }

                $typeEtat = TypeEtat::find($this->typeEtatId);
                $marin->historiserEtat(
                    $typeEtat->code,
                    Carbon::parse($this->dateDebut),
                    $this->dateFin ? Carbon::parse($this->dateFin) : null
                );

                \Log::info('Nouvel État Gel créé', ['marin_id' => $this->marinId]);

                Notification::make()
                    ->title('Etat Gel ajouté avec succès!')
                    ->success()
                    ->send()
                ;
            }

            $this->reset(['typeEtatId', 'dateDebut', 'dateFin', 'etatId']);
            $this->dateDebut = now()->format('Y-m-d');

            $this->dispatch('etat-added');
            $this->dispatch('close-modal', id: 'ajouterEtat');
        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'opération sur l\'état gel', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            Notification::make()
                ->title('Erreur lors de l\'opération')
                ->body('Détail : '.$e->getMessage())
                ->danger()
                ->send()
            ;
        }
    }

    public function render()
    {
        $typesEtats = TypeEtat::orderBy('libelle_court')->get();
        $marin = Marin::with(['grade'])->find($this->marinId);

        return view('fcmcentral::filament.fcmcentral.livewire.add-etat-form', [
            'typesEtats' => $typesEtats,
            'marin' => $marin,
        ]);
    }
}
