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
        // Check if table exists and has the right structure
        if (Schema::hasTable('customer_services')) {
            // Check if category column exists
            if (!Schema::hasColumn('customer_services', 'category')) {
                Schema::table('customer_services', function (Blueprint $table) {
                    $table->enum('category', ['booking', 'shop', 'payment', 'appointment', 'other'])->default('other')->after('message');
                });
            }
            
            // Check if priority column exists
            if (!Schema::hasColumn('customer_services', 'priority')) {
                Schema::table('customer_services', function (Blueprint $table) {
                    $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium')->after('category');
                });
            }
            
            // Check if status column exists
            if (!Schema::hasColumn('customer_services', 'status')) {
                Schema::table('customer_services', function (Blueprint $table) {
                    $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open')->after('priority');
                });
            }
            
            // Check if admin_reply column exists
            if (!Schema::hasColumn('customer_services', 'admin_reply')) {
                Schema::table('customer_services', function (Blueprint $table) {
                    $table->text('admin_reply')->nullable()->after('status');
                });
            }
            
            // Check if assigned_admin_id column exists
            if (!Schema::hasColumn('customer_services', 'assigned_admin_id')) {
                Schema::table('customer_services', function (Blueprint $table) {
                    $table->unsignedBigInteger('assigned_admin_id')->nullable()->after('admin_reply');
                });
            }
            
            // Check if resolved_at column exists
            if (!Schema::hasColumn('customer_services', 'resolved_at')) {
                Schema::table('customer_services', function (Blueprint $table) {
                    $table->timestamp('resolved_at')->nullable()->after('assigned_admin_id');
                });
            }
        } else {
            // Create the table if it doesn't exist
            Schema::create('customer_services', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('shop_id');
                $table->string('subject');
                $table->text('message');
                $table->enum('category', ['booking', 'shop', 'payment', 'appointment', 'other'])->default('other');
                $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
                $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
                $table->text('admin_reply')->nullable();
                $table->unsignedBigInteger('assigned_admin_id')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
                $table->foreign('assigned_admin_id')->references('id')->on('admins')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is for checking/fixing structure, so no down method needed
    }
};
