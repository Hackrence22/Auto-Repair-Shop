<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminPaymentMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $q = request('q');
        $paymentMethods = PaymentMethod::with('shop')->ordered()
            ->when(auth('admin')->user()?->isOwner(), function($query) {
                $admin = auth('admin')->user();
                $ownerShopIds = \App\Models\Shop::query()
                    ->where('admin_id', $admin->id)
                    ->orWhere(function($q) use ($admin) {
                        $q->whereNull('admin_id')->where('owner_name', $admin->name);
                    })
                    ->pluck('id');
                $query->whereIn('shop_id', $ownerShopIds);
            })
            ->when($q, function($query) use ($q) {
                $query->where(function($sub) use ($q) {
                    $sub->where('name', 'like', "%$q%")
                        ->orWhere('role_type', 'like', "%$q%")
                        ->orWhere('account_name', 'like', "%$q%")
                        ->orWhere('account_number', 'like', "%$q%")
                        ->orWhere('description', 'like', "%$q%");
                });
            })
            ->paginate(15)
            ->withQueryString();
        return view('admin.payment-methods.index', compact('paymentMethods'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.payment-methods.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'shop_id' => 'required|exists:shops,id',
            'name' => 'required|string|max:255|unique:payment_methods',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'account_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'role_type' => 'required|in:gcash,paymaya,cash',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        // Owner enforcement: ensure selected shop belongs to current owner
        if (auth('admin')->user()?->isOwner()) {
            $shop = \App\Models\Shop::find($validated['shop_id']);
            $ownerOk = $shop && ($shop->admin_id === auth('admin')->id() || (!$shop->admin_id && $shop->owner_name === auth('admin')->user()->name));
            if (!$ownerOk) { abort(403); }
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('payment-methods', 'public');
            // Store only relative path or filename consistently with getImageUrlAttribute
            $validated['image'] = basename($imagePath);
        }

        PaymentMethod::create($validated);

        return redirect()->route('admin.payment-methods.index')
            ->with('success', 'Payment method created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(PaymentMethod $paymentMethod)
    {
        return view('admin.payment-methods.show', compact('paymentMethod'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PaymentMethod $paymentMethod)
    {
        if (auth('admin')->user()?->isOwner()) {
            $shop = $paymentMethod->shop;
            $ownerOk = ($shop && ($shop->admin_id === auth('admin')->id() || (!$shop->admin_id && $shop->owner_name === auth('admin')->user()->name)));
            if (!$ownerOk) { abort(403); }
        }
        $shopsQuery = \App\Models\Shop::active()->ordered();
        if (auth('admin')->user()?->isOwner()) {
            $adminId = auth('admin')->id();
            $adminName = auth('admin')->user()->name;
            $shopsQuery->where(function($q) use ($adminId, $adminName) {
                $q->where('admin_id', $adminId)
                  ->orWhere(function($qq) use ($adminName) { $qq->whereNull('admin_id')->where('owner_name', $adminName); });
            });
        }
        $shops = $shopsQuery->get();
        return view('admin.payment-methods.edit', compact('paymentMethod', 'shops'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        if (auth('admin')->user()?->isOwner()) {
            $shop = $paymentMethod->shop;
            $ownerOk = ($shop && ($shop->admin_id === auth('admin')->id() || (!$shop->admin_id && $shop->owner_name === auth('admin')->user()->name)));
            if (!$ownerOk) { abort(403); }
        }
        $validated = $request->validate([
            'shop_id' => 'required|exists:shops,id',
            'name' => 'required|string|max:255|unique:payment_methods,name,' . $paymentMethod->id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'account_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'role_type' => 'required|in:gcash,paymaya,cash',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($paymentMethod->image) {
                Storage::disk('public')->delete($paymentMethod->image);
            }
            $imagePath = $request->file('image')->store('payment-methods', 'public');
            $validated['image'] = basename($imagePath);
        }

        $paymentMethod->update($validated);

        return redirect()->route('admin.payment-methods.index')
            ->with('success', 'Payment method updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentMethod $paymentMethod)
    {
        // Check if payment method is being used by any appointments
        if ($paymentMethod->appointments()->count() > 0) {
            return redirect()->route('admin.payment-methods.index')
                ->with('error', 'Cannot delete payment method that is being used by appointments.');
        }

        // Delete image if exists
        if ($paymentMethod->image) {
            ImageService::deleteImage($paymentMethod->image);
        }

        $paymentMethod->delete();

        return redirect()->route('admin.payment-methods.index')
            ->with('success', 'Payment method deleted successfully!');
    }

    /**
     * Toggle the active status of a payment method
     */
    public function toggleStatus(PaymentMethod $paymentMethod)
    {
        $paymentMethod->update([
            'is_active' => !$paymentMethod->is_active
        ]);

        $status = $paymentMethod->is_active ? 'activated' : 'deactivated';
        return redirect()->route('admin.payment-methods.index')
            ->with('success', "Payment method {$status} successfully!");
    }
}
