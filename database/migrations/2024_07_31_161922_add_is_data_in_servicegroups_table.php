<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('servicegroups', function (Blueprint $table) {
            //
        });
        DB::statement("INSERT INTO `servicegroups` (`id`, `group_name`, `created_at`, `updated_at`, `status_id`) VALUES (NULL, 'Recharge', '2024-07-31 11:57:32', '2024-07-31 11:57:32', '1')");
        DB::statement("INSERT INTO `servicegroups` (`id`, `group_name`, `created_at`, `updated_at`, `status_id`) VALUES (NULL, 'Recharge 2', '2024-07-31 11:57:32', '2024-07-31 11:57:32', '1')");
        DB::statement("INSERT INTO `services` (`id`, `service_name`, `service_image`, `slug`, `sub_slug`, `report_slug`, `wallet_id`, `created_at`, `updated_at`, `bbps`, `servicegroup_id`, `report_is_static`, `status_id`) VALUES (NULL, 'Recharge', 'storage/provider-icon/dashboard.trustxpay.org-Airtel CMS-1720526840.png', 'recharge/v1/welcome', NULL, 'recharge-history', '1', NULL, '2024-07-09 12:07:20', '0', '11', '0', '1')");
        DB::statement("INSERT INTO `services` (`id`, `service_name`, `service_image`, `slug`, `sub_slug`, `report_slug`, `wallet_id`, `created_at`, `updated_at`, `bbps`, `servicegroup_id`, `report_is_static`, `status_id`) VALUES (NULL, 'Recharge 2', 'storage/provider-icon/dashboard.trustxpay.org-Airtel CMS-1720526840.png', 'recharge-2/v1/welcome', NULL, 'recharge-2-history', '1', NULL, '2024-07-09 12:07:20', '0', '12', '0', '1')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servicegroups', function (Blueprint $table) {
            //
        });
    }
};
