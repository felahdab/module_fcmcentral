<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages;

use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCommun\Http\Livewire\LivretFcmLight as LivretFcm;

class LivretDeFcmLight extends ViewRecord
{
    protected static string $resource = MarinResource::class;
    protected static ?string $title = 'Livret du marin';

    /** La neutralisation des méthodes authorizeRecourseAccess et authorizeAccess renvoie tout le controle d'accès à la méthode
     *  canAccess. Le comportement de la page est alors identique aux pages non intégrées à des Ressource.
     */
    public static function authorizeResourceAccess(): void {}

    public static function canAccess(array $parameters = []): bool
    {
        $marin_a_voir = $parameters['record'];

        if (null == $marin_a_voir->complementsFcm?->donnees_du_parcours) {
            return false;
        }

        $marin = Marin::fromCurrentUser();

        if (null != $marin) {
            /** L'utilisateur a une fiche de Marin. Il peut donc peut-être être mentor. */
            $marin = cast_as_eloquent_descendant($marin, Marin::class);

            if ($marin->est_mentor_de($marin_a_voir)) {
                return true;
            }
        }

        /* A ce moment là, on fait un test basé sur la resource pour préserver le rôle des permissions liées à la Policy associée au
         * modèle Marin pour l'accès à cette page. On le fait ici par exclusion: si l'utilisateur n'a pas la permission, il est refusé.
         */
        if (!static::getResource()::canView($marin_a_voir)) {
            return false;
        }

        // Mais même s'il a accès à la liste des marins, il faut encore qu'il ait le droit de voir le livrets de FCM.
        // Cela permet de donner accès à la liste sans pour autant donner accès aux livrets
        if (auth()->user()->can('fcmcentral::livret.voir')) {
            return true;
        }

        return false;
    }

    public function getRelationManagers(): array
    {
        return [];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make(fn () => 'Livret de '.($this->record->nom.' '.$this->record->prenom))
                    // ->columns(4)
                    // ->columnSpanFull()
                    ->schema([
                        Livewire::make(LivretFcm::class, [
                            'marinId' => $this->record->id,
                            'Marin' => Marin::class,
                            'UserGeneratedEvent' => UserGeneratedEvent::class,
                            'PermissionPrefix' => 'fcmcentral',
                        ]),
                    ]),
            ])
        ;
    }

    protected function authorizeAccess(): void {}

    protected function getFooterWidgets(): array
    {
        return [];
    }
}
