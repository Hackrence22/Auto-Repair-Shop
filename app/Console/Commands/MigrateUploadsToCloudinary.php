<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Finder\Finder;

class MigrateUploadsToCloudinary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Source should be the local path to the extracted contents of your
     * previous public/uploads folder (the directory that contains
     * subfolders like admin-profiles, shop-images, etc).
     */
    protected $signature = 'media:migrate-uploads-to-cloudinary {source : Absolute or relative path to local uploads root} {--dry-run : List files without uploading}';

    /**
     * The console command description.
     */
    protected $description = 'Upload legacy files from a local uploads directory to the configured public disk (Cloudinary) preserving relative paths.';

    public function handle(): int
    {
        $source = $this->argument('source');
        $dryRun = (bool) $this->option('dry-run');

        if (!is_dir($source)) {
            $this->error("Source directory not found: {$source}");
            return 1;
        }

        $disk = Storage::disk('public');
        $finder = new Finder();
        $finder->files()->in($source);

        $count = 0;
        foreach ($finder as $file) {
            $absolutePath = $file->getRealPath();
            if ($absolutePath === false) {
                continue;
            }
            // Compute relative path under the provided source directory
            $relativePath = ltrim(str_replace('\\', '/', substr($absolutePath, strlen(realpath($source)))), '/');
            // Normalize to use forward slashes
            $relativePath = str_replace('\\', '/', $relativePath);

            if ($dryRun) {
                $this->line("DRY RUN: would upload {$relativePath}");
                $count++;
                continue;
            }

            try {
                // Read file to avoid stream issues on Windows
                $contents = @file_get_contents($absolutePath);
                if ($contents === false) {
                    $this->warn("Skip (cannot read): {$relativePath}");
                    continue;
                }
                // Upload to the public disk (Cloudinary) with the same relative key
                $disk->put($relativePath, $contents);
                $count++;
                $this->info("Uploaded: {$relativePath}");
            } catch (\Throwable $e) {
                $this->error("Failed: {$relativePath} - " . $e->getMessage());
            }
        }

        $this->info("Done. Processed {$count} file(s).");
        return 0;
    }
}


