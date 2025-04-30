<?php

namespace Modules\FcmCentral\Events;

use Modules\FcmCommun\DataObjects\UserGeneratedEvent as BaseUserGeneratedEvent;
use Modules\FcmCentral\Traits\HasTablePrefix;

class UserGeneratedEvent extends BaseUserGeneratedEvent
{
    use HasTablePrefix;

    /**
     * Recupere le prefixe de maniere statique
     * @return string
     */
    public static function getPrefixTable(): string
    {
        // crée une instance pour acceder au préfixe via le trait
        return 'fcmcentral_';

    }
}