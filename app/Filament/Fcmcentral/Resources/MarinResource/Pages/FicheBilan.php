<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages;

use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Livewire\Attributes\Computed;
use Livewire\WithFileUploads;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages\Concerns\FicheBilan\HasAccessControl;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages\Concerns\FicheBilan\HasDocumentsAnnexes;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages\Concerns\FicheBilan\HasJalonsEtGel;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages\Concerns\FicheBilan\HasNotes;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages\Concerns\FicheBilan\HasPieceJointe;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages\Concerns\FicheBilan\HasSavoirFaireProgression;
use Modules\FcmCentral\Models\Marin;

class FicheBilan extends ViewRecord
{
    use WithFileUploads;
    use HasAccessControl;
    use HasNotes;
    use HasJalonsEtGel;
    use HasSavoirFaireProgression;
    use HasPieceJointe;
    use HasDocumentsAnnexes;

    public ?int $marinId = null;

    protected static string $resource = MarinResource::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar';
    protected string $view = 'fcmcentral::filament.fcmcentral.resources.marin-resource.pages.fiche-bilan';
    protected static ?string $title = 'Fiche Bilan';

    protected $listeners = [
        'note-added' => 'refreshNotes',
        'etat-added' => 'refreshEtats',
        'notify' => 'handleNotification',
        'openJalonModal' => 'ouvrirJalonModal',
    ];

    // ============================================
    // 🔧 LIFECYCLE METHODS
    // ============================================

    public function mount($record): void
    {
        parent::mount($record);
        $this->marinId = is_numeric($record) ? $record : $record->id;
    }

    // ============================================
    // 📊 DONNÉES DE BASE (marin / parcours)
    // ============================================

    #[Computed]
    public function marin()
    {
        return Marin::with([
            'grade',
            'specialite',
            'unite',
            'complementsFcm.cohorte',
            'complementsFcm.mentors',
            'etats.typeEtat',
        ])->find($this->marinId);
    }

    #[Computed]
    public function parcours()
    {
        if (!$this->marin->complementsFcm?->donnees_du_parcours?->parcoursmarin) {
            return null;
        }

        return $this->marin->complementsFcm->donnees_du_parcours->parcoursmarin;
    }

    public function getTitle(): string
    {
        if ($this->marin) {
            return "Fiche bilan FCM - {$this->marin->grade?->libelle_court} {$this->marin->prenom} {$this->marin->nom}";
        }

        return 'Fiche bilan FCM';
    }

    public function handleNotification($data)
    {
        Notification::make()
            ->title($data['message'])
            ->{'success' === $data['type'] ? 'success' : 'danger'}()
            ->send()
        ;
    }
}
