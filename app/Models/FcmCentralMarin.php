<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\FcmCentral\Traits\HasTablePrefix;
use Modules\RH\Models\Marin;

// use Modules\FcmCentral\Database\Factories\FcmCentralMarinFactory;

class FcmCentralMarin extends Model
{
    use HasFactory;
    use HasTablePrefix;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'rh_marin_id',
    ];


    // protected static function newFactory(): FcmCentralMarinFactory
    // {
    //     // return FcmCentralMarinFactory::new();
    // }

    /**
     * Un FcmCentralMarin appartient à un RhMarin
     */
    public function rhMarin()
    {
        return $this->belongsTo(Marin::class);
    }
}
