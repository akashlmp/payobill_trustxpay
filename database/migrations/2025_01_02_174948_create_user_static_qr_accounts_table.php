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
        Schema::create('user_static_qr_accounts', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->string('merchant_reference_id')->nullable();
            $table->string('unique_request_number')->nullable();
            $table->string('virtual_account_id')->nullable();
            $table->string("account_number")->nullable();
            $table->string("label")->nullable();
            $table->string("virtual_account_number")->nullable();
            $table->string("virtual_ifsc_number")->nullable();
            $table->string("virtual_upi_handle")->nullable();
            $table->string("description")->nullable();
            $table->tinyInteger("is_active")->default(1);
            $table->dateTime("auto_deactivate_at")->nullable();
            $table->string("upi_qrcode_remote_file_location")->nullable();
            $table->string("upi_qrcode_scanner_remote_file_location")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_static_qr_accounts');
    }
};
