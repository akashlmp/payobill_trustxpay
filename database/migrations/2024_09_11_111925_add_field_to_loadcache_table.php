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
        Schema::table('loadcashes', function (Blueprint $table) {
            $table->string('txn_number')->after('bankref')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loadcashes', function (Blueprint $table) {
            $table->dropColumn(['txn_number']);
        });
    }
};
