<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['customer', 'cashier', 'admin'])->default('customer')->after('email');
            $table->string('nim', 20)->nullable()->after('role');
            $table->string('phone', 20)->nullable()->after('nim');
            $table->boolean('is_student_verified')->default(false)->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'nim', 'phone', 'is_student_verified']);
        });
    }
};
