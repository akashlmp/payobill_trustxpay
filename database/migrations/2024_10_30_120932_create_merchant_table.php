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
        Schema::create('merchant', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('pan_number')->nullable();
            $table->string('gst_number')->nullable();
            $table->string('address')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('pincode')->nullable();
            $table->uuid('api_key')->nullable();
            $table->string('secrete_key')->nullable();
            $table->double('wallet')->nullable();
            $table->unsignedTinyInteger('status')->default(0)->comment('0 for in-active,1 for active');
            $table->unsignedTinyInteger('is_ip_whiltelist')->default(0)->comment('0 for Off,1 for on');
            $table->string('active_services')->nullable();
            $table->string('merchant_ip')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchant');
    }
};
