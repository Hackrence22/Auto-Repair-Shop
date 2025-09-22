<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'weekday_hours',
                'value' => 'Monday - Friday: 8:00 AM - 6:00 PM',
                'type' => 'string',
                'group' => 'business_hours'
            ],
            [
                'key' => 'weekend_hours',
                'value' => 'Saturday: 9:00 AM - 4:00 PM',
                'type' => 'string',
                'group' => 'business_hours'
            ],
            [
                'key' => 'sunday_hours',
                'value' => 'Sunday - Closed',
                'type' => 'string',
                'group' => 'business_hours'
            ]
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
} 