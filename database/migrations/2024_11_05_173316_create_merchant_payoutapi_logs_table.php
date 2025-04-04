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
        Schema::create('merchant_payoutapi_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('merchant_user_id')->default(0);
            $table->string('reference_id', 30)->nullable();
            $table->string('merchant_reference_id', 100)->nullable();
            $table->tinyInteger('type')->nullable()->comment('1=api,2=status,3=webhook,4=refund');
            $table->tinyInteger('mode')->default(0)->comment('0=test,1=live');
            $table->text('url')->nullable();
            $table->json('header')->nullable();
            $table->json('body')->nullable();
            $table->json('response')->nullable();
            $table->string('ip')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchant_payoutapi_logs');
    }
};
