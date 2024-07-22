<?php

use Modules\FcmCommun\Migrations\CreateStoredEventMigration as CommunCreateStoredEventMigration;

return new class extends CommunCreateStoredEventMigration
{
    public $prefix = "fcmcentral_";
};
