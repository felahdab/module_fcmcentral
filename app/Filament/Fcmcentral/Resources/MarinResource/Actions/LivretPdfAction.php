<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Actions;

use Filament\Actions\Action;
use Modules\FcmCommun\Services\LivretPdfService;

class LivretPdfAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name)
            ->iconButton()
            ->icon('heroicon-m-document-text')
            ->color('secondary')
            ->extraAttributes([
                'title' => 'Télécharger PDF du Livret FCM',
                'class' => 'btn-pdf',
            ])
            ->action(function ($record) {
                return response()->streamDownload(function () use ($record) {
                    echo app(LivretPdfService::class)->generatePdf($record);
                }, "livret-fcm-{$record->nom}-{$record->prenom}.pdf", [
                    'Content-Type' => 'application/pdf',
                ]);
            })
        ;
    }
}
