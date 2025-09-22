<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ShopController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $q = request('q');
        $shops = Shop::with(['services', 'slotSettings', 'appointments'])
            ->withAvg('ratings', 'rating')
            ->withCount('ratings')
            ->when(auth('admin')->user()?->isOwner(), function($query) {
                $adminId = auth('admin')->id();
                $adminName = auth('admin')->user()->name;
                $query->where(function($q) use ($adminId, $adminName) {
                    $q->where('admin_id', $adminId)
                      ->orWhere(function($qq) use ($adminName) {
                          $qq->whereNull('admin_id')->where('owner_name', $adminName);
                      });
                });
            })
            ->when($q, function($query) use ($q) {
                $query->where(function($sub) use ($q) {
                    $sub->where('name', 'like', "%$q%")
                        ->orWhere('owner_name', 'like', "%$q%")
                        ->orWhere('city', 'like', "%$q%")
                        ->orWhere('address', 'like', "%$q%")
                        ->orWhere('email', 'like', "%$q%")
                        ->orWhere('phone', 'like', "%$q%")
                        ->orWhere('description', 'like', "%$q%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();
        return view('admin.shops.index', compact('shops'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.shops.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'country' => 'required|string|max:100',
            'map_embed_url' => 'nullable|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'opening_time' => 'required|date_format:H:i',
            'closing_time' => 'required|date_format:H:i|after:opening_time',
            'working_days' => 'required|array|min:1',
            'working_days.*' => 'integer|between:1,7',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        try {
            // Handle image upload
            if ($request->hasFile('image')) {
                $validated['image'] = $request->file('image')->store('shop-images', 'public');
            }

            // Extract src URL from iframe code if provided
            if (!empty($validated['map_embed_url'])) {
                $validated['map_embed_url'] = $this->extractSrcFromIframe($validated['map_embed_url']);
            }

            if (auth('admin')->user()?->isOwner()) {
                // Force association to current owner and set owner_name from owner profile
                $validated['admin_id'] = auth('admin')->id();
                $validated['owner_name'] = auth('admin')->user()->name;
                // Default location values for owners if not provided
                $validated['city'] = $validated['city'] ?: 'Surigao City';
                $validated['state'] = $validated['state'] ?: 'Surigao Del Norte';
                $validated['zip_code'] = $validated['zip_code'] ?: '8400';
            }
            $shop = Shop::create($validated);

            Log::info('Shop created successfully', [
                'shop_id' => $shop->id,
                'name' => $shop->name,
                'admin_id' => auth('admin')->id()
            ]);

            return redirect()->route('admin.shops.index')
                ->with('success', 'Shop created successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create shop', [
                'error' => $e->getMessage(),
                'admin_id' => auth('admin')->id()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create shop. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Shop $shop)
    {
        if (auth('admin')->user()?->isOwner()) {
            $ownerOk = ($shop->admin_id === auth('admin')->id()) || (is_null($shop->admin_id) && $shop->owner_name === auth('admin')->user()->name);
            if (!$ownerOk) { abort(403); }
        }
        $shop->load([
            'services', 
            'slotSettings', 
            'appointments',
            'ratings' => function($q) { $q->latest()->with('user:id,name,profile_picture'); },
            'admin'
        ])->loadAvg('ratings', 'rating')->loadCount('ratings');
        return view('admin.shops.show', compact('shop'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Shop $shop)
    {
        if (auth('admin')->user()?->isOwner()) {
            $ownerOk = ($shop->admin_id === auth('admin')->id()) || (is_null($shop->admin_id) && $shop->owner_name === auth('admin')->user()->name);
            if (!$ownerOk) { abort(403); }
        }
        return view('admin.shops.edit', compact('shop'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Shop $shop)
    {
        if (auth('admin')->user()?->isOwner()) {
            $ownerOk = ($shop->admin_id === auth('admin')->id()) || (is_null($shop->admin_id) && $shop->owner_name === auth('admin')->user()->name);
            if (!$ownerOk) { abort(403); }
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'country' => 'required|string|max:100',
            'map_embed_url' => 'nullable|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'opening_time' => 'required|date_format:H:i',
            'closing_time' => 'required|date_format:H:i|after:opening_time',
            'working_days' => 'required|array|min:1',
            'working_days.*' => 'integer|between:1,7',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        try {
            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($shop->image) {
                    Storage::disk('public')->delete($shop->image);
                }
                $validated['image'] = $request->file('image')->store('shop-images', 'public');
            }

            // Extract src URL from iframe code if provided
            if (!empty($validated['map_embed_url'])) {
                $validated['map_embed_url'] = $this->extractSrcFromIframe($validated['map_embed_url']);
            }

            $shop->update($validated);

            Log::info('Shop updated successfully', [
                'shop_id' => $shop->id,
                'name' => $shop->name,
                'admin_id' => auth('admin')->id()
            ]);

            return redirect()->route('admin.shops.index')
                ->with('success', 'Shop updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update shop', [
                'error' => $e->getMessage(),
                'shop_id' => $shop->id,
                'admin_id' => auth('admin')->id()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update shop. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shop $shop)
    {
        if (auth('admin')->user()?->isOwner()) {
            $ownerOk = ($shop->admin_id === auth('admin')->id()) || (is_null($shop->admin_id) && $shop->owner_name === auth('admin')->user()->name);
            if (!$ownerOk) { abort(403); }
        }
        try {
            // Delete shop image if exists
            if ($shop->image) {
                Storage::disk('public')->delete($shop->image);
            }

            $shopName = $shop->name;
            $shop->delete();

            Log::info('Shop deleted successfully', [
                'shop_name' => $shopName,
                'admin_id' => auth('admin')->id()
            ]);

            return redirect()->route('admin.shops.index')
                ->with('success', 'Shop deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete shop', [
                'error' => $e->getMessage(),
                'shop_id' => $shop->id,
                'admin_id' => auth('admin')->id()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to delete shop. Please try again.');
        }
    }

    /**
     * Toggle shop status
     */
    public function toggleStatus(Shop $shop)
    {
        if (auth('admin')->user()?->isOwner()) {
            $ownerOk = ($shop->admin_id === auth('admin')->id()) || (is_null($shop->admin_id) && $shop->owner_name === auth('admin')->user()->name);
            if (!$ownerOk) { abort(403); }
        }
        try {
            $shop->update(['is_active' => !$shop->is_active]);

            $status = $shop->is_active ? 'activated' : 'deactivated';
            return redirect()->back()
                ->with('success', "Shop {$status} successfully.");
        } catch (\Exception $e) {
            Log::error('Failed to toggle shop status', [
                'error' => $e->getMessage(),
                'shop_id' => $shop->id,
                'admin_id' => auth('admin')->id()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to update shop status.');
        }
    }

    /**
     * Extract src URL from iframe code.
     *
     * @param string $iframeCode The iframe HTML code.
     * @return string|null The extracted src URL or null if not found.
     */
    private function extractSrcFromIframe($iframeCode)
    {
        $pattern = '/src="([^"]+)"/';
        if (preg_match($pattern, $iframeCode, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
