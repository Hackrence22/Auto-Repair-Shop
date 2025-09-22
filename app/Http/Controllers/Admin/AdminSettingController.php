<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminSettingController extends Controller
{
    public function index()
    {
        $settings = [
            'shop_name' => setting('shop_name', 'Auto Repair Shop'),
            'shop_address' => setting('shop_address', ''),
            'shop_phone' => setting('shop_phone', ''),
            'shop_email' => setting('shop_email', ''),
            'business_hours' => setting('business_hours', 'Mon-Fri: 9:00 AM - 6:00 PM'),
            'appointment_interval' => setting('appointment_interval', 30),
            'max_appointments_per_day' => setting('max_appointments_per_day', 20),
            'maintenance_mode' => setting('maintenance_mode', false),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'shop_name' => 'required|string|max:255',
            'shop_address' => 'required|string',
            'shop_phone' => 'required|string|max:20',
            'shop_email' => 'required|email|max:255',
            'business_hours' => 'required|string',
            'appointment_interval' => 'required|integer|min:15|max:120',
            'max_appointments_per_day' => 'required|integer|min:1|max:100',
            'maintenance_mode' => 'boolean'
        ]);

        foreach ($validated as $key => $value) {
            setting([$key => $value]);
        }

        // Clear the settings cache
        Cache::forget('settings');

        return redirect()->route('admin.settings')
            ->with('success', 'Settings updated successfully.');
    }
} 