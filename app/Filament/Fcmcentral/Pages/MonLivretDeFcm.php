<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Pages;

use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\FcmMarin;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Components\Livewire;
use Modules\FcmCentral\Traits\FcmPermissionTrait;
use Modules\FcmCommun\Http\Livewire\LivretFcmLight as LivretFcm;

class MonLivretDeFcm extends Page
{
    use FcmPermissionTrait;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'fcmcentral::filament.fcmcentral.pages.mon-livret-de-fcm';

    public Marin $marin;
    public ?int $marinId = null;

    public static function canAccess(array $parameters = []): bool
    {
        return static::canAccessPermission('livret.voir');
    }

    public function mount()
    {
        // Si un paramètre marinId est présent, mode mentor
        $this->marinId = request()->get('marinId');

        if ($this->marinId) {
            // Vérification mentor
            $currentMarin = Marin::fromCurrentUser();
            if (!$currentMarin) {
                abort(403, 'Votre compte n\'est pas lié à un marin.');
            }

            $marinCible = Marin::with('complementsFcm')->findOrFail($this->marinId);

            $estSupervise = FcmMarin::where('mentor_id', $currentMarin->id)
                ->where('rh_marin_id', $this->marinId)
                ->exists();

            if (!static::canAccessPermission('livret.voir') || !$estSupervise) {
                abort(403, 'Accès non autorisé à ce livret.');
            }

            $this->marin = $marinCible;
        } else {
            // Livret du marin connecté
            $this->marin = Marin::fromCurrentUser();
            if (!$this->marin) {
                abort(403, 'Aucun marin associé à votre compte.');
            }
        }

        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Livewire::make(LivretFcm::class, [
                "marinId" => $this->marin->id,
                "Marin" => Marin::class,
                "UserGeneratedEvent" => UserGeneratedEvent::class,
                "PermissionPrefix" => "fcmcentral"
            ]),
        ]);
    }

    public function getTitle(): string
    {
        return "Livret FCM de {$this->marin->nom} {$this->marin->prenom}";
    }
}

