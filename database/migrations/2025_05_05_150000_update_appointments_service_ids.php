<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update existing appointments to set service_id based on service_type
        $appointments = DB::table('appointments')->whereNull('service_id')->get();
        foreach ($appointments as $appointment) {
            $service = DB::table('services')
                ->where('name', $appointment->service_type)
                ->first();
            
            if ($service) {
                DB::table('appointments')
                    ->where('id', $appointment->id)
                    ->update(['service_id' => $service->id]);
            }
        }
    }

    public function down(): void
    {
        // No need to revert this data migration
    }
}; 