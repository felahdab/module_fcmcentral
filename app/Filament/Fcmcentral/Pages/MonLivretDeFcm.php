<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Pages;

use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Components\Livewire;

use Modules\FcmCommun\Http\Livewire\LivretFcmLight as LivretFcm;

use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Events\UserGeneratedEvent;

class MonLivretDeFcm extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'fcmcentral::filament.fcmcentral.pages.mon-livret-de-fcm';
    protected static ?string $navigationLabel = "Mon livret FCM";

    public Marin $marin;
    public ?int $marinId = null;

    public static function canAccess(array $parameters = []): bool
    {
        $currentMarin = Marin::fromCurrentUser();
        if (! $currentMarin) return false;

        if ($currentMarin->complements_fcm?->donnees_du_parcours) return true;
        
        return false;
    }

    public function mount()
    {
        $currentMarin = Marin::fromCurrentUser();
        if (!$currentMarin) {
            abort(403, 'Votre compte n\'est pas lié à un marin.');
        }

        $this->marin = $currentMarin;

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

