<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\StoredEventResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;
use Modules\RH\Models\Marin as RHMarin;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\StoredEventResource;

class ListStoredEvents extends ListRecords
{
    protected static string $resource = StoredEventResource::class;

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
                    ->pluck('uuid');

                if ($userUuids->isNotEmpty()) {
                    $q->orWhereIn('user_uuid', $userUuids);
                }

                // Recherche par nom de marin (objet concerné)
                $marinUuids = RHMarin::where('nom', 'like', "%{$search}%")
                    ->orWhere('prenom', 'like', "%{$search}%")
                    ->pluck('uuid');

                if ($marinUuids->isNotEmpty()) {
                    $q->orWhereIn('object_uuid', $marinUuids);
                }

                // Recherche directe sur le type d'événement
                $q->orWhere('event_type', 'like', "%{$search}%");

                // Recherche sur le commentaire dans detail (JSON)
                $q->orWhereJsonContains('detail->commentaire', $search)
                  ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(detail, '$.commentaire')) LIKE ?", ["%{$search}%"]);
            });
        }

        return $query;
    }
}
