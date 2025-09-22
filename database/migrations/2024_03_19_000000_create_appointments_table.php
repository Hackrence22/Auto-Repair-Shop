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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('customer_name');
            $table->string('email');
            $table->string('phone');
            $table->string('vehicle_type');
            $table->string('vehicle_model');
            $table->string('vehicle_year');
            $table->unsignedBigInteger('service_id')->nullable();
            $table->foreign('service_id')->references('id')->on('services')->onDelete('set null');
            $table->string('service_type');
            $table->date('appointment_date');
            $table->time('appointment_time');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'approved', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->string('technician')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
}; 