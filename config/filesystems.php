<?php

return [

    'supabase_public_url' => env('SUPABASE_PUBLIC_URL', 'https://kjhaquimghhejqznleyn.supabase.co/storage/v1/object/public/gallery'),


    'default' => env('FILESYSTEM_DISK', 'local'),

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root'   => storage_path('app/private'),
            'serve'  => true,
            'throw'  => false,
            'report' => false,
        ],

        'private' => [
            'driver' => 'local',
            'root'   => storage_path('app/private'),
            'throw'  => false,
        ],

        'public' => [
            'driver'     => 'local',
            'root'       => storage_path('app/public'),
            'url'        => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw'      => false,
            'report'     => false,
        ],

        's3' => [
            'driver'   => 's3',
            'key'      => env('AWS_ACCESS_KEY_ID'),
            'secret'   => env('AWS_SECRET_ACCESS_KEY'),
            'region'   => env('AWS_DEFAULT_REGION'),
            'bucket'   => env('AWS_BUCKET'),
            'url'      => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw'    => false,
            'report'   => false,
        ],

        'supabase' => [
            'driver'                  => 's3',
            'key'                     => env('SUPABASE_STORAGE_KEY', 'service_role'),
            'secret'                  => env('SUPABASE_STORAGE_SECRET'),
            'region'                  => 'us-east-1',
            'bucket'                  => env('SUPABASE_STORAGE_BUCKET', 'gallery'),
            'endpoint'                => env('SUPABASE_STORAGE_ENDPOINT'),
            'use_path_style_endpoint' => true,
            'http'                    => ['verify' => false],
            'throw'                   => true,
        ],

    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
