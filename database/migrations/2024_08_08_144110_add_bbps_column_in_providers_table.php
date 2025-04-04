<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->unsignedBigInteger('operator_id')->default(0);
            $table->unsignedBigInteger('bank_id')->default(0);
            $table->longText('input')->nullable();
            $table->unsignedTinyInteger('bbps_enabled')->default(0);
            $table->unsignedTinyInteger('view_bill')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->dropColumn(['bbps_enabled','view_bill','input','bank_id','operator_id']);
        });
    }
};
