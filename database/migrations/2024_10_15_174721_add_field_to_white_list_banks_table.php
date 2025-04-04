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
        Schema::table('white_list_banks', function (Blueprint $table) {
            $table->tinyInteger('status')->default(0)->comment('0 for Pending approval,1 for Approve,2 for reject');
            $table->string('bank_proof')->nullable();
            $table->bigInteger('approve_reject_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('white_list_banks', function (Blueprint $table) {
            $table->dropColumn(['status', 'bank_proof', 'approve_reject_by']);
        });
    }
};
