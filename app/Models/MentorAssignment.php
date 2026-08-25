<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Une ligne de la table pivot fcmcentral_fcm_marin_mentors : un couple (mentor, mentoré).
 */
class MentorAssignment extends Model
{
    protected $table = 'fcmcentral_fcm_marin_mentors';

    public $timestamps = true;

    public function fcmMarin(): BelongsTo
    {
        return $this->belongsTo(FcmMarin::class, 'fcm_marin_id');
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(Marin::class, 'marin_id');
    }
}
