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
        Schema::create('merchant_payouts', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('merchant_transaction_id')->nullable()->index();
            $table->bigInteger('merchant_id')->nullable()->index();
            $table->string('merchant_reference_id')->nullable();
            $table->string('reference_id')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('utr')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('ifsc')->nullable();
            $table->string('bene_name')->nullable();
            $table->string('bene_phone_number')->nullable();
            $table->string('mode')->nullable();
            $table->double('amount')->nullable();
            $table->tinyInteger('status')->nullable()->comment("0=Pending,1=Success,2=Failed,3=Refunded");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchant_payouts');
    }
};
