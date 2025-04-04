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
        Schema::create('balance_enquiries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->integer('provider_id')->nullable()->index();
            $table->tinyInteger('provider_api_from')->default(0)->comment('0 For none, 1 For Paysprint 2 for BankIt'); 
            $table->string('number')->nullable();
            $table->string('client_id',40)->nullable();
            $table->integer('api_id');
            $table->string('ip_address')->nullable();
            $table->text('txnid')->nullable();
            $table->double('opening_balance')->nullable();
            $table->double('amount')->nullable();
            $table->double('profit')->nullable();
            $table->double('total_balance')->nullable();
            $table->string('description')->nullable();
            $table->string('mode')->nullable();
            $table->integer('status_id')->nullable();
            $table->integer('wallet_type');
            $table->integer('state_id')->nullable();
            $table->string('failure_reason')->nullable();
            $table->text('row_data')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->timestamps();
            //$table->string('created_at', 22)->nullable()->collation('utf8mb4_0900_ai_ci');           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balance_enquiries');
    }
};
