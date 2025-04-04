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
        Schema::create('whatsapp_report_urls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('report_id')->default(0);
            $table->string('number')->nullable();
            $table->text('whatsapp_web_url')->nullable();
            $table->text('whatsapp_mobile_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_report_urls');
    }
};
