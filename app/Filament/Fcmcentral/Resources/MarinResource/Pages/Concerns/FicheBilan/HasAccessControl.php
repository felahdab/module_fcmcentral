<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages\Concerns\FicheBilan;

use Modules\FcmCentral\Models\Marin;

trait HasAccessControl
{
    /** La neutralisation des méthodes authorizeRecourseAccess et authorizeAccess renvoie tout le controle d'accès à la méthode
     *  canAccess. Le comportement de la page est alors identique aux pages non intégrées à des Ressource.
     */
    public static function authorizeResourceAccess(): void {}

    public static function canAccess(array $parameters = []): bool
    {
        $marin_a_voir = $parameters['record'];
        $marin = Marin::fromCurrentUser();

        if (null != $marin) {
            /** L'utilisateur a une fiche de Marin. Il peut donc peut-être être mentor. */
            $marin = cast_as_eloquent_descendant($marin, Marin::class);

            if ($marin->est_mentor_de($marin_a_voir)) {
                return true;
            }

            if ($marin->id == $marin_a_voir->id) {
                return true;
            }
        }

        /* A ce moment là, on fait un test basé sur la resource pour préserver le rôle des permissions liées à la Policy associée au
         * modèle Marin pour l'accès à cette page. On le fait ici par exclusion: si l'utilisateur n'a pas la permission, il est refusé.
         */
        if (!static::getResource()::canView($marin_a_voir)) {
            return false;
        }

        // Mais même s'il a accès à la liste des marins, il faut encore qu'il ait le droit de voir les fiches bilan.
        // Cela permet de donner accès à la liste sans pour autant donner accès à la fiche Bilan.
        if (auth()->user()->can('fcmcentral::ficheBilan.voir')) {
            return true;
        }

        return false;
    }

    protected function authorizeAccess(): void {}

    private function canValidateBCF(): bool
    {
        return auth()->user()->can('fcmcentral::note.valider');
    }
}
