<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feedback;
use App\Models\Notification;
use App\Models\Admin;

class FeedbackController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'message' => 'required|string|max:2000',
        ]);

        // Overwrite with authenticated user info if available
        if (auth()->check()) {
            $validated['name'] = auth()->user()->name;
            $validated['email'] = auth()->user()->email;
        }

        Feedback::create($validated);
        // Notify admin(s) of new feedback
        $admin = Admin::first();
        $userId = auth()->check() ? auth()->id() : null;
        if ($admin) {
            Notification::create([
                'admin_id' => $admin->id,
                'user_id' => $userId, // Set user_id if available
                'type' => 'feedback',
                'title' => 'New Feedback Submitted',
                'message' => $validated['message'], // Store the actual feedback message
                'data' => [
                    'name' => $validated['name'],
                    'email' => $validated['email'] ?? null,
                ],
            ]);
        }

        return back()->with('success', 'Thank you for your feedback!');
    }
}
