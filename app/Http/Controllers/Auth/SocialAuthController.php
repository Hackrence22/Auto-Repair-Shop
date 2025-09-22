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
                if (!$user->avatar) {
                    $updateData['avatar'] = $googleUser->getAvatar();
                }
                // Sync avatar to profile_picture for display
                if (!$user->profile_picture) {
                    $updateData['profile_picture'] = $this->downloadAndStoreAvatar($googleUser->getAvatar(), $user->id);
                }
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
                    'avatar' => $googleUser->getAvatar(),
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
                
                // Download and store avatar as profile_picture after user is created
                $profilePicturePath = $this->downloadAndStoreAvatar($googleUser->getAvatar(), $user->id);
                if ($profilePicturePath) {
                    $user->update(['profile_picture' => $profilePicturePath]);
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
            
            // Download the image with timeout
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10, // 10 second timeout
                    'user_agent' => 'Mozilla/5.0 (compatible; AutoRepairShop/1.0)',
                ]
            ]);
            
            $imageData = file_get_contents($avatarUrl, false, $context);
            
            if ($imageData === false || empty($imageData)) {
                \Log::warning('Failed to download avatar: Empty or invalid response', ['url' => $avatarUrl]);
                return null;
            }
            
            // Validate image data (basic check)
            if (strlen($imageData) < 100) { // Too small to be a real image
                \Log::warning('Failed to download avatar: Image too small', ['url' => $avatarUrl, 'size' => strlen($imageData)]);
                return null;
            }
            
            // Generate unique filename
            $filename = 'profile_' . $userId . '_' . time() . '.jpg';
            
            // Store directly to storage
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