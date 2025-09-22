<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function markAllRead(Request $request)
    {
        if (auth('admin')->check()) {
            \App\Models\Notification::where('admin_id', auth('admin')->id())->where('is_read', false)->update(['is_read' => true]);
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true]);
            }
            return redirect()->back()->with('success', 'All notifications marked as read.');
        } elseif (auth()->check()) {
            \App\Models\Notification::where('user_id', auth()->id())->where('is_read', false)->update(['is_read' => true]);
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true]);
            }
            return redirect()->back()->with('success', 'All notifications marked as read.');
        }
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => false]);
        }
        return redirect()->back()->with('error', 'Unauthorized.');
    }

    public function index()
    {
        $q = request('q');
        if (auth('admin')->check()) {
            // Keep admin index behavior per your last change
            $notifications = \App\Models\Notification::where('admin_id', auth('admin')->id())
                ->latest()
                ->paginate(15)
                ->withQueryString();
            return view('admin.notifications.index', compact('notifications'));
        } else {
            // Add search for user-side view-all notifications
            $notifications = \App\Models\Notification::where('user_id', auth()->id())
                ->when($q, function($query) use ($q) {
                    $query->where(function($sub) use ($q) {
                        $sub->where('title', 'like', "%$q%")
                            ->orWhere('message', 'like', "%$q%");
                    });
                })
                ->latest()
                ->paginate(15)
                ->withQueryString();
            return view('notifications.index', compact('notifications'));
        }
    }

    public function adminIndex()
    {
        $q = request('q');
        $notifications = \App\Models\Notification::where('admin_id', auth('admin')->id())
            ->when($q, function($query) use ($q) {
                $query->where(function($sub) use ($q) {
                    $sub->where('title', 'like', "%$q%")
                        ->orWhere('message', 'like', "%$q%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();
        return view('admin.notifications.index', compact('notifications'));
    }

    public function toggleRead($id)
    {
        $notification = Notification::findOrFail($id);
        if ((auth('admin')->check() && $notification->admin_id == auth('admin')->id()) || (auth()->check() && $notification->user_id == auth()->id())) {
            $notification->is_read = !$notification->is_read;
            $notification->save();
            
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['success' => true]);
            }
            
            return redirect()->back()->with('success', 'Notification status updated successfully.');
        }
        
        if (request()->expectsJson() || request()->ajax()) {
            return response()->json(['success' => false], 403);
        }
        
        return redirect()->back()->with('error', 'Unauthorized.');
    }

    public function destroy($id)
    {
        $notification = Notification::findOrFail($id);
        if ((auth('admin')->check() && $notification->admin_id == auth('admin')->id()) || (auth()->check() && $notification->user_id == auth()->id())) {
            $notification->delete();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 403);
    }

    public function deleteAll()
    {
        if (auth('admin')->check()) {
            \App\Models\Notification::where('admin_id', auth('admin')->id())->delete();
            return redirect()->back()->with('success', 'All notifications deleted.');
        }
        return redirect()->back()->with('error', 'Unauthorized.');
    }

    public function markAllUnread(Request $request)
    {
        if (auth('admin')->check()) {
            \App\Models\Notification::where('admin_id', auth('admin')->id())->update(['is_read' => false]);
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true]);
            }
            return redirect()->back()->with('success', 'All notifications marked as unread.');
        } elseif (auth()->check()) {
            \App\Models\Notification::where('user_id', auth()->id())->update(['is_read' => false]);
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true]);
            }
            return redirect()->back()->with('success', 'All notifications marked as unread.');
        }
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => false]);
        }
        return redirect()->back()->with('error', 'Unauthorized.');
    }

    public function senderInfo($id)
    {
        $notification = \App\Models\Notification::findOrFail($id);
        $avatar = asset('images/default-profile.png');
        $email = null;
        $name = 'System';

        // If notification has admin_id, show admin info
        if ($notification->admin_id) {
            $admin = \App\Models\Admin::find($notification->admin_id);
            if ($admin) {
                $name = $admin->name;
                $email = $admin->email;
                if ($admin->profile_picture && \Storage::disk('public')->exists($admin->profile_picture)) {
                    $avatar = \Storage::disk('public')->url($admin->profile_picture);
                }
            }
        } 
        // If notification has user_id and no admin_id, show user info
        elseif ($notification->user_id) {
            $user = \App\Models\User::find($notification->user_id);
            if ($user) {
                $name = $user->name;
                $email = $user->email;
                if ($user->profile_picture && \Storage::disk('public')->exists($user->profile_picture)) {
                    $avatar = \Storage::disk('public')->url($user->profile_picture);
                }
            }
        }
        
        return response()->json([
            'avatar' => $avatar,
            'email' => $email,
            'name' => $name,
        ]);
    }

    public function show($id)
    {
        $notification = \App\Models\Notification::findOrFail($id);
        return response()->json($notification);
    }
} 