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
            $table->string('callback_url')->nullable()->after('longitude');
            $table->string('server_ip')->nullable()->after('is_ip_whiltelist');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('merchant', function (Blueprint $table) {
            $table->dropColumn(['callback_url', 'server_ip']);
        });
    }
};
