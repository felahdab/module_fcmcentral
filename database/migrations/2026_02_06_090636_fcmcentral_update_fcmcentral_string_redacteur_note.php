<?php

use Illuminate\Database\PostgresConnection;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

use Modules\FcmCommun\Migrations\UpdateNoteMarinRedacteurToStringMigration;

return new class extends UpdateNoteMarinRedacteurToStringMigration
{
    public $prefix = 'fcmcentral_';
};
