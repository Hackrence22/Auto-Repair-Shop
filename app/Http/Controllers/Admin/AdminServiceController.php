<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class AdminServiceController extends Controller
{
    public function index()
    {
        $q = request('q');
        $services = Service::with('shop')
            ->when(auth('admin')->user()?->isOwner(), function($query) {
                $adminId = auth('admin')->id();
                $adminName = auth('admin')->user()->name;
                $query->whereHas('shop', function($s) use ($adminId, $adminName) {
                    $s->where('admin_id', $adminId)
                      ->orWhere(function($ss) use ($adminName) {
                          $ss->whereNull('admin_id')->where('owner_name', $adminName);
                      });
                });
            })
            ->when($q, function($query) use ($q) {
                $query->where(function($sub) use ($q) {
                    $sub->where('name', 'like', "%$q%")
                        ->orWhere('type', 'like', "%$q%")
                        ->orWhere('description', 'like', "%$q%")
                        ->orWhereHas('shop', function($s) use ($q) { $s->where('name', 'like', "%$q%"); });
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();
        return view('admin.services.index', compact('services'));
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
        return view('admin.services.create', compact('shops'));
    }

    public function show(Service $service)
    {
        if (auth('admin')->user()?->isOwner()) {
            $ownerOk = ($service->shop && ($service->shop->admin_id === auth('admin')->id() || (!$service->shop->admin_id && $service->shop->owner_name === auth('admin')->user()->name)));
            if (!$ownerOk) { abort(403); }
        }
        $service->load('shop');
        return view('admin.services.show', compact('service'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'duration_unit' => 'nullable|in:minutes,hours,days',
            'type' => 'required|in:repair,maintenance,inspection,other',
            'shop_id' => 'required|exists:shops,id',
            'is_active' => 'sometimes|boolean'
        ]);

        $validated['is_active'] = $request->boolean('is_active', false);
        if ($request->input('duration_unit') === 'hours') {
            $validated['duration'] = $validated['duration'] * 60;
        } elseif ($request->input('duration_unit') === 'days') {
            $validated['duration'] = $validated['duration'] * 1440; // minutes in a day
        }

        unset($validated['duration_unit']);
        Service::create($validated);

        return redirect()->route('admin.services.index')
            ->with('success', 'Service created successfully.');
    }

    public function edit(Service $service)
    {
        if (auth('admin')->user()?->isOwner()) {
            $ownerOk = ($service->shop && ($service->shop->admin_id === auth('admin')->id() || (!$service->shop->admin_id && $service->shop->owner_name === auth('admin')->user()->name)));
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
        return view('admin.services.edit', compact('service', 'shops'));
    }

    public function update(Request $request, Service $service)
    {
        if (auth('admin')->user()?->isOwner()) {
            $ownerOk = ($service->shop && ($service->shop->admin_id === auth('admin')->id() || (!$service->shop->admin_id && $service->shop->owner_name === auth('admin')->user()->name)));
            if (!$ownerOk) { abort(403); }
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'duration_unit' => 'nullable|in:minutes,hours,days',
            'type' => 'required|in:repair,maintenance,inspection,other',
            'shop_id' => 'required|exists:shops,id',
            'is_active' => 'sometimes|boolean'
        ]);

        $validated['is_active'] = $request->boolean('is_active', false);
        if ($request->input('duration_unit') === 'hours') {
            $validated['duration'] = $validated['duration'] * 60;
        } elseif ($request->input('duration_unit') === 'days') {
            $validated['duration'] = $validated['duration'] * 1440; // minutes in a day
        }

        unset($validated['duration_unit']);
        $service->update($validated);

        return redirect()->route('admin.services.index')
            ->with('success', 'Service updated successfully.');
    }

    public function destroy(Service $service)
    {
        if (auth('admin')->user()?->isOwner()) {
            $ownerOk = ($service->shop && ($service->shop->admin_id === auth('admin')->id() || (!$service->shop->admin_id && $service->shop->owner_name === auth('admin')->user()->name)));
            if (!$ownerOk) { abort(403); }
        }
        $service->delete();

        return redirect()->route('admin.services.index')
            ->with('success', 'Service deleted successfully.');
    }
} 