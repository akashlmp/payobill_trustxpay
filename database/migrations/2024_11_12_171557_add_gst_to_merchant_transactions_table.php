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
            $table->double('gst')->default(0)->after('decrementAmount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('merchant_transactions', function (Blueprint $table) {
            $table->dropColumn(['gst']);
        });
    }
};
