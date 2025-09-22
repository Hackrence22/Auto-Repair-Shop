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
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->string('image')->nullable()->after('name');
            $table->string('account_name')->nullable()->after('image');
            $table->string('account_number')->nullable()->after('account_name');
            $table->enum('role_type', ['gcash', 'paymaya', 'cash', 'bank_transfer', 'other'])->default('other')->after('account_number');
            $table->text('description')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn(['image', 'account_name', 'account_number', 'role_type']);
            $table->string('description')->nullable()->change();
        });
    }
};
