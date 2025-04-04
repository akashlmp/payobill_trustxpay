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
        Schema::create('merchant_apicommreports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->integer('provider_id');
            $table->integer('api_id');
            $table->double('amount')->default(0);
            $table->double('retailerComm')->default(0);
            $table->double('retailerCharge')->default(0);
            $table->double('apiCommission')->default(0);
            $table->double('apiCharge')->default(0);
            $table->double('totalProfit')->default(0);
            $table->unsignedBigInteger('report_id');
            $table->double('d')->default(0);
            $table->double('sd')->default(0);
            $table->double('st')->default(0);
            $table->double('rf')->default(0);
            $table->smallInteger('status_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchant_apicommreports');
    }
};
