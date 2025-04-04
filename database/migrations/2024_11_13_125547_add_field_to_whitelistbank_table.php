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
        Schema::table('white_list_banks', function (Blueprint $table) {
            $table->bigInteger('merchant_id')->default(0)->after('user_id');
            $table->tinyInteger('type')->default(1)->after('merchant_id')->comment('1 for agent users 2 for merchant');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('white_list_banks', function (Blueprint $table) {
            $table->dropColumn(['type','merchant_id']);
        });
    }
};
