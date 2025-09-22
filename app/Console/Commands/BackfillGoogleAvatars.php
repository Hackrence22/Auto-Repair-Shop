<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class BackfillGoogleAvatars extends Command
{
    protected $signature = 'users:backfill-google-avatars {--limit=100 : Max users to process in one run}';

    protected $description = 'Download Google avatar URLs for users missing profile_picture and upload to the public disk (Cloudinary).';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $query = User::query()
            ->whereNotNull('google_id')
            ->where(function ($q) {
                $q->whereNull('profile_picture')->orWhere('profile_picture', '');
            })
            ->whereNotNull('avatar')
            ->limit($limit);

        $users = $query->get();
        if ($users->isEmpty()) {
            $this->info('No users require backfill.');
            return 0;
        }

        $processed = 0;
        foreach ($users as $user) {
            $avatarUrl = $user->avatar;
            if (empty($avatarUrl) || !filter_var($avatarUrl, FILTER_VALIDATE_URL)) {
                $this->warn("Skip user {$user->id}: invalid avatar URL");
                continue;
            }

            try {
                $resp = Http::timeout(10)->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; AutoRepairShop/1.0)'
                ])->get($avatarUrl);
                if (!$resp->ok() || empty($resp->body())) {
                    $this->warn("Skip user {$user->id}: failed to download ({$resp->status()})");
                    continue;
                }

                $contentType = $resp->header('Content-Type');
                $ext = 'jpg';
                if (is_string($contentType)) {
                    if (str_contains($contentType, 'png')) { $ext = 'png'; }
                    elseif (str_contains($contentType, 'jpeg') || str_contains($contentType, 'jpg')) { $ext = 'jpg'; }
                    elseif (str_contains($contentType, 'gif')) { $ext = 'gif'; }
                    elseif (str_contains($contentType, 'webp')) { $ext = 'webp'; }
                }

                $filename = 'profile_' . $user->id . '_' . time() . '.' . $ext;
                $path = 'profile-pictures/' . $filename;
                Storage::disk('public')->put($path, $resp->body());

                $user->update(['profile_picture' => $path]);
                $processed++;
                $this->info("Updated user {$user->id}: {$path}");
            } catch (\Throwable $e) {
                $this->error("User {$user->id} failed: " . $e->getMessage());
            }
        }

        $this->info("Done. Backfilled {$processed} user(s).");
        return 0;
    }
}


