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
        Schema::table('aeps_payout_requests', function (Blueprint $table) {
            $table->string('bene_name')->nullable()->after('bene_id');
            $table->string('account_no')->nullable()->after('bene_id');
            $table->string('ifsc')->nullable()->after('bene_id');
            $table->string('bank_name')->nullable()->after('bene_id');
            $table->decimal('charges')->default(0)->after('amount');
            $table->string('utr')->nullable()->after('transaction_id');
            $table->dateTime('transaction_date')->nullable()->after('response');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aeps_payout_requests', function (Blueprint $table) {
            $table->dropColumn(['bene_name','account_no','ifsc','bank_name','charges','utr','transaction_date']);
        });
    }
};
