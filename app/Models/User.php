<?php

namespace Modules\FcmCentral\Models;
use \App\Models\User as BaseUser;

class User extends BaseUser
{
    protected $guard_name ='web';

}