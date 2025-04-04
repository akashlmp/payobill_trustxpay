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
        Schema::create('paysprint_payout_banks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bank_id')->default(0);
            $table->string('bank_name')->nullable();
            $table->string('iinno')->nullable();
            $table->unsignedTinyInteger('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paysprint_payout_banks');
    }
};
