<?php

namespace Modules\FcmCentral\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\FcmCentral\Models\Competence;
use Modules\FcmCentral\Models\Annee; 

class ExportController extends Controller
{
    public function exportCompetencesCsv(Request $request)
    {
        $ids = $request->query('ids'); // "1,2,3" optional
        $filename = 'competences_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $columns = [
            'ID',
            'Code',
            'Libellé court',
            'Libellé long',
            'Type de Parcours',
            'Couleur du Badge',
            'Ordre',
        ];

        $callback = function () use ($columns, $ids) {
            $out = fopen('php://output', 'w');
            // BOM UTF-8 pour Excel
            fwrite($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            // séparation par ';' (Excel FR). change en ',' si besoin
            fputcsv($out, $columns, ';');

            $query = Competence::with('parcoursType')->orderBy('ordre');

            if (! empty($ids)) {
                $idArray = array_filter(explode(',', $ids), fn($v) => is_numeric($v));
                if (! empty($idArray)) {
                    $query->whereIn('id', $idArray);
                }
            }

            $query->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $r) {
                    fputcsv($out, [
                        $r->id,
                        $r->code,
                        $r->libelle_court,
                        $r->libelle_long,
                        $r->parcoursType->libelle_court ?? '',
                        $r->color ?? '',
                        $r->ordre ?? '',
                    ], ';');
                }
            });

            fclose($out);
        };

        return response()->streamDownload($callback, $filename, $headers);
    }

    public function exportAnneesCsv(Request $request)
    {
        $ids = $request->query('ids'); // optional
        $filename = 'annees_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        // adapte les colonnes selon ton model Annee
        $columns = [
            'ID',
            'Année',
            'Libellé',
            'Créé à',
        ];

        $callback = function () use ($columns, $ids) {
            $out = fopen('php://output', 'w');
            fwrite($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, $columns, ';');

            $query = Annee::orderBy('id');

            if (! empty($ids)) {
                $idArray = array_filter(explode(',', $ids), fn($v) => is_numeric($v));
                if (! empty($idArray)) {
                    $query->whereIn('id', $idArray);
                }
            }

            $query->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $r) {
                    // adapte les attributs (ex: $r->annee, $r->libelle)
                    fputcsv($out, [
                        $r->id,
                        $r->annee ?? ($r->libelle ?? ''),
                        $r->libelle ?? '',
                        optional($r->created_at)->toDateTimeString() ?? '',
                    ], ';');
                }
            });

            fclose($out);
        };

        return response()->streamDownload($callback, $filename, $headers);
    }
}
