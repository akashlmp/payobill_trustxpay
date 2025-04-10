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
        Schema::create('bankitdmtbanks', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name');
            $table->string('ifsc');
            $table->string('ifscStatus');
            $table->string('accVerAvailabe');
            $table->string('channelsSupported');
            $table->string('bankCode');
            $table->bigInteger('bank_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bankitdmtbanks');
    }
};
