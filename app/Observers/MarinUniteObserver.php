<?php

namespace Modules\FcmCentral\Observers;

use App\Models\User;
use Filament\Notifications\Notification;
use Modules\FcmCentral\Models\Marin as FcmCentralMarin;
use Modules\RH\Models\Marin;

class MarinUniteObserver
{
    /**
     * Quand un marin change d'unité, on vérifie si ce changement crée un décalage
     * d'unité avec son mentor (s'il est mentoré) ou avec ses mentorés (s'il est mentor),
     * et on prévient les personnes habilitées à gérer les mentors (permission
     * fcmcentral::suivi.mentor) pour qu'elles puissent réaffecter si besoin.
     */
    public function updated(Marin $marin): void
    {
        if (!$marin->isDirty('unite_id')) {
            return;
        }

        // On ne se base pas sur $fcmCentralMarin->suivi_en_fcm : un mentor n'a pas
        // forcément sa propre fiche FCM (complementsFcm), seul le mentoré en a une.
        // isMentor()/getMentors() renvoient naturellement false/vide si non concerné.
        $fcmCentralMarin = cast_as_eloquent_descendant($marin, FcmCentralMarin::class);

        $decalages = collect();

        if ($fcmCentralMarin->isMentor()) {
            foreach ($fcmCentralMarin->marinsSupervises() as $supervise) {
                if ($supervise->unite_id !== $fcmCentralMarin->unite_id) {
                    $decalages->push("mentoré {$supervise->fullNameAndGrade}");
                }
            }
        }

        foreach ($fcmCentralMarin->getMentors() as $mentor) {
            if ($mentor->unite_id !== $fcmCentralMarin->unite_id) {
                $decalages->push("mentor {$mentor->fullNameAndGrade}");
            }
        }

        if ($decalages->isEmpty()) {
            return;
        }

        $destinataires = User::permission('fcmcentral::suivi.mentor')->get();

        if ($destinataires->isEmpty()) {
            return;
        }

        Notification::make()
            ->title("Décalage d'unité mentor/mentoré")
            ->body("{$fcmCentralMarin->fullNameAndGrade} a changé d'unité, décalage avec : ".$decalages->implode(', ').'.')
            ->warning()
            ->sendToDatabase($destinataires);
    }
}
