<?php

namespace Modules\FcmCentral\Models;

use Carbon\Carbon;

use Illuminate\Database\Eloquent\Builder;
use Modules\FcmCommun\Models\StoredEvent as StoredEventBase;
use Modules\FcmCentral\Traits\HasTablePrefix;

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
}
