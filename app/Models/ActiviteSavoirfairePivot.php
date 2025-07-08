<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ActiviteSavoirfairePivot extends Pivot 
{
  protected $fillable=[
    'activite_id',
    'savoirfaire_id',
    'coeff',
    'duree',
    'ordre',
    'data'
  ];

  protected $casts = [
    'coeff' => 'decimal:2',
    'duree' => 'string',
    'ordre' => 'integer',
    'data' => 'array'
  ];

}