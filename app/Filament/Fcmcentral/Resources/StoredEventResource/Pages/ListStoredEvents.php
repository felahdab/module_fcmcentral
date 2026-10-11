<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\StoredEventResource\Pages;

use App\Models\User;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\StoredEventResource;
use Modules\RH\Models\Marin as RHMarin;

class ListStoredEvents extends ListRecords
{
    protected static string $resource = StoredEventResource::class;

    /** La neutralisation des méthodes authorizeRecourseAccess et authorizeAccess renvoie tout le controle d'accès à la méthode
     *  canAccess. Le comportement de la page est alors identique aux pages non intégrées à des Ressource.
     */
    public static function authorizeResourceAccess(): void {}

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->user()->can('fcmcentral::voir_journal_des_evenements');
    }

    protected function authorizeAccess(): void {}

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function applySearchToTableQuery(Builder $query): Builder
    {
        $search = $this->getTableSearch();

        if (filled($search)) {
            $query->where(function (Builder $q) use ($search) {
                // Recherche par nom d'utilisateur → retrouve les user_uuid
                $userUuids = User::where('display_name', 'like', "%{$search}%")
                    ->orWhere('nom', 'like', "%{$search}%")
                    ->pluck('uuid')
                ;

                if ($userUuids->isNotEmpty()) {
                    $q->orWhereIn('user_uuid', $userUuids);
                }

                // Recherche par nom de marin (objet concerné)
                $marinReferences = RHMarin::where('nom', 'like', "%{$search}%")
                    ->orWhere('prenom', 'like', "%{$search}%")
                    ->get(['id', 'nid', 'uuid'])
                    ->flatMap(fn (RHMarin $marin) => [$marin->nid, $marin->uuid, (string) $marin->id])
                    ->filter()
                    ->unique()
                ;

                if ($marinReferences->isNotEmpty()) {
                    $q->orWhereIn('object_uuid', $marinReferences);
                }

                // Recherche directe sur le type d'événement
                $q->orWhere('event_type', 'like', "%{$search}%");

                // Recherche sur le commentaire dans detail (JSON)
                $q->orWhereJsonContains('detail->commentaire', $search)
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(detail, '$.commentaire')) LIKE ?", ["%{$search}%"])
                ;
            });
        }

        return $query;
    }
}
