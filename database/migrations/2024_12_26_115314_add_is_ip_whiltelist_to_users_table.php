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
        Schema::table('users', function (Blueprint $table) {
            $table->tinyInteger('is_ip_whiltelist')->default(0);
            $table->string('server_ip')->nullable();
            $table->string('merchant_ip')->nullable();
            $table->string('callback_url',512)->nullable();
            $table->char('api_key',36)->nullable();
            $table->string('secrete_key')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
