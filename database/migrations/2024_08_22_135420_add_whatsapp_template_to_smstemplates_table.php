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
        Schema::table('smstemplates', function (Blueprint $table) {
            $table->string('sms_sender_id')->nullable()->after('template_id');
            $table->string('whatsapp_template_name')->nullable()->after('sms_sender_id');
            $table->text('whatsapp_template_msg')->nullable()->after('whatsapp_template_name');
            $table->string('whatsapp_template_id')->nullable()->after('whatsapp_template_msg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('smstemplates', function (Blueprint $table) {
            $table->dropColumn(['sms_sender_id','whatsapp_template_name','whatsapp_template_msg','whatsapp_template_id']);
        });
    }
};
