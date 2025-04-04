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
        Schema::table('user_static_qr_accounts', function (Blueprint $table) {
            $table->string('upi_intent')->after("virtual_account_id")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_static_qr_accounts', function (Blueprint $table) {
            //
        });
    }
};
