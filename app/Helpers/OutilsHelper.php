<?php

namespace Modules\FcmCentral\Helpers;

class OutilsHelper
{
    
    public static function selectColors(): array
    {
        return [
            'info' => 'Bleu',
            'success' => 'Vert',
            'gray' => 'Gris',
            'warning' => 'Orange',
            'danger' => 'Rouge',
        ];
    }
}
