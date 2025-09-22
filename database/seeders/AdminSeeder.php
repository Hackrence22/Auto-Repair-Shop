<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::updateOrCreate(
            ['email' => 'clarencelisondra45@gmail.com'],
            [
                'name' => 'Admin Clarence',
                'password' => Hash::make('Hacker143'),
            ]
        );
    }
} 