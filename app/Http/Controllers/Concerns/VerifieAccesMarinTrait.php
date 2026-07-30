<?php

namespace Modules\FcmCentral\Http\Controllers\Concerns;

use Modules\FcmCentral\Models\Marin;

/**
 * Logique d'autorisation partagée par les contrôleurs de téléchargement de fichiers
 * privés liés à un marin (pièce jointe, document annexe, ...).
 */
trait VerifieAccesMarinTrait
{
    /**
     * Autorisé pour : le marin lui-même, un mentor de ce marin, ou un utilisateur
     * ayant la permission $permission.
     */
    private function marinEstAccessiblePour(Marin $marin, string $permission): bool
    {
        if (auth()->user()?->can($permission)) {
            return true;
        }

        $marinConnecte = Marin::fromCurrentUser();

        if (!$marinConnecte) {
            return false;
        }

        $marinConnecte = cast_as_eloquent_descendant($marinConnecte, Marin::class);

        if ($marinConnecte->id === $marin->id) {
            return true;
        }

        return $marinConnecte->est_mentor_de($marin);
    }
}
