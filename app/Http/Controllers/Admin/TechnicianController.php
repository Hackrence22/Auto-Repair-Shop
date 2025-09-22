<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Technician;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TechnicianController extends Controller
{
    /**
     * Get the shop(s) for the current owner
     */
    private function getOwnerShops()
    {
        $adminId = auth('admin')->id();
        $adminName = auth('admin')->user()->name;
        return Shop::where(function($q) use ($adminId, $adminName) {
            $q->where('admin_id', $adminId)
              ->orWhere(function($qq) use ($adminName) { 
                  $qq->whereNull('admin_id')->where('owner_name', $adminName); 
              });
        })->get();
    }

    /**
     * Check if owner can access a specific shop
     */
    private function canAccessShop($shopId)
    {
        $adminId = auth('admin')->id();
        $adminName = auth('admin')->user()->name;
        return Shop::where(function($q) use ($adminId, $adminName) {
            $q->where('admin_id', $adminId)
              ->orWhere(function($qq) use ($adminName) { 
                  $qq->whereNull('admin_id')->where('owner_name', $adminName); 
              });
        })->where('id', $shopId)->exists();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $isOwner = auth('admin')->user()?->isOwner();
        
        // Get shops for filtering
        if ($isOwner) {
            $shops = $this->getOwnerShops();
        } else {
            $shops = Shop::all();
        }
        
        // Build query
        $query = Technician::with('shop');
        
        if ($isOwner) {
            // For owners, filter by their shops
            $ownerShops = $this->getOwnerShops();
            if ($ownerShops->count() > 0) {
                $shopIds = $ownerShops->pluck('id')->toArray();
                $query->whereIn('shop_id', $shopIds);
            } else {
                // If owner has no shop, return empty result
                $query->where('id', 0);
            }
        }
        
        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('specialization', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        // Apply shop filter
        if ($request->filled('shop')) {
            $shopId = $request->shop;
            if ($isOwner) {
                // For owners, ensure they can only filter by their own shops
                if ($this->canAccessShop($shopId)) {
                    $query->where('shop_id', $shopId);
                }
            } else {
                // For admins, filter by any shop
                $query->where('shop_id', $shopId);
            }
        }
        
        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Apply availability filter
        if ($request->filled('availability')) {
            $query->where('is_available', $request->availability);
        }
        
        // Order and paginate
        $technicians = $query->orderBy('name')->paginate(15);
        
        // Append query parameters to pagination links
        $technicians->appends($request->query());
        
        return view('admin.technicians.index', compact('technicians', 'shops', 'isOwner'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $isOwner = auth('admin')->user()?->isOwner();
        
        if ($isOwner) {
            // For owners, get their associated shop using the helper method
            $shops = $this->getOwnerShops();
        } else {
            // For admins, get all shops
            $shops = Shop::all();
        }

        return view('admin.technicians.create', compact('shops', 'isOwner'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shop_id' => 'required|exists:shops,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:technicians,email',
            'phone' => 'required|string|max:20',
            'bio' => 'nullable|string',
            'specialization' => 'nullable|string|max:255',
            'experience_years' => 'nullable|integer|min:0|max:50',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'certifications' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive,on_leave',
            'working_hours_start' => 'nullable|date_format:H:i',
            'working_hours_end' => 'nullable|date_format:H:i',
            'working_days' => 'nullable|array',
            'working_days.*' => 'integer|min:1|max:7',
            'hourly_rate' => 'nullable|numeric|min:0',
            'is_available' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->all();
        $data['is_available'] = $request->has('is_available');

        // For owners, ensure they can only assign to their own shop
        $isOwner = auth('admin')->user()?->isOwner();
        if ($isOwner) {
            if (!$this->canAccessShop($data['shop_id'])) {
                return redirect()->back()->withErrors(['shop_id' => 'You can only assign technicians to your own shop.'])->withInput();
            }
        }

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('technicians', 'public');
            $data['profile_picture'] = $path;
        }

        Technician::create($data);

        return redirect()->route('admin.technicians.index')
            ->with('success', 'Technician created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Technician $technician)
    {
        // Check if owner can view this technician
        $isOwner = auth('admin')->user()?->isOwner();
        if ($isOwner) {
            if (!$this->canAccessShop($technician->shop_id)) {
                abort(403, 'Unauthorized access to technician.');
            }
        }

        $technician->load(['shop', 'appointments.service', 'appointments.user']);

        return view('admin.technicians.show', compact('technician'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Technician $technician)
    {
        // Check if owner can edit this technician
        $isOwner = auth('admin')->user()?->isOwner();
        if ($isOwner) {
            if (!$this->canAccessShop($technician->shop_id)) {
                abort(403, 'Unauthorized access to technician.');
            }
        }
        
        if ($isOwner) {
            // For owners, get their associated shop using the helper method
            $shops = $this->getOwnerShops();
        } else {
            // For admins, get all shops
            $shops = Shop::all();
        }

        return view('admin.technicians.edit', compact('technician', 'shops', 'isOwner'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Technician $technician)
    {
        // Check if owner can update this technician
        $isOwner = auth('admin')->user()?->isOwner();
        if ($isOwner) {
            if (!$this->canAccessShop($technician->shop_id)) {
                abort(403, 'Unauthorized access to technician.');
            }
        }

        $validator = Validator::make($request->all(), [
            'shop_id' => 'required|exists:shops,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:technicians,email,' . $technician->id,
            'phone' => 'required|string|max:20',
            'bio' => 'nullable|string',
            'specialization' => 'nullable|string|max:255',
            'experience_years' => 'nullable|integer|min:0|max:50',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'certifications' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive,on_leave',
            'working_hours_start' => 'nullable|date_format:H:i',
            'working_hours_end' => 'nullable|date_format:H:i',
            'working_days' => 'nullable|array',
            'working_days.*' => 'integer|min:1|max:7',
            'hourly_rate' => 'nullable|numeric|min:0',
            'is_available' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->all();
        $data['is_available'] = $request->has('is_available');

        // For owners, ensure they can only assign to their own shop
        $isOwner = auth('admin')->user()?->isOwner();
        if ($isOwner) {
            if (!$this->canAccessShop($data['shop_id'])) {
                return redirect()->back()->withErrors(['shop_id' => 'You can only assign technicians to your own shop.'])->withInput();
            }
        }

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture
            if ($technician->profile_picture) {
                Storage::disk('public')->delete($technician->profile_picture);
            }
            
            $path = $request->file('profile_picture')->store('technicians', 'public');
            $data['profile_picture'] = $path;
        }

        $technician->update($data);

        return redirect()->route('admin.technicians.index')
            ->with('success', 'Technician updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Technician $technician)
    {
        // Check if owner can delete this technician
        $isOwner = auth('admin')->user()?->isOwner();
        if ($isOwner) {
            if (!$this->canAccessShop($technician->shop_id)) {
                abort(403, 'Unauthorized access to technician.');
            }
        }

        // Delete profile picture
        if ($technician->profile_picture) {
            Storage::disk('public')->delete($technician->profile_picture);
        }

        $technician->delete();

        return redirect()->route('admin.technicians.index')
            ->with('success', 'Technician deleted successfully!');
    }

    /**
     * Toggle technician availability
     */
    public function toggleAvailability(Technician $technician)
    {
        // Check if owner can modify this technician
        $isOwner = auth('admin')->user()?->isOwner();
        if ($isOwner) {
            if (!$this->canAccessShop($technician->shop_id)) {
                abort(403, 'Unauthorized access to technician.');
            }
        }

        $technician->update(['is_available' => !$technician->is_available]);

        return response()->json([
            'success' => true,
            'is_available' => $technician->is_available,
            'message' => 'Technician availability updated successfully!'
        ]);
    }

    /**
     * Get technicians for a specific shop (AJAX)
     */
    public function getTechniciansByShop(Request $request)
    {
        $shopId = $request->get('shop_id');
        
        $technicians = Technician::where('shop_id', $shopId)
            ->where('status', 'active')
            ->where('is_available', true)
            ->orderBy('name')
            ->get(['id', 'name', 'specialization']);

        return response()->json($technicians);
    }

    /**
     * Get technicians for a specific shop and date (AJAX)
     */
    public function getTechniciansByShopAndDate(Request $request)
    {
        $shopId = $request->get('shop_id');
        $date = $request->get('date');
        
        $technicians = Technician::where('shop_id', $shopId)
            ->where('status', 'active')
            ->where('is_available', true)
            ->orderBy('name')
            ->get(['id', 'name', 'specialization', 'working_days']);
        
        // Filter by working days if date is provided
        if ($date) {
            $dayOfWeek = \Carbon\Carbon::parse($date)->dayOfWeek;
            // Convert Carbon dayOfWeek (0=Sunday, 1=Monday, etc.) to our format (1=Monday, 7=Sunday)
            $dayNumber = $dayOfWeek == 0 ? 7 : $dayOfWeek;
            
            $technicians = $technicians->filter(function($technician) use ($dayNumber) {
                // If technician has no working days set, assume they work all days
                if (empty($technician->working_days)) {
                    return true;
                }
                // Check if the selected day is in technician's working days
                return in_array($dayNumber, $technician->working_days);
            });
        }

        return response()->json($technicians);
    }
}
