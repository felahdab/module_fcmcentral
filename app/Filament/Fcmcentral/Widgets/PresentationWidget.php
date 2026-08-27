<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Widgets;

use App\Models\Setting;
use Filament\Widgets\Widget;
use Modules\FcmCentral\Filament\Fcmcentral\Pages\PresentationSettings;

class PresentationWidget extends Widget
{
    protected static ?int $sort = -100;
    protected int|string|array $columnSpan = 'full';
    protected string $view = 'fcmcentral::filament.fcmcentral.widgets.presentation';

    public array $data = [];

    public function mount(): void
    {
        $this->data = Setting::forKey('fcmcentral_presentation')->data ?? [];
    }

    public function peutModifier(): bool
    {
        return auth()->check() && auth()->user()->can('fcmcentral::dashboard');
    }

    public function urlEdition(): string
    {
        return PresentationSettings::getUrl();
    }
}
