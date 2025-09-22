<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Storage;
use Google\Cloud\Storage\StorageClient;
use League\Flysystem\Filesystem;
use League\Flysystem\GoogleCloudStorage\GoogleCloudStorageAdapter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Use Bootstrap 5 pagination views globally
        Paginator::useBootstrapFive();

        // Register Google Cloud Storage based drivers (GCS and Firebase Storage)
        Storage::extend('gcs', function ($app, $config) {
            $clientConfig = [];
            if (!empty($config['project_id'])) {
                $clientConfig['projectId'] = $config['project_id'];
            }
            if (!empty($config['key_file'])) {
                // Prefer key file path if provided
                if (is_string($config['key_file']) && file_exists($config['key_file'])) {
                    $clientConfig['keyFilePath'] = $config['key_file'];
                } else {
                    // Accept JSON string contents
                    $clientConfig['keyFile'] = is_string($config['key_file']) ? json_decode($config['key_file'], true) : $config['key_file'];
                }
            }

            $client = new StorageClient($clientConfig);
            $bucket = $client->bucket($config['bucket']);
            $pathPrefix = $config['path_prefix'] ?? null;
            $adapter = new GoogleCloudStorageAdapter($bucket, $pathPrefix);
            $visibility = $config['visibility'] ?? 'public';
            $filesystemConfig = ['visibility' => $visibility];
            if (!empty($config['directory_visibility'])) {
                $filesystemConfig['directory_visibility'] = $config['directory_visibility'];
            }
            return new Filesystem($adapter, $filesystemConfig);
        });

        Storage::extend('firebase', function ($app, $config) {
            $clientConfig = [];
            if (!empty($config['project_id'])) {
                $clientConfig['projectId'] = $config['project_id'];
            }
            if (!empty($config['key_file'])) {
                if (is_string($config['key_file']) && file_exists($config['key_file'])) {
                    $clientConfig['keyFilePath'] = $config['key_file'];
                } else {
                    $clientConfig['keyFile'] = is_string($config['key_file']) ? json_decode($config['key_file'], true) : $config['key_file'];
                }
            }

            $client = new StorageClient($clientConfig);
            $bucket = $client->bucket($config['bucket']);
            $pathPrefix = $config['path_prefix'] ?? null;
            $adapter = new GoogleCloudStorageAdapter($bucket, $pathPrefix);
            $visibility = $config['visibility'] ?? 'public';
            $filesystemConfig = ['visibility' => $visibility];
            if (!empty($config['directory_visibility'])) {
                $filesystemConfig['directory_visibility'] = $config['directory_visibility'];
            }
            return new Filesystem($adapter, $filesystemConfig);
        });
    }
}
