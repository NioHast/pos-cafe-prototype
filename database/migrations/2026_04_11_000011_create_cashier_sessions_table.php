<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashier_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('shift_start');
            $table->timestamp('shift_end')->nullable();
            $table->decimal('total_sales', 12, 2)->default(0);
            $table->unsignedInteger('total_transactions')->default(0);
            $table->timestamps();

            $table->index('user_id');
            $table->index('shift_start');
            $table->index('shift_end');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashier_sessions');
    }
};