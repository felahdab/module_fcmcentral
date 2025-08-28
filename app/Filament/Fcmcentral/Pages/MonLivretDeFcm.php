<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Pages;

use Modules\FcmCentral\Models\Marin;

use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Components\Livewire;

use Modules\FcmCommun\Http\Livewire\LivretFcmLight as LivretFcm;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages\LivretDeFcmLight as LivretDeFcm;

class MonLivretDeFcm extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'fcmcentral::filament.fcmcentral.pages.mon-livret-de-fcm';

    public Marin $marin;

    public static function canAccess(array $parameters = []): bool
    {
        $marin =  Marin::fromCurrentUser();
        // Verification que le marin exsite ET qu'il est suivi en FCM ET qu'ila un parcours
        return  $marin != null && 
                $marin->suivi_en_fcm &&
                $marin->complements_fcm !== null &&
                $marin->complements_fcm->donnees_du_parcours !== null &&
                $marin->complements_fcm->donnees_du_parcours->parcoursmarin !==null
                ;
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
                "marin" => $this->marin,
            ]),
        ]);
    }

}
