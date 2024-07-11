<?php

return [
    'name' => 'Core',
    'overrides' => [
        'permission' => true,
        'filament-shield' => true,
        'settings' => true,
        'ldap' => true,
        'activitylog' => true,
    ],
    'multitenancy' => [
        'enabled' => env('MULTITENANCY_ENABLED', false),
        'shared_models' => [
            \App\Models\User::class,
            \Modules\Core\Models\User::class,
        ],
    ],
    'ldap' => [
        'enabled' => env('LDAP_ENABLED', false),
        'providers' => [
            'staff' => [
                'driver' => 'ldap',
                'model' => \Modules\Core\Models\Ldap\Staff::class,
                'rules' => [],
                'scopes' => [],
                'database' => [
                    'model' => 'App\Models\User',
                    'sync_passwords' => false,
                    'sync_attributes' => [
                        'name' => 'cn',
                        'username' => 'samaccountname',
                        'email' => 'mail',
                        'uac' => 'useraccountcontrol',
                    ],
                    'sync_existing' => [
                        'email' => 'mail',
                        'username' => 'samaccountname',
                        'name' => 'cn',
                        'uac' => 'useraccountcontrol',
                    ],
                    'password_column' => 'password',
                ],

            ],
            'students' => [
                'driver' => 'ldap',
                'model' => \Modules\Core\Models\Ldap\Student::class,
                'rules' => [],
                'scopes' => [],
                'database' => [
                    'model' => 'App\Models\User',
                    'sync_passwords' => true,
                    'sync_attributes' => [
                        'name' => 'cn',
                        'username' => 'samaccountname',
                        'uac' => 'useraccountcontrol',
                        'email' => 'mail',
                    ],
                    'sync_existing' => [
                        'name' => 'cn',
                        'username' => 'samaccountname',
                        'email' => 'mail',
                        'uac' => 'useraccountcontrol',
                    ],
                    'password_column' => 'password',
                ],

            ],
        ],
    ],
    'masquerade' => env('MASQUERADE_ENABLED', false),
];
