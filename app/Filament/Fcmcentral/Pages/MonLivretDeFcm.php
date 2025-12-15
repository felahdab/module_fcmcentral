<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Pages;

use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Events\UserGeneratedEvent;

use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Components\Livewire;
use Modules\FcmCentral\Traits\FcmPermissionTrait;

use Modules\FcmCommun\Http\Livewire\LivretFcmLight as LivretFcm;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages\LivretDeFcmLight as LivretDeFcm;


class MonLivretDeFcm extends Page
{
   
    use FcmPermissionTrait;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'fcmcentral::filament.fcmcentral.pages.mon-livret-de-fcm';

    public Marin $marin;

    public static function canAccess(array $parameters = []): bool
    {
        return static::canAccessPermission('livret.voir');
    }

    public function mount()
    {
        $this->marin = Marin::fromCurrentUser();
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

}
