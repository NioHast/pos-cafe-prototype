<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('menus', 'is_stock_calculated')) {
            Schema::table('menus', function (Blueprint $table) {
                $table->boolean('is_stock_calculated')->default(false)->after('is_available');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('menus', 'is_stock_calculated')) {
            Schema::table('menus', function (Blueprint $table) {
                $table->dropColumn('is_stock_calculated');
            });
        }
    }
};