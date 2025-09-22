<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class AdminFeedbackController extends Controller
{
    public function index()
    {
        $q = request('q');
        $feedbacks = Feedback::when($q, function($query) use ($q) {
                $query->where(function($sub) use ($q) {
                    $sub->where('name', 'like', "%$q%")
                        ->orWhere('email', 'like', "%$q%")
                        ->orWhere('message', 'like', "%$q%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();
        return view('admin.feedback.index', compact('feedbacks'));
    }

    public function show(Feedback $feedback)
    {
        return view('admin.feedback.show', compact('feedback'));
    }

    public function reply(Request $request, Feedback $feedback)
    {
        $request->validate(['reply' => 'required|string|max:2000']);
        $feedback->reply = $request->reply;
        $feedback->save();
        // Find user by feedback email
        $user = User::where('email', $feedback->email)->first();
        if ($user) {
            Notification::create([
                'user_id' => $user->id,
                'admin_id' => auth('admin')->id(), // Set the sender as the replying admin
                'type' => 'feedback',
                'title' => 'Feedback Reply',
                'message' => 'Admin replied to your feedback: ' . $request->reply,
                'data' => [],
            ]);
        }
        return redirect()->route('admin.feedback.show', $feedback)
            ->with('success', 'Reply sent to user.');
    }

    public function destroy(Feedback $feedback)
    {
        $feedback->delete();
        return redirect()->route('admin.feedback.index')->with('success', 'Feedback deleted successfully.');
    }
} 