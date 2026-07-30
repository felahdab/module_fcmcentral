<?php

namespace Modules\FcmCentral\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\FcmCentral\Http\Controllers\Concerns\VerifieAccesMarinTrait;
use Modules\FcmCentral\Models\Marin;
use Modules\RH\Models\Scopes\MarinFiltreParUniteScope;
use Symfony\Component\HttpFoundation\Response;

class DocumentAnnexeDownloadController extends Controller
{
    use VerifieAccesMarinTrait;

    /**
     * Sert le fichier d'un document annexe (hors parcours) d'un marin, stocké sur le
     * disque privé s3-private.
     *
     * Accès autorisé pour :
     * - le marin concerné lui-même ;
     * - un mentor de ce marin ;
     * - un utilisateur ayant la permission fcmcentral::document_annexe.voir.
     */
    public function __invoke(string $marinUuid, string $documentUuid): Response
    {
        // ⚠️ Voir PieceJointeDownloadController : sans le retrait de ce scope, un mentor ou un
        // utilisateur autorisé par permission ne pourrait même pas trouver le marin ciblé s'il
        // n'est pas dans la même unité.
        $marin = Marin::withoutGlobalScope(MarinFiltreParUniteScope::class)
            ->where('uuid', $marinUuid)
            ->firstOrFail()
        ;

        abort_unless($this->marinEstAccessiblePour($marin, 'fcmcentral::document_annexe.voir'), 403);

        $chemin = $this->trouverCheminDocumentAnnexe($marin, $documentUuid);

        abort_if(!$chemin, 404, 'Document introuvable.');

        $disk = Storage::disk('s3-private');

        abort_unless($disk->exists($chemin), 404, 'Fichier introuvable sur le stockage.');

        return $disk->response($chemin);
    }

    private function trouverCheminDocumentAnnexe(Marin $marin, string $documentUuid): ?string
    {
        $documents = $marin->complements_fcm?->documents_annexes ?? [];

        foreach ($documents as $document) {
            if (($document['uuid'] ?? null) === $documentUuid) {
                return $document['chemin'] ?? null;
            }
        }

        return null;
    }
}
