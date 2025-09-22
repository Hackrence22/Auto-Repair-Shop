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
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('owner_name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->text('address');
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('country')->default('Philippines');
            $table->decimal('latitude', 10, 8)->nullable(); // For map coordinates
            $table->decimal('longitude', 11, 8)->nullable(); // For map coordinates
            $table->string('map_embed_url')->nullable(); // For embedded Google Maps
            $table->text('description')->nullable();
            $table->string('image')->nullable(); // Shop photo
            $table->time('opening_time')->default('08:00:00');
            $table->time('closing_time')->default('17:00:00');
            $table->json('working_days')->nullable(); // Array of working days (1-7, Monday-Sunday)
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
