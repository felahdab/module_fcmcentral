<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Widgets;

use Filament\Widgets\Widget;
use Modules\FcmCentral\Models\Marin;



class DocumentationMentorWidget extends Widget
{
    protected static string $view = 'fcmcentral::filament.fcmcentral.widgets.documentation-mentor-widget';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = -1;

    public static function canView(): bool
    {
        // Ce widget ne doit être visible que si l'utilisateur est un mentor.
        $currentMarin = Marin::fromCurrentUser();
        if ( $currentMarin == null ) return false;

        return $currentMarin->est_mentor ;
    }

    public function getPdfUrl(): string
    {
        return asset('fcmcentral/pdfs/LdM_DEASM.pdf');
    }
}
