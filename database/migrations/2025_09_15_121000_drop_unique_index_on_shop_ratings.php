<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('shop_ratings')) {
            return;
        }
        $indexExists = DB::table('information_schema.statistics')
            ->whereRaw('table_schema = DATABASE()')
            ->where('table_name', 'shop_ratings')
            ->where('index_name', 'shop_ratings_shop_id_user_id_unique')
            ->exists();

        if ($indexExists) {
            Schema::table('shop_ratings', function (Blueprint $table) {
                $table->dropUnique('shop_ratings_shop_id_user_id_unique');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('shop_ratings')) {
            return;
        }
        // Recreate unique only if absent
        $indexExists = DB::table('information_schema.statistics')
            ->whereRaw('table_schema = DATABASE()')
            ->where('table_name', 'shop_ratings')
            ->where('index_name', 'shop_ratings_shop_id_user_id_unique')
            ->exists();
        if (!$indexExists) {
            Schema::table('shop_ratings', function (Blueprint $table) {
                $table->unique(['shop_id', 'user_id']);
            });
        }
    }
};


