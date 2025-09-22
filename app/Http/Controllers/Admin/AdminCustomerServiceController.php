<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerService;
use App\Models\Notification;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminCustomerServiceController extends Controller
{
    public function __construct()
    {
        // Routes are already protected by 'auth:admin' middleware
    }

    public function index(Request $request)
    {
        $query = CustomerService::with(['user', 'shop', 'assignedAdmin']);

        // Scope non-super admins to their own shop requests only
        $adminId = Auth::id();
        $managesAnyShop = Shop::where('admin_id', $adminId)->exists();
        if ($managesAnyShop) {
            $query->where(function ($scoped) use ($adminId) {
                $scoped->whereHas('shop', function ($q) use ($adminId) {
                    $q->where('admin_id', $adminId);
                })->orWhere('assigned_admin_id', $adminId);
            });
        }

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('shop_id')) {
            $query->where('shop_id', $request->shop_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $customerServices = $query->orderBy('created_at', 'desc')->paginate(15);
        $shops = $managesAnyShop ? Shop::where('admin_id', $adminId)->get() : collect();

        return view('admin.customer-service.index', compact('customerServices', 'shops'));
    }

    public function show(CustomerService $customerService)
    {
        // Allow all authenticated admins to view

        return view('admin.customer-service.show', compact('customerService'));
    }

    public function edit(CustomerService $customerService)
    {
        // Allow all authenticated admins to edit

        return view('admin.customer-service.edit', compact('customerService'));
    }

    public function update(Request $request, CustomerService $customerService)
    {
        // Allow all authenticated admins to update

        $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
            'admin_reply' => 'nullable|string|max:1000',
            'assigned_admin_id' => 'nullable|exists:admins,id',
        ]);

        $oldStatus = $customerService->status;
        $oldAssignedAdmin = $customerService->assigned_admin_id;
        $oldReply = $customerService->admin_reply;

        $customerService->update([
            'status' => $request->status,
            'admin_reply' => $request->admin_reply,
            'assigned_admin_id' => $request->assigned_admin_id,
        ]);

        // Mark as resolved if status is resolved
        if ($request->status === 'resolved' && $oldStatus !== 'resolved') {
            $customerService->markAsResolved();
        }

        // Create notification for user about status change
        $notificationMessage = "Your {$customerService->category} request '{$customerService->subject}' status has been updated to {$request->status}";
        
        if ($request->admin_reply && $request->admin_reply !== $oldReply) {
            $notificationMessage = "Admin has responded to your {$customerService->category} request '{$customerService->subject}'";
        }

        Notification::create([
            'user_id' => $customerService->user_id,
            'shop_id' => $customerService->shop_id,
            'type' => 'customer_service_status_updated',
            'title' => 'Customer Service Request Updated',
            'message' => $notificationMessage,
            'data' => [
                'customer_service_id' => $customerService->id,
                'status' => $request->status,
                'admin_reply' => $request->admin_reply,
                'category' => $customerService->category,
            ],
        ]);

        // Notify newly assigned admin
        if ($request->assigned_admin_id && $request->assigned_admin_id !== $oldAssignedAdmin) {
            Notification::create([
                'admin_id' => $request->assigned_admin_id,
                'shop_id' => $customerService->shop_id,
                'type' => 'customer_service_assigned',
                'title' => 'Customer Service Request Assigned',
                'message' => "You have been assigned a new {$customerService->category} request: {$customerService->subject}",
                'data' => [
                    'customer_service_id' => $customerService->id,
                    'user_name' => $customerService->user->name,
                    'priority' => $customerService->priority,
                    'category' => $customerService->category,
                ],
            ]);
        }

        return redirect()->route('admin.customer-service.show', $customerService)
            ->with('success', 'Customer service request updated successfully.');
    }

    public function assignToMe(CustomerService $customerService)
    {
        // Allow all authenticated admins to self-assign

        $customerService->assignToAdmin(Auth::id());

        // Notify user about assignment
        Notification::create([
            'user_id' => $customerService->user_id,
            'shop_id' => $customerService->shop_id,
            'type' => 'customer_service_assigned',
            'title' => 'Customer Service Request Assigned',
            'message' => "Your {$customerService->category} request '{$customerService->subject}' has been assigned to an admin",
            'data' => [
                'customer_service_id' => $customerService->id,
                'category' => $customerService->category,
            ],
        ]);

        return redirect()->back()->with('success', 'Customer service request assigned to you successfully.');
    }

    public function dashboard()
    {
        $query = CustomerService::query();
        $adminId = Auth::id();
        $managesAnyShop = Shop::where('admin_id', $adminId)->exists();
        if ($managesAnyShop) {
            $query->where(function ($scoped) use ($adminId) {
                $scoped->whereHas('shop', function ($q) use ($adminId) {
                    $q->where('admin_id', $adminId);
                })->orWhere('assigned_admin_id', $adminId);
            });
        }

        $totalRequests = $query->count();
        $openRequests = $query->where('status', 'open')->count();
        $inProgressRequests = $query->where('status', 'in_progress')->count();
        $resolvedRequests = $query->count() - $openRequests - $inProgressRequests;

        // Priority breakdown
        $urgentRequests = $query->where('priority', 'urgent')->where('status', '!=', 'closed')->count();
        $highPriorityRequests = $query->where('priority', 'high')->where('status', '!=', 'closed')->count();

        // Category breakdown
        $categoryBreakdown = $query->selectRaw('category, count(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();

        // Recent requests
        $recentRequests = $query->with(['user', 'shop'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.customer-service.dashboard', compact(
            'totalRequests',
            'openRequests',
            'inProgressRequests',
            'resolvedRequests',
            'urgentRequests',
            'highPriorityRequests',
            'categoryBreakdown',
            'recentRequests'
        ));
    }
}
