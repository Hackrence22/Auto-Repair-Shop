<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // First, modify the column to allow null temporarily
            $table->string('status')->nullable()->change();
            
            // Then update existing records
            DB::statement("UPDATE appointments SET status = 'pending' WHERE status IS NULL");
            
            // Finally, modify the column to be an enum with all statuses
            $table->enum('status', ['pending', 'approved', 'confirmed', 'completed', 'cancelled'])
                  ->default('pending')
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])
                  ->default('pending')
                  ->change();
        });
    }
}; 