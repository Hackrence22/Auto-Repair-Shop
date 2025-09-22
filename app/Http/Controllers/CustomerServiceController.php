<?php

namespace App\Http\Controllers;

use App\Models\CustomerService;
use App\Models\Shop;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $customerServices = CustomerService::where('user_id', Auth::id())
            ->with(['shop', 'assignedAdmin'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('customer-service.index', compact('customerServices'));
    }

    public function create()
    {
        $shops = Shop::all();
        return view('customer-service.create', compact('shops'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'shop_id' => 'required|exists:shops,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'category' => 'required|in:booking,shop,payment,appointment,other',
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        $customerService = CustomerService::create([
            'user_id' => Auth::id(),
            'shop_id' => $request->shop_id,
            'subject' => $request->subject,
            'message' => $request->message,
            'category' => $request->category,
            'priority' => $request->priority,
        ]);

        // Create notification for all admins of the shop
        $shop = Shop::find($request->shop_id);
        if ($shop && $shop->admin_id) {
            Notification::create([
                'admin_id' => $shop->admin_id,
                'shop_id' => $shop->id,
                'type' => 'customer_service_request',
                'title' => 'New Customer Service Request',
                'message' => "New {$request->priority} priority {$request->category} request: {$request->subject}",
                'data' => [
                    'customer_service_id' => $customerService->id,
                    'user_name' => Auth::user()->name,
                    'category' => $request->category,
                    'priority' => $request->priority,
                ],
            ]);
        }

        return redirect()->route('customer-service.index')
            ->with('success', 'Your customer service request has been submitted successfully.');
    }

    public function show(CustomerService $customerService)
    {
        // Ensure user can only view their own requests
        if ($customerService->user_id !== Auth::id()) {
            abort(403);
        }

        return view('customer-service.show', compact('customerService'));
    }
}
