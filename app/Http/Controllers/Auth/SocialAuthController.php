<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class SocialAuthController extends Controller
{
    /**
     * Redirect to Google OAuth for Login
     */
    public function redirectToGoogleLogin()
    {
        // Store the intended action in session
        session(['google_auth_action' => 'login']);
        
        return Socialite::driver('google')
            ->scopes(['profile', 'email', 'phone', 'address'])
            ->redirect();
    }

    /**
     * Redirect to Google OAuth for Registration
     */
    public function redirectToGoogleRegister()
    {
        // Store the intended action in session
        session(['google_auth_action' => 'register']);
        
        return Socialite::driver('google')
            ->scopes(['profile', 'email', 'phone', 'address'])
            ->redirect();
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            $action = session('google_auth_action', 'login'); // Get action from session, default to login
            
            // Check if user already exists
            $user = User::where('email', $googleUser->getEmail())->first();
            
            if ($user) {
                // User exists - handle login flow
                if ($action === 'register') {
                    // User tried to register but account already exists
                    return redirect()->route('login')->with('error', 'An account with this email already exists. Please login instead.');
                }
                
                // Update existing user with Google info if needed
                $updateData = [];
                if (!$user->google_id) {
                    $updateData['google_id'] = $googleUser->getId();
                }
                // Ensure profile_picture is populated (use Google avatar URL directly; no upload)
                if (!$user->profile_picture && $googleUser->getAvatar()) {
                    $updateData['profile_picture'] = $googleUser->getAvatar();
                }
                // Do not persist remote avatar URL; keep using profile_picture
                if (empty($updateData) === false) {
                    $user->update($updateData);
                }
                
                // Log the user in
                Auth::login($user);
                
                // Clear the session action
                session()->forget('google_auth_action');
                
                // Check if user needs to complete profile
                if ($this->needsProfileCompletion($user)) {
                    return redirect()->route('profile.edit')->with('info', 'Please complete your profile information to continue.');
                }
                
                return redirect()->intended('/')->with('success', 'Successfully logged in with Google!');
                
            } else {
                // User doesn't exist - handle registration flow
                if ($action === 'login') {
                    // User tried to login but account doesn't exist
                    return redirect()->route('register')->with('error', 'No account found with this email. Please register first.');
                }
                
                // Create new user with enhanced profile data
                $userData = [
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password' => Hash::make(Str::random(24)), // Random password
                    'email_verified_at' => now(), // Google emails are verified
                ];
                
                // Try to extract additional info from Google user
                $rawUser = $googleUser->getRaw();
                
                // Extract phone if available (requires additional Google permissions)
                if (isset($rawUser['phone_number'])) {
                    $userData['phone'] = $rawUser['phone_number'];
                }
                
                // Extract address if available (requires additional Google permissions)
                if (isset($rawUser['address'])) {
                    $address = $rawUser['address'];
                    if (is_array($address)) {
                        $addressParts = [];
                        if (isset($address['street_address'])) $addressParts[] = $address['street_address'];
                        if (isset($address['locality'])) $addressParts[] = $address['locality'];
                        if (isset($address['region'])) $addressParts[] = $address['region'];
                        if (isset($address['postal_code'])) $addressParts[] = $address['postal_code'];
                        if (isset($address['country'])) $addressParts[] = $address['country'];
                        $userData['address'] = implode(', ', $addressParts);
                    }
                }
                
                $user = User::create($userData);
                
                // Set profile_picture to Google avatar URL directly; skip upload
                if ($googleUser->getAvatar()) {
                    $user->update(['profile_picture' => $googleUser->getAvatar()]);
                }
                
                // Log the user in
                Auth::login($user);
                
                // Clear the session action
                session()->forget('google_auth_action');
                
                // Check if user needs to complete profile
                if ($this->needsProfileCompletion($user)) {
                    return redirect()->route('profile.edit')->with('info', 'Please complete your profile information to continue.');
                }
                
                return redirect()->intended('/')->with('success', 'Successfully registered and logged in with Google!');
            }
            
        } catch (\Exception $e) {
            \Log::error('Google OAuth Error: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all()
            ]);
            
            // Determine which page to redirect to based on action
            $redirectRoute = session('google_auth_action') === 'register' ? 'register' : 'login';
            return redirect()->route($redirectRoute)->with('error', 'Google authentication failed. Please try again.');
        }
    }

    

    /**
     * Check if user needs to complete their profile
     */
    private function needsProfileCompletion($user)
    {
        return empty($user->phone) || empty($user->address);
    }

    /**
     * Download and store avatar from social provider
     */
    private function downloadAndStoreAvatar($avatarUrl, $userId)
    {
        try {
            // Validate URL
            if (empty($avatarUrl) || !filter_var($avatarUrl, FILTER_VALIDATE_URL)) {
                return null;
            }
            
            // If using Cloudinary, prefer direct URL upload via SDK (no server-side download needed)
            $publicDisk = config('filesystems.disks.public.driver');
            if ($publicDisk === 'cloudinary') {
                try {
                    $result = Cloudinary::upload($avatarUrl, ['folder' => 'profile-pictures']);
                    $publicId = $result->getPublicId();
                    $format = $result->getExtension() ?: 'jpg';
                    // Ensure we return a path Flysystem can resolve
                    return $publicId . '.' . $format;
                } catch (\Throwable $e) {
                    \Log::warning('Cloudinary URL upload failed, falling back to HTTP download', [
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Download using Laravel HTTP client (handles SSL and timeouts)
            $response = Http::timeout(10)->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; AutoRepairShop/1.0)'
            ])->get($avatarUrl);

            if (!$response->ok()) {
                \Log::warning('Failed to download avatar: HTTP error', ['url' => $avatarUrl, 'status' => $response->status()]);
                return null;
            }

            $imageData = $response->body();
            if (empty($imageData)) {
                \Log::warning('Failed to download avatar: Empty body', ['url' => $avatarUrl]);
                return null;
            }

            // Infer extension from content-type
            $contentType = $response->header('Content-Type');
            $ext = 'jpg';
            if (is_string($contentType)) {
                if (str_contains($contentType, 'png')) { $ext = 'png'; }
                elseif (str_contains($contentType, 'jpeg') || str_contains($contentType, 'jpg')) { $ext = 'jpg'; }
                elseif (str_contains($contentType, 'gif')) { $ext = 'gif'; }
                elseif (str_contains($contentType, 'webp')) { $ext = 'webp'; }
            }

            // If using Cloudinary, upload via SDK using a temp file to avoid adapter fopen issues
            if ($publicDisk === 'cloudinary') {
                $tmp = tempnam(sys_get_temp_dir(), 'avatar_');
                if ($tmp !== false) {
                    file_put_contents($tmp, $imageData);
                    try {
                        $result = Cloudinary::uploadFile($tmp, ['folder' => 'profile-pictures']);
                        @unlink($tmp);
                        $secureUrl = method_exists($result, 'getSecurePath') ? $result->getSecurePath() : ($result->getPath() ?? null);
                        if ($secureUrl) {
                            return $secureUrl;
                        }
                    } catch (\Throwable $e) {
                        @unlink($tmp);
                        \Log::warning('Cloudinary uploadFile failed', ['error' => $e->getMessage()]);
                    }
                }
            }

            // Generate unique filename and store via configured disk (non-Cloudinary path)
            $filename = 'profile_' . $userId . '_' . time() . '.' . $ext;
            $path = 'profile-pictures/' . $filename;
            Storage::disk('public')->put($path, $imageData);
            \Log::info('Avatar downloaded successfully', ['url' => $avatarUrl, 'path' => $path]);
            return $path;
            
        } catch (\Exception $e) {
            // Log error but don't break the flow
            \Log::warning('Failed to download avatar: ' . $e->getMessage(), ['url' => $avatarUrl, 'user_id' => $userId]);
            return null;
        }
    }
}