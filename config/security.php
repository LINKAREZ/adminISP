<?php

return [
    'root_email' => env('ROOT_USER_EMAIL'),
    'root_emails' => array_filter(array_map(
        'trim',
        explode(',', (string)env('ROOT_USER_EMAILS', ''))
    )),
    'root_name' => env('ROOT_USER_NAME', 'Root'),
    'root_password' => env('ROOT_USER_PASSWORD'),
    'default_admin_email' => env('DEFAULT_ADMIN_EMAIL'),
    'default_admin_password' => env('DEFAULT_ADMIN_PASSWORD'),
    'default_admin_name' => env('DEFAULT_ADMIN_NAME', 'Administrador'),
];
