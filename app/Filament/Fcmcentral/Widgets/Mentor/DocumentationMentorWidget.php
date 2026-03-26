<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Widgets\Mentor;

use Filament\Widgets\Widget;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Models\FcmMarin;

class DocumentationMentorWidget extends Widget
{
    protected string $view = 'fcmcentral::filament.fcmcentral.widgets.documentation-mentor-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = -1;

    public static function canView(): bool
    {
        $marin = Marin::fromCurrentUser();

        if ($marin === null || !$marin->est_mentor) {
            return false;
        }

        return static::getParcoursViaMenutore($marin)?->livret_pdf_url !== null;
    }

    public function getPdfUrl(): ?string
    {
        $marin = Marin::fromCurrentUser();
        if ($marin === null) return null;

        return static::getParcoursViaMenutore($marin)?->livret_pdf_url;
    }

    public function getPdfLabel(): string
    {
        $marin   = Marin::fromCurrentUser();
        $parcours = $marin ? static::getParcoursViaMenutore($marin) : null;

        return $parcours
            ? 'Livret ' . ($parcours->libelle_court ?? $parcours->code ?? 'du Mentor')
            : 'Livret du Mentor';
    }

    private static function getParcoursViaMenutore(Marin $mentor): ?Parcours
    {
        // Récupère le premier mentoré de ce mentor
        $premierFcmMarin = FcmMarin::where('mentor_id', $mentor->id)->first();

        if ($premierFcmMarin === null) return null;

        // Remonte la chaîne depuis le mentoré
        return $premierFcmMarin
            ->donnees_du_parcours        // MarinParcours
            ?->parcours_serialise        // ParcoursSerialise
            ?->parcoursModel;            // Parcours
    }
}
