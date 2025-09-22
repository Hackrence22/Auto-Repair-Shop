<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AdminProfileController extends Controller
{
    public function show()
    {
        return view('admin.profile.show', [
            'admin' => auth('admin')->user()
        ]);
    }

    public function edit()
    {
        return view('admin.profile.edit', [
            'admin' => auth('admin')->user()
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email,' . auth('admin')->id(),
        ]);

        auth('admin')->user()->update($validated);

        return redirect()->route('admin.profile')
            ->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password:admin'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $admin = auth('admin')->user();
        $admin->update([
            'password' => Hash::make($validated['password'])
        ]);

        return redirect()->route('admin.profile.show')
            ->with('success', 'Password updated successfully.');
    }

    public function updatePicture(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:102400' // 100MB = 102400KB
        ]);

        $admin = auth('admin')->user();

        try {
            // Delete old profile picture if exists
            if ($admin->profile_picture) {
                Storage::disk('public')->delete($admin->profile_picture);
            }

            // Store new profile picture
            $path = $request->file('profile_picture')->store('profile-pictures', 'public');
            $admin->update(['profile_picture' => $path]);

            Log::info('Profile picture updated successfully', [
                'admin_id' => $admin->id,
                'filename' => basename($path),
                'path' => $path
            ]);

            return redirect()->route('admin.profile')
                ->with('success', 'Profile picture updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update profile picture', [
                'error' => $e->getMessage(),
                'admin_id' => $admin->id
            ]);

            return redirect()->route('admin.profile')
                ->with('error', 'Failed to update profile picture. Please try again.');
        }
    }
} 