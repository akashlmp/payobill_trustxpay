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
        Schema::table('companies', function (Blueprint $table) {
            $table->tinyInteger('dmt_provider')->default(1)->after('active_services')->comment('1 For Paysprint 2 for BankIt');
            $table->tinyInteger('aeps_provider')->default(1)->after('dmt_provider')->comment('1 For Paysprint 2 for BankIt');
            $table->tinyInteger('cms_provider')->default(1)->after('aeps_provider')->comment('1 For Paysprint 2 for BankIt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['dmt_provider', 'aeps_provider', 'cms_provider']);
        });
    }
};
