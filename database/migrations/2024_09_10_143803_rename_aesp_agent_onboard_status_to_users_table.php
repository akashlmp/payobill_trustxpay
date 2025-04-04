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
            $table->tinyInteger('aeps_agent_id')->default(0)->change();
            $table->renameColumn('aeps_agent_id', 'aeps_onboard_status')->comment("0=Pending,1=Completed");
            $table->renameColumn('agent_onboard_status', 'cms_onboard_status')->comment("0=Pending,1=Completed");

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
