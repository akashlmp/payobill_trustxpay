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
            $table->tinyInteger('provider_api_from')->default(1)->comment('1=Paysprint,2=Bankit,3=iServeU')->after('user_id');
            $table->string('bene_phone_number')->nullable()->after('bene_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aeps_payout_requests', function (Blueprint $table) {
            //
        });
    }
};
