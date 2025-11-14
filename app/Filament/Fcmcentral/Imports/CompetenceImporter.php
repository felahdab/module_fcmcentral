<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Imports;

use Modules\FcmCentral\Models\Competence;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class CompetenceImporter extends Importer
{
    protected static ?string $model = Competence::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('code')
                ->label('Code')
                ->requiredMapping()
                ->rules(['required', 'max:150']),

            ImportColumn::make('libelle_court')
                ->label('Libellé court')
                ->requiredMapping()
                ->rules(['required', 'max:150']),

            ImportColumn::make('libelle_long')
                ->label('Libellé long')
                ->rules(['nullable', 'max:1500']),

            // relationship: maps to parcours_type_id via parcoursType relationship
            ImportColumn::make('parcours_type')
                ->label('Type de Parcours')
                ->relationship(resolveUsing: 'libelle_court')
                ->helperText('Rechercher par libellé'),

            ImportColumn::make('color')
                ->label('Couleur du Badge')
                ->rules(['nullable', 'max:50']),

            ImportColumn::make('ordre')
                ->label('Ordre')
                ->integer()
                ->rules(['nullable', 'integer']),
        ];

    }

    public function resolveRecord(): ?Competence
    {
        // return Competence::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

       // return new Competence();
       $code = $this->data['code'] ?? null;

       if (! $code) {
           return new Competence();
       }

       return Competence::firstOrNew(['code' => $code]);

    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Import des compétences terminé : ' . number_format($import->successful_rows) . ' ' . str('ligne')->plural($import->successful_rows) . ' importée(s).';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' en échec.';
        }

        return $body;
    }
}
