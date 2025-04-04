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
        Schema::table('merchant_transactions', function (Blueprint $table) {
            $table->integer('callback_status')->default(0)->after('ip_address');
            $table->tinyInteger('callback_retry')->default(0)->after('callback_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('merchant_transactions', function (Blueprint $table) {
            $table->dropColumn(['callback_status', 'callback_retry']);
        });
    }
};
