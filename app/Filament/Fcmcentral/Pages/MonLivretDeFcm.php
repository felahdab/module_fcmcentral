<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Pages;

use Filament\Pages\Page;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Schema;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCommun\Http\Livewire\LivretFcmLight as LivretFcm;

class MonLivretDeFcm extends Page
{
    public Marin $marin;
    public ?int $marinId = null;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-text';
    protected string $view = 'fcmcentral::filament.fcmcentral.pages.mon-livret-de-fcm';

    public static function canAccess(array $parameters = []): bool
    {
        $currentMarin = Marin::fromCurrentUser();
        if (!$currentMarin) {
            return false;
        }

        if ($currentMarin->complements_fcm?->donnees_du_parcours) {
            return true;
        }

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

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Livewire::make(LivretFcm::class, [
                    'marinId' => $this->marin->id,
                    'Marin' => Marin::class,
                    'UserGeneratedEvent' => UserGeneratedEvent::class,
                    'PermissionPrefix' => 'fcmcentral',
                ]),
            ])
        ;
    }

    public function getTitle(): string
    {
        return "Livret  FCM de {$this->marin->nom} {$this->marin->prenom}";
    }
}
