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
        Schema::create('merchant_api_commissions', function (Blueprint $table) {
            $table->id();
            $table->integer("merchant_id")->nullable();
            $table->integer("provider_id")->nullable();
            $table->integer("service_id")->nullable();
            $table->integer("provider_commission_type")->nullable()->comment("1=Paysprint,2=Bankit,3=iServeU");
            $table->integer("commission_type")->nullable()->comment("1=commission,2=charges");
            $table->double("min_amount")->nullable();
            $table->double("max_amount")->nullable();
            $table->double("commission")->nullable();
            $table->tinyInteger("status")->default(1);
            $table->tinyInteger("type")->nullable()->comment("0=%1=rs");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchant_api_commissions');
    }
};
