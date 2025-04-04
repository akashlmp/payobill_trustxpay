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
        Schema::table('loadcashes', function (Blueprint $table) {
            $table->tinyInteger('added_from')->default(0)->comment('1 For Axix-transaction,2 For CDM,3 For EasyPay')->after('txn_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loadcash', function (Blueprint $table) {
            //
        });
    }
};
