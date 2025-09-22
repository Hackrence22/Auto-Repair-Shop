<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('customer_services')) {
            if (!Schema::hasColumn('customer_services', 'category')) {
                Schema::table('customer_services', function (Blueprint $table) {
                    $table->enum('category', ['booking', 'shop', 'payment', 'appointment', 'other'])->default('other')->after('message');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('customer_services') && Schema::hasColumn('customer_services', 'category')) {
            Schema::table('customer_services', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }
    }
};
