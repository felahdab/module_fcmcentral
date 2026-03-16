<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffectationParcours extends Model
{
    protected $table = 'fcmcentral_affectation_parcours';

    protected $fillable = [
        'parcours_id',
        'affectation_id',
        'ordre',
    ];

    protected $casts = [
        'ordre' => 'integer',
    ];

    // ─── Relations ───────────────────────────────────────────────

    public function parcours(): BelongsTo
    {
        return $this->belongsTo(Parcours::class, 'parcours_id');
    }

    public function affectation(): BelongsTo
    {
        return $this->belongsTo(Affectation::class, 'affectation_id');
    }
}
