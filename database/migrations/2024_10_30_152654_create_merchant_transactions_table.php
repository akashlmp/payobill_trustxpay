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
        Schema::create('merchant_transactions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('merchant_id')->nullable()->index();
            $table->integer('provider_id')->nullable()->index();
            $table->string('account_number')->nullable();
            $table->string('merchant_reference_id')->nullable();
            $table->string('reference_id')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('utr')->nullable();
            $table->double('opening_balance')->nullable();
            $table->double('amount')->nullable();
            $table->double('profit')->nullable();
            $table->double('total_balance')->nullable();
            $table->double('tds')->nullable();
            $table->double('decrementAmount')->nullable();
            $table->string('description')->nullable();
            $table->string('mode')->nullable();
            $table->integer('status_id')->nullable();
            $table->string('failure_reason')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchant_transactions');
    }
};
