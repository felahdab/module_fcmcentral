<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Exports;

use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Modules\FcmCentral\Models\Competence;

class CompetenceExporter extends Exporter
{
    protected static ?string $model = Competence::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('code')->label('Code'),
            ExportColumn::make('libelle_court')->label('Libellé court'),
            ExportColumn::make('libelle_long')->label('Libellé long'),
            ExportColumn::make('parcoursType.libelle_court')->label('Type de Parcours'),
            ExportColumn::make('color')->label('Couleur du Badge'),
            ExportColumn::make('ordre')->label('Ordre'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Export des compétences terminé : '.number_format($export->successful_rows).' '.str('ligne')->plural($export->successful_rows).' exportée(s).';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' en échec.';
        }

        return $body;
    }
}
