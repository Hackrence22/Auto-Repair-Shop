<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'public'),

    // Optional separate cloud disk name (some code/packages reference this)
    'cloud' => env('FILESYSTEM_CLOUD', env('PUBLIC_DISK_DRIVER', 'public') === 'cloudinary' ? 'public' : 'public'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        // The "public" disk is used throughout the app for user-uploaded files
        // (images, payment proofs, receipts). We allow switching it to Cloudinary
        // via the PUBLIC_DISK_DRIVER env without touching application code that
        // calls Storage::disk('public').
        'public' => env('PUBLIC_DISK_DRIVER', 'local') === 'cloudinary'
            ? [
                'driver' => 'cloudinary',
                'url' => env('CLOUDINARY_URL'),
                'secure' => env('CLOUDINARY_SECURE', true),
                'visibility' => 'public',
                'throw' => false,
                'report' => false,
            ]
            : [
                'driver' => 'local',
                'root' => public_path('uploads'),
                'url' => env('APP_URL').'/uploads',
                'visibility' => 'public',
                'throw' => false,
                'report' => false,
            ],

        // Optional explicit Cloudinary disk
        'cloudinary' => [
            'driver' => 'cloudinary',
            'url' => env('CLOUDINARY_URL'),
            'secure' => env('CLOUDINARY_SECURE', true),
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
