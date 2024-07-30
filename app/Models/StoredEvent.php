<?php

namespace Modules\FcmCentral\Models;

use Carbon\Carbon;

use Illuminate\Database\Eloquent\Builder;
use Modules\FcmCommun\Models\StoredEvent as StoredEventBase;

class StoredEvent extends StoredEventBase
{
    protected $prefix = 'fcmcentral_';

    public function scopeAfter(Builder $query, $date): Builder
    {
        return $query->where('event_datetime', '>=', Carbon::parse($date));
    }

    public function scopeBefore(Builder $query, $date): Builder
    {
        return $query->where('event_datetime', '<=', Carbon::parse($date));
    }
}
