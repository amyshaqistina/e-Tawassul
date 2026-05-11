<?php

return [
    'defaults' => [
        'guard' => 'student',
        'passwords' => 'students',
    ],

    'guards' => [
        'student' => [
            'driver' => 'session',
            'provider' => 'students',
        ],
        'admin' => [
            'driver' => 'session',
            'provider' => 'admins',
        ],
        'nok' => [
            'driver' => 'session',
            'provider' => 'noks',
        ],
        'lecturer' => [
            'driver' => 'session',
            'provider' => 'lecturers',
        ],
        'web' => [
            'driver' => 'session',
            'provider' => 'students',
        ],
    ],

    'providers' => [
        'students' => [
            'driver' => 'eloquent',
            'model'  => App\Models\Student::class,
        ],
        'admins' => [
            'driver' => 'eloquent',
            'model'  => App\Models\Admin::class,
        ],
        'noks' => [
            'driver' => 'eloquent',
            'model'  => App\Models\NextOfKin::class,
        ],
        'lecturers' => [
            'driver' => 'eloquent',
            'model'  => App\Models\Lecturer::class,
        ],
    ],

    'passwords' => [
        'students' => [
            'provider' => 'students',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,
];
