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
        Schema::table('merchant', function (Blueprint $table) {
            $table->string('otp')->after('mobile_number')->nullable();
            $table->dateTime('password_changed_at')->after('callback_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('merchant', function (Blueprint $table) {
            $table->dropColumn('otp');
            $table->dropColumn('password_changed_at');
        });
    }
};
