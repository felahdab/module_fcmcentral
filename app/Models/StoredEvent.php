<?php

namespace Modules\FcmCentral\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Modules\FcmCentral\Traits\HasTablePrefix;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\Models\StoredEvent as StoredEventBase;

class StoredEvent extends StoredEventBase
{
    use HasTablePrefix;

    public function scopeAfter(Builder $query, $date): Builder
    {
        return $query->where('event_datetime', '>=', Carbon::parse($date));
    }

    public function scopeBefore(Builder $query, $date): Builder
    {
        return $query->where('event_datetime', '<=', Carbon::parse($date));
    }

    #[Scope]
    protected function filtre_par_unite(Builder $query)
    {
        $marins_visibles_references = Marin::query()
            ->get(['id', 'nid', 'uuid'])
            ->flatMap(fn (Marin $marin) => [$marin->nid, $marin->uuid, (string) $marin->id])
            ->filter()
            ->unique()
        ; // Cette requête repose sur le modèle Marin, et applique donc le scope de filtrage par unité.

        $query->whereNot('object_class', ObjectClasses::MARIN->value) // Tous les stored event qui ne parlent pas d'un marin en particulier
            ->orWhereIn('object_uuid', $marins_visibles_references) // Les stored event qui parlent d'un marin dont le marin est dans la liste des marins accessibles.
        ;
    }
}
