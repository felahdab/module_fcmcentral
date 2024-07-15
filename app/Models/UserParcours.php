<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Modules\FcmCentral\Traits\HasTablePrefix;

use App\Models\User;
use Modules\FcmCentral\Models\ParcoursSerialise;

class UserParcours extends Model
{
    use HasTablePrefix;
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [ "user_id",
                            "parcours_id",
                            "parcours"];

    protected $casts = [
        'parcours' => 'array',
    ];

    public function user() 
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parcoursserialise() 
    {
        return $this->belongsTo(ParcoursSerialise::class, 'parcours_id');
    }

    
}
