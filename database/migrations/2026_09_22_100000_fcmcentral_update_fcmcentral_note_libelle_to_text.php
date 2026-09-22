 <?php

use Modules\FcmCommun\Migrations\UpdateNoteLibelleToTextMigration;

return new class extends UpdateNoteLibelleToTextMigration {
    public $prefix = 'fcmcentral_';
};
