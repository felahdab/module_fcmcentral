<?php

namespace Modules\FcmCentral\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\FcmCentral\Http\Controllers\Concerns\VerifieAccesMarinTrait;
use Modules\FcmCentral\Models\Marin;
use Modules\RH\Models\Scopes\MarinFiltreParUniteScope;
use Symfony\Component\HttpFoundation\Response;

class PieceJointeDownloadController extends Controller
{
    use VerifieAccesMarinTrait;

    /**
     * Sert le fichier d'une pièce jointe (savoirfaire/objectif) du parcours d'un marin,
     * stockée sur le disque privé s3-private.
     *
     * Accès autorisé pour :
     * - le marin concerné lui-même ;
     * - un mentor de ce marin ;
     * - un utilisateur ayant la permission fcmcentral::piece_jointe.voir.
     */
    public function __invoke(string $marinUuid, string $type, string $uuid): Response
    {
        // ⚠️ Sans le retrait de ce scope, un mentor ou un utilisateur avec la permission
        // fcmcentral::piece_jointe.voir ne pourrait même pas trouver le marin ciblé dès lors
        // qu'il n'est pas dans la même unité (le scope filtre par unité de l'utilisateur
        // connecté) : l'autorisation réelle est vérifiée juste après, indépendamment de ce scope.
        $marin = Marin::withoutGlobalScope(MarinFiltreParUniteScope::class)
            ->where('uuid', $marinUuid)
            ->firstOrFail()
        ;

        abort_unless($this->marinEstAccessiblePour($marin, 'fcmcentral::piece_jointe.voir'), 403);

        $chemin = $this->trouverCheminPieceJointe($marin, $type, $uuid);

        abort_if(!$chemin, 404, 'Pièce jointe introuvable.');

        $disk = Storage::disk('s3-private');

        abort_unless($disk->exists($chemin), 404, 'Fichier introuvable sur le stockage.');

        return $disk->response($chemin);
    }

    private function trouverCheminPieceJointe(Marin $marin, string $type, string $uuid): ?string
    {
        $parcoursMarin = $marin->complements_fcm?->donnees_du_parcours?->parcoursmarin ?? [];

        foreach ($parcoursMarin['affectations'] ?? [] as $affectation) {
            foreach ($affectation['competences'] ?? [] as $competence) {
                foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                    if ('savoirfaire' === $type && ($csf['uuid'] ?? null) === $uuid) {
                        return $csf['piece_jointe'] ?? null;
                    }

                    if ('objectif' === $type) {
                        foreach ($csf['objectifs'] ?? [] as $objectif) {
                            if (($objectif['uuid'] ?? null) === $uuid) {
                                return $objectif['piece_jointe'] ?? null;
                            }
                        }
                    }
                }
            }
        }

        return null;
    }
}
