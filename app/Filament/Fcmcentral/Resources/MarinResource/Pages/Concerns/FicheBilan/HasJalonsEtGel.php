<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages\Concerns\FicheBilan;

use Carbon\Carbon;
use Filament\Notifications\Notification;
use Livewire\Attributes\Computed;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;

trait HasJalonsEtGel
{
    public $showJalonModal = false;
    public $jalonForm = [
        'date_sedimentation' => null,
        'date_fcm' => null,
        'date_competence' => null,
    ];

    public function canCreateEtat(): bool
    {
        return auth()->user()->can('fcmcentral::etat.create');
    }

    public function canEditJalons(): bool
    {
        return auth()->user()->can('fcmcentral::definir_jalons_de_fcm');
    }

    public function ajouterEtat()
    {
        if (!$this->canCreateEtat()) {
            Notification::make()
                ->title('Accès refusé')
                ->body('Vous n\'avez pas les droits pour créer un état')
                ->warning()
                ->send()
            ;

            return;
        }

        $this->dispatch('open-modal', id: 'ajouterEtat');
    }

    public function modifierEtat($etatId)
    {
        if (!$this->canCreateEtat()) {
            Notification::make()
                ->title('Accès refusé')
                ->body('Vous n\'avez pas les droits pour modifier un état')
                ->warning()
                ->send()
            ;

            return;
        }
        $this->dispatch('loadEtatForEdit', etatId: $etatId);
        $this->dispatch('open-modal', id: 'ajouterEtat');
    }

    #[Computed]
    public function etatActuel()
    {
        return $this->marin->getDernierTypeEtat();
    }

    public function getStatutLibelle(): string
    {
        return $this->marin->getDernierTypeEtatLibelle() ?? 'Non défini';
    }

    public function getStatutColor(): string
    {
        $code = $this->marin->getDernierTypeEtatCode();

        return match ($code) {
            'FCM' => 'text-green-600',
            'SED' => 'text-blue-600',
            'GEL' => 'text-orange-600',
            'FIN' => 'text-purple-600',
            default => 'text-gray-600'
        };
    }

    public function getStatutBgColor(): string
    {
        $code = $this->marin->getDernierTypeEtatCode();

        return match ($code) {
            'FCM' => 'bg-green-50 border-green-200',
            'SED' => 'bg-blue-50 border-blue-200',
            'GEL' => 'bg-orange-50 border-orange-200',
            'FIN' => 'bg-purple-50 border-purple-200',
            default => 'bg-gray-50 border-gray-200'
        };
    }

    public function getDureeEtat(): int
    {
        $etat = $this->etatActuel;

        if (!$etat || !$etat->date_debut) {
            return 0;
        }

        $dateDebut = Carbon::parse($etat->date_debut);
        $dateFin = $etat->date_fin ? Carbon::parse($etat->date_fin) : now();

        return $dateDebut->diffInDays($dateFin);
    }

    public function getDateDebutEtat(): ?string
    {
        $etat = $this->etatActuel;

        if (!$etat || !$etat->date_debut) {
            return null;
        }

        return Carbon::parse($etat->date_debut)->format('d/m/Y');
    }

    public function getDateFinEtat(): ?string
    {
        $etat = $this->etatActuel;

        if (!$etat || !$etat->date_fin) {
            return null;
        }

        return Carbon::parse($etat->date_fin)->format('d/m/Y');
    }

    #[Computed]
    public function historiqueEtats()
    {
        return $this->marin->etats()
            ->with('typeEtat')
            ->orderBy('date_debut', 'desc')
            ->get()
        ;
    }

    public function ouvrirJalonModal()
    {
        $fcmMarin = $this->marin->complementsFcm;

        $this->jalonForm = [
            'date_sedimentation' => $fcmMarin?->date_sedimentation
                ? Carbon::parse($fcmMarin->date_sedimentation)->format('Y-m-d')
                : null,
            'date_fcm' => $fcmMarin?->date_fcm
                ? Carbon::parse($fcmMarin->date_fcm)->format('Y-m-d')
                : null,
            'date_competence' => $fcmMarin?->date_competence
                ? Carbon::parse($fcmMarin->date_competence)->format('Y-m-d')
                : null,
        ];

        $this->showJalonModal = true;
        $this->dispatch('open-modal', id: 'jalon-modal');
    }

    public function sauvegarderJalons()
    {
        $fcmMarin = $this->marin->complementsFcm;

        if ($fcmMarin) {
            if (!empty($this->jalonForm['date_sedimentation'])) {
                event(new UserGeneratedEvent(
                    UserGeneratedEventTypes::DEFINIR_JALON_FCM,
                    auth()->user()->uuid,
                    ObjectClasses::MARIN,
                    $this->marin->nid,
                    ['date_sedimentation' => $this->jalonForm['date_sedimentation']]
                ));
            }
            if (!empty($this->jalonForm['date_fcm'])) {
                event(new UserGeneratedEvent(
                    UserGeneratedEventTypes::DEFINIR_JALON_FCM,
                    auth()->user()->uuid,
                    ObjectClasses::MARIN,
                    $this->marin->nid,
                    ['date_fcm' => $this->jalonForm['date_fcm']]
                ));
            }
            if (!empty($this->jalonForm['date_competence'])) {
                event(new UserGeneratedEvent(
                    UserGeneratedEventTypes::DEFINIR_JALON_FCM,
                    auth()->user()->uuid,
                    ObjectClasses::MARIN,
                    $this->marin->nid,
                    ['date_competence' => $this->jalonForm['date_competence']]
                ));
            }
        }
        $this->marin->refresh();
        $this->dispatch('close-modal', id: 'jalon-modal');
        $this->dispatch('$refresh');
    }

    public function getDateSedimentationFormatee(): string
    {
        $date = $this->marin->complementsFcm?->date_sedimentation;

        return $date ? Carbon::parse($date)->format('d/m/Y') : 'Non renseigné';
    }

    public function getDateFcmFormatee(): string
    {
        $date = $this->marin->complementsFcm?->date_fcm;

        return $date ? Carbon::parse($date)->format('d/m/Y') : 'Non renseigné';
    }

    public function getDateCompetenceFormatee(): string
    {
        $date = $this->marin->complementsFcm?->date_competence;

        return $date ? Carbon::parse($date)->format('d/m/Y') : 'Non renseigné';
    }

    public function refreshEtats()
    {
        $this->dispatch('$refresh');
    }

    private function calculerDureeSedimentation(): int
    {
        $debut = $this->marin->debut_sedimentation;
        $vert = $this->marin->vert_sedimentation;

        if ($debut && $vert) {
            return Carbon::parse($debut)->diffInDays(Carbon::parse($vert));
        }

        return 0;
    }

    private function calculerDureePPA(): int
    {
        $vert = $this->marin->vert_sedimentation;

        if ($vert) {
            return Carbon::parse($vert)->diffInDays(now());
        }

        return 0;
    }

    private function calculerDureeTotale(): int
    {
        $debut = $this->marin->debut_sedimentation;

        if ($debut) {
            return Carbon::parse($debut)->diffInDays(now());
        }

        return 0;
    }
}
