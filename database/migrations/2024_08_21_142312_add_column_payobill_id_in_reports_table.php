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
        Schema::table('reports', function (Blueprint $table) {
            $table->string('payobill_id')->nullable()->after('txnid');
            $table->string('op_ref_no')->nullable()->after('txnid');
            $table->unsignedBigInteger('circle_id')->default(0)->after('provider_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('payobill_id','op_ref_no','circle_id');
        });
    }
};
