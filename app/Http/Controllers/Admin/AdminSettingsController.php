<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class AdminSettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'appearance' => Setting::getGroup('appearance'),
            'notifications' => Setting::getGroup('notifications'),
            'business_hours' => Setting::getGroup('business_hours'),
            'appointments' => Setting::getGroup('appointments')
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $group = $request->input('group');
        
        switch ($group) {
            case 'notifications':
                $this->updateNotificationSettings($request);
                break;
            case 'business_hours':
                $this->updateBusinessHoursSettings($request);
                break;
            case 'appointments':
                // No need to call updateAppointmentSettings as it's no longer used
                break;
        }

        // Clear all settings cache
        Cache::forget('settings.group.appearance');
        Cache::forget('settings.group.notifications');
        Cache::forget('settings.group.business_hours');
        Cache::forget('settings.group.appointments');
        // Force clear the entire cache to ensure settings update everywhere
        \Artisan::call('cache:clear');

        return redirect()->back()->with('success', 'Settings updated successfully');
    }

    private function updateNotificationSettings(Request $request)
    {
        $request->validate([
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean'
        ]);

        Setting::set('email_notifications', $request->boolean('email_notifications'), 'boolean', 'notifications');
        Setting::set('sms_notifications', $request->boolean('sms_notifications'), 'boolean', 'notifications');
    }

    private function updateBusinessHoursSettings(Request $request)
    {
        $request->validate([
            'weekday_hours' => 'required|string|max:255',
            'weekend_hours' => 'required|string|max:255',
            'sunday_hours' => 'required|string|max:255'
        ]);

        Setting::set('weekday_hours', $request->weekday_hours, 'string', 'business_hours');
        Setting::set('weekend_hours', $request->weekend_hours, 'string', 'business_hours');
        Setting::set('sunday_hours', $request->sunday_hours, 'string', 'business_hours');
    }
} 