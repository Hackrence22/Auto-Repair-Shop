<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SlotSetting;
use Illuminate\Http\Request;

class SlotSettingController extends Controller
{
    public function index()
    {
        $q = request('q');
        $slotSettings = SlotSetting::with('shop')
            ->when(auth('admin')->user()?->isOwner(), function($query) {
                $adminId = auth('admin')->id();
                $adminName = auth('admin')->user()->name;
                $query->whereHas('shop', function($s) use ($adminId, $adminName) {
                    $s->where('admin_id', $adminId)
                      ->orWhere(function($ss) use ($adminName) { $ss->whereNull('admin_id')->where('owner_name', $adminName); });
                });
            })
            ->when($q, function($query) use ($q) {
                $query->whereHas('shop', function($s) use ($q) { $s->where('name', 'like', "%$q%"); })
                      ->orWhere('slots_per_hour', 'like', "%$q%")
                      ->orWhere('start_time', 'like', "%$q%")
                      ->orWhere('end_time', 'like', "%$q%");
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();
        return view('admin.slot-settings.index', compact('slotSettings'));
    }

    public function create()
    {
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
        return view('admin.slot-settings.create', compact('shops'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'shop_id' => 'required|exists:shops,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'slots_per_hour' => 'required|integer|min:1',
            'is_active' => 'boolean'
        ]);

        $validated['is_active'] = $request->boolean('is_active', false);

        SlotSetting::create($validated);

        return redirect()->route('admin.slot-settings.index')
            ->with('success', 'Slot setting created successfully.');
    }

    public function edit(SlotSetting $slotSetting)
    {
        if (auth('admin')->user()?->isOwner()) {
            $shop = $slotSetting->shop;
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
        return view('admin.slot-settings.edit', compact('slotSetting', 'shops'));
    }

    public function show(SlotSetting $slotSetting)
    {
        if (auth('admin')->user()?->isOwner()) {
            $shop = $slotSetting->shop;
            $ownerOk = ($shop && ($shop->admin_id === auth('admin')->id() || (!$shop->admin_id && $shop->owner_name === auth('admin')->user()->name)));
            if (!$ownerOk) { abort(403); }
        }
        $slotSetting->load('shop');
        return view('admin.slot-settings.show', compact('slotSetting'));
    }

    public function update(Request $request, SlotSetting $slotSetting)
    {
        if (auth('admin')->user()?->isOwner()) {
            $shop = $slotSetting->shop;
            $ownerOk = ($shop && ($shop->admin_id === auth('admin')->id() || (!$shop->admin_id && $shop->owner_name === auth('admin')->user()->name)));
            if (!$ownerOk) { abort(403); }
        }
        $validated = $request->validate([
            'shop_id' => 'required|exists:shops,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'slots_per_hour' => 'required|integer|min:1',
            'is_active' => 'boolean'
        ]);

        $validated['is_active'] = $request->boolean('is_active', false);

        $slotSetting->update($validated);

        return redirect()->route('admin.slot-settings.index')
            ->with('success', 'Slot setting updated successfully.');
    }

    public function destroy(SlotSetting $slotSetting)
    {
        $slotSetting->delete();
        return redirect()->route('admin.slot-settings.index')
            ->with('success', 'Slot setting deleted successfully.');
    }
}