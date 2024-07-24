<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\UserResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
