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
            $table->string('latitude', 25)->nullable()->after('merchant_ip');
            $table->string('longitude', 25)->nullable()->after('latitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('merchant', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
