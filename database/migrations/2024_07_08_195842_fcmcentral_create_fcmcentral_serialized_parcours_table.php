<?php

use Modules\FcmCommun\Migrations\CreateParcoursSerialisesMigration;

return new class extends CreateParcoursSerialisesMigration
{
    public $prefix = 'fcmcentral_';
    public $table_marins = 'rh_marins';

};
