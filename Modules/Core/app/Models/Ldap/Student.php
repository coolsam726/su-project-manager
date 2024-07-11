<?php

namespace Modules\Core\Models\Ldap;

use LdapRecord\Models\ActiveDirectory\User;

class Student extends User
{
    protected ?string $connection = 'students';
}
