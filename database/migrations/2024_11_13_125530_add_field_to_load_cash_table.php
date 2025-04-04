<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('loadcashes', function (Blueprint $table) {
            $table->bigInteger('merchant_id')->default(0)->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('load_cash', function (Blueprint $table) {
            $table->dropColumn(['merchant_id']);
        });
    }
};
