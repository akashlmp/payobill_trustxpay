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
        Schema::table('merchant_test_transactions', function (Blueprint $table) {
            $table->string('ben_name')->nullable()->after('transaction_id');
            $table->string('ben_ifsc')->nullable()->after('ben_name');
            $table->string('ben_phone_number')->nullable()->after('ben_ifsc');
            $table->string('ben_bank_name')->nullable()->after('ben_phone_number');
        });

        if (Schema::hasColumn('merchant_test_transactions', 'provider_id')) {
            Schema::table('merchant_test_transactions', function (Blueprint $table) {
                $table->dropColumn('provider_id');
            });
        }

        if (Schema::hasColumn('merchant_test_transactions', 'opening_balance')) {
            Schema::table('merchant_test_transactions', function (Blueprint $table) {
                $table->dropColumn('opening_balance');
            });
        }

        if (Schema::hasColumn('merchant_test_transactions', 'profit')) {
            Schema::table('merchant_test_transactions', function (Blueprint $table) {
                $table->dropColumn('profit');
            });
        }

        if (Schema::hasColumn('merchant_test_transactions', 'total_balance')) {
            Schema::table('merchant_test_transactions', function (Blueprint $table) {
                $table->dropColumn('total_balance');
            });
        }

        if (Schema::hasColumn('merchant_test_transactions', 'tds')) {
            Schema::table('merchant_test_transactions', function (Blueprint $table) {
                $table->dropColumn('tds');
            });
        }

        if (Schema::hasColumn('merchant_test_transactions', 'decrementAmount')) {
            Schema::table('merchant_test_transactions', function (Blueprint $table) {
                $table->dropColumn('decrementAmount');
            });
        }

        if (Schema::hasColumn('merchant_test_transactions', 'description')) {
            Schema::table('merchant_test_transactions', function (Blueprint $table) {
                $table->dropColumn('description');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('merchant_test_transactions', function (Blueprint $table) {
            $table->dropColumn(['ben_name', 'ben_ifsc', 'ben_phone_number', 'ben_bank_name']);
        });
    }
};
