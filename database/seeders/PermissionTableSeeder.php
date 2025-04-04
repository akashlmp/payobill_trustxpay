<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /** Roles */
        $roles = [
            ['name' => 'Super Admin', 'guard_name' => 'web']
        ];

        /** Super Admins */
        $super_admins = [
            ['name' => 'Super Admin','mobile'=>'1234567890','email' => 'admin@gmail.com', 'password' => Hash::make('@dmin35!!24'), 'status_id' => '1']
        ];

        /** All Permission  */
        $permissions = [
            /** Members*/
            ['module' => 'Members', 'sub_module' => 'Retailer', 'name' => 'admin.member.view.settings', 'label' => 'View Api Settings', 'type' => 1, 'guard_name' => 'web'],
            ['module' => 'Members', 'sub_module' => 'Retailer', 'name' => 'admin.member.update.settings', 'label' => 'Update Api Settings', 'type' => 1, 'guard_name' => 'web'],

            /** Master*/
            ['module' => 'Master', 'sub_module' => 'Credentials Master', 'name' => 'admin.master.credentials.list', 'label' => 'Listing', 'type' => 1, 'guard_name' => 'web'],
            ['module' => 'Master', 'sub_module' => 'Credentials Master', 'name' => 'admin.master.credentials.create', 'label' => 'Create', 'type' => 1, 'guard_name' => 'web'],
            ['module' => 'Master', 'sub_module' => 'Credentials Master', 'name' => 'admin.master.credentials.edit', 'label' => 'Edit', 'type' => 1, 'guard_name' => 'web'],

            /** Payment */
            ['module' => 'Payment', 'sub_module' => 'Balance Transfer', 'name' => 'admin.balance_transfer', 'label' => 'Listing', 'type' => 1, 'guard_name' => 'web'],
            ['module' => 'Payment', 'sub_module' => 'Balance Transfer', 'name' => 'admin.view_transfer_users', 'label' => 'View', 'type' => 1, 'guard_name' => 'web'],

            ['module' => 'Payment', 'sub_module' => 'Balance Return', 'name' => 'admin.balance_return', 'label' => 'Listing', 'type' => 1, 'guard_name' => 'web'],
            ['module' => 'Payment', 'sub_module' => 'Balance Return', 'name' => 'admin.view_balance_return', 'label' => 'View', 'type' => 1, 'guard_name' => 'web'],

            ['module' => 'Payment', 'sub_module' => 'Payment Request View', 'name' => 'admin.payment_request_view', 'label' => 'Listing', 'type' => 1, 'guard_name' => 'web'],
            ['module' => 'Payment', 'sub_module' => 'Payment Request View', 'name' => 'admin.view_payment_request', 'label' => 'Update', 'type' => 1, 'guard_name' => 'web'],

            ['module' => 'Payment', 'sub_module' => 'Purchase Balance', 'name' => 'admin.purchase_balance', 'label' => 'Listing', 'type' => 1, 'guard_name' => 'web'],
            ['module' => 'Payment', 'sub_module' => 'Purchase Balance', 'name' => 'admin.purchase_balance.create', 'label' => 'Add Balance', 'type' => 1, 'guard_name' => 'web'],
            ['module' => 'Payment', 'sub_module' => 'Purchase Balance', 'name' => 'admin.purchase_balance.download', 'label' => 'Download', 'type' => 1, 'guard_name' => 'web'],

            /*Report*/
            ['module' => 'Report', 'sub_module' => 'Admin Profit Report', 'name' => 'admin.report.profit', 'label' => 'Listing', 'type' => 1, 'guard_name' => 'web'],
            ['module' => 'Report', 'sub_module' => 'Admin Profit Report', 'name' => 'admin.report.profit.download', 'label' => 'Download', 'type' => 1, 'guard_name' => 'web'],
            ['module' => 'Report', 'sub_module' => 'Ledger Report', 'name' => 'admin.report.ledger_report', 'label' => 'Listing', 'type' => 1, 'guard_name' => 'web'],
            ['module' => 'Report', 'sub_module' => 'Payment Debit Report', 'name' => 'admin.report.debit_report', 'label' => 'Listing', 'type' => 1, 'guard_name' => 'web'],
            ['module' => 'Report', 'sub_module' => 'Payment Debit Report', 'name' => 'admin.report.debit_report.download', 'label' => 'Download', 'type' => 1, 'guard_name' => 'web'],
            ['module' => 'Report', 'sub_module' => 'Payment Credit Report', 'name' => 'admin.report.credit_report', 'label' => 'Listing', 'type' => 1, 'guard_name' => 'web'],
            ['module' => 'Report', 'sub_module' => 'Payment Credit Report', 'name' => 'admin.report.credit_report.download', 'label' => 'Download', 'type' => 1, 'guard_name' => 'web'],

             /*Transactions*/
             ['module' => 'Transactions', 'sub_module' => 'All Transactions', 'name' => 'admin.transaction.all', 'label' => 'Listing', 'type' => 1, 'guard_name' => 'web'],
             ['module' => 'Transactions', 'sub_module' => 'All Transactions', 'name' => 'admin.transaction.update', 'label' => 'Update', 'type' => 1, 'guard_name' => 'web'],
             ['module' => 'Transactions', 'sub_module' => 'All Transactions', 'name' => 'admin.transaction.download', 'label' => 'Download', 'type' => 1, 'guard_name' => 'web'],

            //  ['module' => 'Transactions', 'sub_module' => 'Payin', 'name' => 'admin.transaction.payin-history', 'label' => 'Listing', 'type' => 1, 'guard_name' => 'web'],
            //  ['module' => 'Transactions', 'sub_module' => 'Payin', 'name' => 'admin.transaction.static-qr-history.download', 'label' => 'Download', 'type' => 1, 'guard_name' => 'web'],

             ['module' => 'Transactions', 'sub_module' => 'Pending Transaction', 'name' => 'admin.transaction.pending', 'label' => 'Listing', 'type' => 1, 'guard_name' => 'web'],
             ['module' => 'Transactions', 'sub_module' => 'Pending Transaction', 'name' => 'admin.transaction.pending.update', 'label' => 'Update', 'type' => 1, 'guard_name' => 'web'],
             ['module' => 'Transactions', 'sub_module' => 'Pending Transaction', 'name' => 'admin.transaction.pending.download', 'label' => 'Download', 'type' => 1, 'guard_name' => 'web'],

             ['module' => 'Transactions', 'sub_module' => 'Refund Manager', 'name' => 'admin.transaction.refund.manager', 'label' => 'Listing', 'type' => 1, 'guard_name' => 'web'],
             ['module' => 'Transactions', 'sub_module' => 'Refund Manager', 'name' => 'admin.transaction.refund.update', 'label' => 'Update', 'type' => 1, 'guard_name' => 'web'],

             ['module' => 'Transactions', 'sub_module' => 'Api Summary', 'name' => 'admin.transaction.api_summary', 'label' => 'Listing', 'type' => 1, 'guard_name' => 'web'],
             ['module' => 'Transactions', 'sub_module' => 'Operator Wise Sale', 'name' => 'admin.transaction.operator_wise_sale', 'label' => 'Listing', 'type' => 1, 'guard_name' => 'web'],

        ];


        foreach ($permissions as $permission) {
            $permissionData = Permission::where("name", $permission['name'])->where('guard_name', 'web')->first();
            if (empty($permissionData)) {
                Permission::insert($permission);
            } else {
                $permissionData->update($permission);
            }
        }

        foreach ($roles as $role) {
            Role::query()->updateOrCreate($role);
        }

        $role = Role::where('name', 'Super Admin')->first();
        $permissions = Permission::where("guard_name", "web")->pluck('id', 'id')->all();

        $admin = User::where('role_id', 1)->first();
        // if (!$admin) {
        //     $admin = User::query()->create($data);
        // }
        $role->syncPermissions( $permissions);
        $admin->assignRole([$role->id]);

        // foreach ($super_admins as $data) {
        //     $admin = User::where('role_id', 1)->first();
        //     // if (!$admin) {
        //     //     $admin = User::query()->create($data);
        //     // }
        //     $role->syncPermissions($permissions);
        //     $admin->assignRole([$role->id]);
        // }
    }
}
