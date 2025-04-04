<?php

use App\Http\Controllers\Admin\RoleController;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


//Route::get('/store-bbps-provider',[\App\Http\Controllers\ScriptController::class,'bbps_provider_store']);
Route::get('/check-iserveU-transaction-status', [\App\Http\Controllers\ScriptController::class, 'checkTransactionStatus']);
// Route::get('/store-zipcodes',[\App\Http\Controllers\ScriptController::class,'storeZipcodes']);
Route::post('/getZipcodeDetails', [\App\Http\Controllers\ScriptController::class, 'getZipcodeDetails']);
// Route::get('/store-cms-provider',[\App\Http\Controllers\ScriptController::class,'storeCMSProvider']);
// Route::get('/store-cms-commission',[\App\Http\Controllers\ScriptController::class,'storeCMSCommission']);
// Route::get('/store-cms-payobill-commission',[\App\Http\Controllers\ScriptController::class,'storePayobillCMSCommission']);
// Route::get('/update-state-data',[\App\Http\Controllers\ScriptController::class,'updateStateData']);

Route::get('/check_dmt_status', [\App\Http\Controllers\ScriptController::class, 'check_dmt_status']);

//Route::get('/', [App\Http\Controllers\FrontController::class, 'welcome'])->name('home');
Route::post('/transaction-receipt-whatsapp-msg', [App\Http\Controllers\Agent\InvoiceController::class, 'downloadReceiptSendWhatsapp'])->name('downloadReceiptSendWhatsapp');

Route::get('/web-receipt/{id}', [App\Http\Controllers\Agent\InvoiceController::class, 'downloadTransactionReceipt'])->name('downloadTransactionReceipt');

Route::post('/mobile-receipt-whatsapp-msg', [App\Http\Controllers\Agent\InvoiceController::class, 'mobileReceiptSendWhatsapp'])->name('mobileReceiptSendWhatsapp');

Route::get('/mobile-receipt/{id}', [App\Http\Controllers\Agent\InvoiceController::class, 'downloadMobileReceipt']);
Route::post('/money-receipt-whatsapp-msg', [App\Http\Controllers\Agent\InvoiceController::class, 'moneyReceiptWhatsappMsg'])->name('moneyReceiptSendWhatsapp');

Route::get('/moneyReceipt/{id}', [App\Http\Controllers\Agent\InvoiceController::class, 'downloadMoneyReceipt']);


Route::get('/', [App\Http\Controllers\Auth\LoginController::class, 'login'])->name('login');
Route::get('/pages/{company_id}/{slug}', [App\Http\Controllers\FrontController::class, 'dynamic_page'])->name('dynamic_page');
Route::get('/contact-us', [App\Http\Controllers\FrontController::class, 'contact_us'])->name('contact_us');
Auth::routes();
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index']);
Route::post('/save-contact-enquiry', [App\Http\Controllers\FrontController::class, 'save_contact_enquiry'])->name('save_contact_enquiry');
// register here
Route::get('/sign-up/{slug?}', [App\Http\Controllers\Auth\SignupController::class, 'sign_up'])->name('sign_up');
Route::post('/sign-up', [App\Http\Controllers\Auth\SignupController::class, 'register_now'])->name('register_now');
// login here
Route::post('/login-now', [App\Http\Controllers\Auth\LoginController::class, 'login_now'])->name('login_now');
Route::post('/resend-login-otp', [App\Http\Controllers\Auth\LoginController::class, 'resend_login_otp'])->name('resend_login_otp');
Route::post('/login-with-otp', [App\Http\Controllers\Auth\LoginController::class, 'login_with_otp'])->name('login_with_otp');
// forgor password here
Route::get('/forgot-password', [App\Http\Controllers\Auth\LoginController::class, 'forgot_password'])->name('forgot_password');
Route::post('/forgot-password-otp', [App\Http\Controllers\Auth\LoginController::class, 'forgot_password_otp'])->name('forgot_password_otp');
Route::post('/confirm-forgot-password', [App\Http\Controllers\Auth\LoginController::class, 'confirm_forgot_password'])->name('confirm_forgot_password');
Route::get('/term-conditions', [App\Http\Controllers\FrontController::class, 'termConditions'])->name('term-conditions');

// documentation routes
Route::group(['prefix' => 'documentation'], function () {
    Route::get('list', [App\Http\Controllers\Merchant\DocumentationController::class, 'list'])->name('documentation.list');
    Route::get('payment-api', [App\Http\Controllers\Merchant\DocumentationController::class, 'paymentApi'])->name('documentation.paymentApi');
    Route::get('status-api', [App\Http\Controllers\Merchant\DocumentationController::class, 'statusApi'])->name('documentation.statusApi');
    Route::get('webhooks', [App\Http\Controllers\Merchant\DocumentationController::class, 'webhooks'])->name('documentation.webhooks');

    //Static QR
    Route::get('static-qr/payin/get', [App\Http\Controllers\Merchant\DocumentationController::class, 'staticQRGetApi'])->name('documentation.staticQRApi');
    Route::get('static-qr/payin/create', [App\Http\Controllers\Merchant\DocumentationController::class, 'staticQRCreateApi'])->name('documentation.staticQRCreateApi');
    Route::get('static-qr/payin/webhooks', [App\Http\Controllers\Merchant\DocumentationController::class, 'staticQRWebhooks'])->name('documentation.staticQRWebhooks');

    // dynamic qr
    Route::get('dynamic-qr/payin/pay-api', [App\Http\Controllers\Merchant\DocumentationController::class, 'dynamicQRPayinApi'])->name('documentation.dynamicQRPayinApi');
    Route::get('dynamic-qr/payin/status-api', [App\Http\Controllers\Merchant\DocumentationController::class, 'dynamicQRStatusApi'])->name('documentation.dynamicQRStatusApi');
    Route::get('dynamic-qr/payin/webhooks', [App\Http\Controllers\Merchant\DocumentationController::class, 'dynamicQRWebhooks'])->name('documentation.dynamicQRWebhooks');
});

Route::middleware(['auth'])->group(function () {
    Route::get('payerrorlogs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);
    Route::middleware(['password_expired'])->group(function () {
        Route::post('/verify-password', [App\Http\Controllers\HomeController::class, 'verifyPassword']);
        // for admin dashboard
        Route::prefix('admin')->group(function () {


            Route::middleware('permissions')->group(static function () {
                // Credentials routes
                Route::get('/credentials', [App\Http\Controllers\Admin\MasterController::class, 'list_credentials'])->name('admin.master.credentials.list');
                Route::get('/create-credentials', [App\Http\Controllers\Admin\MasterController::class, 'create_credentials'])->name('admin.master.credentials.create');
                Route::get('/edit-credentials/{id}', [App\Http\Controllers\Admin\MasterController::class, 'edit_credentials'])->name('admin.master.credentials.edit');

                Route::get('/balance-transfer', [App\Http\Controllers\Admin\TransferController::class, 'balance_transfer'])->name("admin.balance_transfer");
                Route::post('/view-transfer-users', [App\Http\Controllers\Admin\TransferController::class, 'view_transfer_users'])->name("admin.view_transfer_users");

                Route::get('/balance-return', [App\Http\Controllers\Admin\TransferController::class, 'balance_return'])->name("admin.balance_return");

                Route::get('/payment-request-view', [App\Http\Controllers\Admin\PaymentrequestController::class, 'payment_request_view'])->name("admin.payment_request_view");
                Route::post('/view-payment-request', [App\Http\Controllers\Admin\PaymentrequestController::class, 'view_payment_request'])->name("admin.view_payment_request");

                Route::get('/purchase-balance', [App\Http\Controllers\Admin\TransferController::class, 'purchase_balance'])->name("admin.purchase_balance");

                Route::group(['prefix' => 'report/v1', 'middleware' => 'auth'], function () {
                    Route::get('/all-transaction-report', [App\Http\Controllers\Admin\ReportController::class, 'all_transaction_report'])->name("admin.transaction.all");
                    Route::get('/pending-transaction', [App\Http\Controllers\Admin\ReportController::class, 'pending_transaction'])->name("admin.transaction.pending");
                    Route::get('/refund-manager', [App\Http\Controllers\Admin\ReportController::class, 'refund_manager'])->name("admin.transaction.refund.manager");
                    Route::get('/debit-report', [App\Http\Controllers\Admin\ReportController::class, 'debit_report'])->name("admin.report.debit_report");
                    Route::get('/credit-report', [App\Http\Controllers\Admin\ReportController::class, 'credit_report'])->name("admin.report.credit_report");
                    Route::get('/api-profit-loss-report', [App\Http\Controllers\Admin\ReportController::class, 'apiProfitLossReport'])->name("admin.report.profit");
                });

                Route::group(['prefix' => 'income'], function () {
                    Route::get('/operator-wise-sale', [App\Http\Controllers\Admin\IncomeController::class, 'operator_wise_sale'])->name("admin.transaction.operator_wise_sale");
                    Route::get('/api-summary-report', [App\Http\Controllers\Admin\IncomeController::class, 'api_summary_report'])->name("admin.transaction.api_summary");
                });

            });
            //member login
            Route::post('/member/login', [App\Http\Controllers\Admin\MemberController::class, 'memberLoginProcess'])->name('member.login');

            // API RESPONSES
            Route::get('/api-responses', [App\Http\Controllers\Admin\ApiResponseController::class, 'index']);
            Route::get('/api-responses-data', [App\Http\Controllers\Admin\ApiResponseController::class, 'api_response_ajax']);


            Route::get('/get-mnp-balance', [App\Http\Controllers\Agent\MnpController::class, 'getMnpBalance']);
            Route::post('/check-balance', [App\Http\Controllers\Admin\ApimasterController::class, 'giftCardBalance']);
            Route::get('/get-just-recharge-balance', [App\Http\Controllers\Admin\ApimasterController::class, 'justRechargeBalance']);
            Route::get('/get-recharge2-balance', [App\Http\Controllers\Agent\Recharge2Controller::class, 'recharge2Balance']);
            Route::get('/get-paysprint-credit-balance', [App\Http\Controllers\Admin\ApimasterController::class, 'paySpringCreditBalance']);
            Route::get('/get-paysprint-debit-balance', [App\Http\Controllers\Admin\ApimasterController::class, 'paySpringDebitBalance']);


            Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'dashboard']);
            Route::get('/dashboard-data-api', [App\Http\Controllers\Admin\DashboardController::class, 'dashboard_data_api']);
            Route::get('/dashboard-chart-api', [App\Http\Controllers\Admin\DashboardController::class, 'dashboard_chart_api']);
            Route::get('/dashboard-details-api', [App\Http\Controllers\Admin\DashboardController::class, 'dashboard_details_api']);
            Route::get('/activity-logs', [App\Http\Controllers\Admin\DashboardController::class, 'activity_logs']);
            Route::get('/top-seller', [App\Http\Controllers\Admin\DashboardController::class, 'top_seller']);
            Route::get('/get-service-wise-sales', [App\Http\Controllers\Admin\DashboardController::class, 'getServiceWiseSales']);
            // my profile here
            Route::get('/my-profile', [App\Http\Controllers\Admin\ProfileController::class, 'my_profile']);
            Route::post('/change-password', [App\Http\Controllers\Admin\ProfileController::class, 'change_password']);
            Route::post('/update-profile', [App\Http\Controllers\Admin\ProfileController::class, 'update_profile']);
            Route::post('/update-profile-photo', [App\Http\Controllers\Admin\ProfileController::class, 'update_profile_photo']);
            Route::post('/update-shop-photo', [App\Http\Controllers\Admin\ProfileController::class, 'update_shop_photo']);
            Route::post('/update-gst-regisration-photo', [App\Http\Controllers\Admin\ProfileController::class, 'update_gst_regisration_photo']);
            Route::post('/update-pancard-photo', [App\Http\Controllers\Admin\ProfileController::class, 'update_pancard_photo']);
            Route::post('/cancel-cheque-photo', [App\Http\Controllers\Admin\ProfileController::class, 'cancel_cheque_photo']);
            Route::post('/address-proof-photo', [App\Http\Controllers\Admin\ProfileController::class, 'address_proof_photo']);
            Route::post('/aadhar-front-photo', [App\Http\Controllers\Admin\ProfileController::class, 'aadhar_front_photo']);
            Route::post('/aadhar-back-photo', [App\Http\Controllers\Admin\ProfileController::class, 'aadhar_back_photo']);
            Route::post('/agreement-form-doc', [App\Http\Controllers\Admin\ProfileController::class, 'agreement_form_doc']);
            // generate transaction pin
            Route::get('/transaction-pin', [App\Http\Controllers\Admin\ProfileController::class, 'transaction_pin']);
            Route::post('/send-transaction-pin-otp', [App\Http\Controllers\Admin\ProfileController::class, 'send_transaction_pin_otp']);
            Route::post('/create-transaction-pin', [App\Http\Controllers\Admin\ProfileController::class, 'create_transaction_pin']);
            // admin company settings
            Route::get('/company-settings', [App\Http\Controllers\Admin\CompanyController::class, 'company_settings']);
            Route::post('/update-company-seeting', [App\Http\Controllers\Admin\CompanyController::class, 'update_company_seeting']);
            Route::get('/logo-upload', [App\Http\Controllers\Admin\CompanyController::class, 'logo_upload']);
            Route::post('/store-logo', [App\Http\Controllers\Admin\CompanyController::class, 'store_logo']);
            Route::post('/view-company-active-services', [App\Http\Controllers\Admin\CompanyController::class, 'view_company_active_services']);
            Route::post('/view-company-default-services', [App\Http\Controllers\Admin\CompanyController::class, 'view_company_default_services']);
            Route::get('/service-banner', [App\Http\Controllers\Admin\CompanyController::class, 'service_banner']);
            Route::post('/store-service-banner', [App\Http\Controllers\Admin\CompanyController::class, 'store_service_banner']);
            Route::post('/delete-service-banner', [App\Http\Controllers\Admin\CompanyController::class, 'delete_service_banner']);

            // WhitelabelController routes
            Route::get('/white-label', [App\Http\Controllers\Admin\WhitelabelController::class, 'white_label']);
            Route::post('/view-white-label-details', [App\Http\Controllers\Admin\WhitelabelController::class, 'view_white_label_details']);
            Route::post('/create-white-label', [App\Http\Controllers\Admin\WhitelabelController::class, 'create_white_label']);
            Route::post('/update-white-label', [App\Http\Controllers\Admin\WhitelabelController::class, 'update_white_label']);

            // PackageController routes
            Route::get('/package-settings', [App\Http\Controllers\Admin\PackageController::class, 'package_settings']);
            Route::post('/view-package-details', [App\Http\Controllers\Admin\PackageController::class, 'view_package_details']);
            Route::post('/update-package', [App\Http\Controllers\Admin\PackageController::class, 'update_package']);
            Route::post('/create-new-package', [App\Http\Controllers\Admin\PackageController::class, 'create_new_package']);
            Route::post('/delete-package', [App\Http\Controllers\Admin\PackageController::class, 'delete_package']);
            Route::post('/copy-package', [App\Http\Controllers\Admin\PackageController::class, 'copy_package']);

            // CommissionController routes
            Route::post('/commission-setup', [App\Http\Controllers\Admin\CommissionController::class, 'commission_setup']);
            Route::post('/set-operator-commission', [App\Http\Controllers\Admin\CommissionController::class, 'set_operator_commission']);
            Route::post('/view-operator-commission', [App\Http\Controllers\Admin\CommissionController::class, 'view_operator_commission']);
            Route::post('/update-operator-commission', [App\Http\Controllers\Admin\CommissionController::class, 'update_operator_commission']);
            Route::post('/store-commission', [App\Http\Controllers\Admin\CommissionController::class, 'store_commission']);
            Route::post('/delete-commission-slab', [App\Http\Controllers\Admin\CommissionController::class, 'delete_commission_slab']);
            Route::post('/store-bulk-commission', [App\Http\Controllers\Admin\CommissionController::class, 'store_bulk_commission']);


            Route::get('/merchant-commission-setup/{id}', [App\Http\Controllers\Admin\MerchantCommissionController::class, 'merchant_commission_setup'])->name('merchantSetupCommission');
            Route::post('/merchant-set-operator-commission', [App\Http\Controllers\Admin\MerchantCommissionController::class, 'merchant_set_operator_commission'])->name('merchantSetOperatorCommission');
            Route::post('/merchant-store-commission', [App\Http\Controllers\Admin\MerchantCommissionController::class, 'merchant_store_commission'])->name('merchantStoreCommission');
            Route::post('/merchant-view-operator-commission', [App\Http\Controllers\Admin\MerchantCommissionController::class, 'merchant_view_operator_commission'])->name('merchantViewCommission');
            Route::post('/merchant-update-operator-commission', [App\Http\Controllers\Admin\MerchantCommissionController::class, 'merchant_update_operator_commission'])->name('merchantUpdateCommission');
            Route::post('/merchant-delete-commission-slab', [App\Http\Controllers\Admin\MerchantCommissionController::class, 'merchant_delete_commission_slab']);




            // BankController routes
            Route::get('/bank-settings', [App\Http\Controllers\Admin\BankController::class, 'bank_settings']);
            Route::post('/view-bank-details', [App\Http\Controllers\Admin\BankController::class, 'view_bank_details']);
            Route::post('/update-bank', [App\Http\Controllers\Admin\BankController::class, 'update_bank']);
            Route::post('/add-bank', [App\Http\Controllers\Admin\BankController::class, 'add_bank']);

            // MemberController routes
            Route::get('/member-list/{id}', [App\Http\Controllers\Admin\MemberController::class, 'member_list']);
            Route::get('/parent-down-users/{role_slug}/{parent_id}', [App\Http\Controllers\Admin\MemberController::class, 'parent_down_users']);
            Route::get('/member-list-api', [App\Http\Controllers\Admin\MemberController::class, 'member_list_api']);
            Route::post('/view-members-details', [App\Http\Controllers\Admin\MemberController::class, 'view_members_details']);
            Route::post('/get-distric-by-state', [App\Http\Controllers\Admin\MemberController::class, 'get_distric_by_state']);
            Route::post('/get-city-by-state', [App\Http\Controllers\Admin\MemberController::class, 'get_city_by_state']);
            Route::get('/create-user/{id}', [App\Http\Controllers\Admin\MemberController::class, 'create_user']);
            Route::get('/create-super-admin', [App\Http\Controllers\Admin\MemberController::class, 'create_super_admin']);
            Route::get('/view-update-users/{id}', [App\Http\Controllers\Admin\MemberController::class, 'view_update_users']);
            Route::get('/view-update-admins/{id}', [App\Http\Controllers\Admin\MemberController::class, 'view_update_admins']);
            Route::post('/update-members', [App\Http\Controllers\Admin\MemberController::class, 'update_members']);
            Route::post('/update-admins', [App\Http\Controllers\Admin\MemberController::class, 'update_admins']);
            Route::post('/reset-password', [App\Http\Controllers\Admin\MemberController::class, 'reset_password']);
            Route::post('/transaction-reset-password', [App\Http\Controllers\Admin\MemberController::class, 'transaction_reset_password']);
            Route::post('/store-members', [App\Http\Controllers\Admin\MemberController::class, 'store_members']);
            Route::post('/store-admin-users', [App\Http\Controllers\Admin\MemberController::class, 'store_admin_users']);
            Route::get('/view-user-kyc/{id}', [App\Http\Controllers\Admin\MemberController::class, 'view_user_kyc']);
            Route::post('/update-kyc', [App\Http\Controllers\Admin\MemberController::class, 'update_kyc']);
            Route::get('/suspended-users', [App\Http\Controllers\Admin\MemberController::class, 'suspended_users']);
            Route::get('/suspended-user-api', [App\Http\Controllers\Admin\MemberController::class, 'suspended_user_api']);
            Route::post('/create-pancard-id', [App\Http\Controllers\Admin\MemberController::class, 'create_pancard_id']);
            Route::post('/update-dropdown-package', [App\Http\Controllers\Admin\MemberController::class, 'update_dropdown_package']);
            Route::post('/update-dropdown-parent', [App\Http\Controllers\Admin\MemberController::class, 'update_dropdown_parent']);
            Route::get('/not-working-users', [App\Http\Controllers\Admin\MemberController::class, 'not_working_users']);
            Route::get('/not-working-users-api', [App\Http\Controllers\Admin\MemberController::class, 'not_working_users_api']);
            Route::post('/refresh-scheme', [App\Http\Controllers\Admin\MemberController::class, 'refresh_scheme']);
            Route::get('/export-member', [App\Http\Controllers\Admin\MemberController::class, 'export_member']);
            Route::get('/all-user-list/{slug?}', [App\Http\Controllers\Admin\MemberController::class, 'all_user_list']);
            Route::get('/all-user-list-api', [App\Http\Controllers\Admin\MemberController::class, 'all_user_list_api']);
            Route::post('/force-logout-all-users', [App\Http\Controllers\Admin\MemberController::class, 'force_logout_all_users']);
            Route::post('/view-user-active-services', [App\Http\Controllers\Admin\MemberController::class, 'view_user_active_services']);
            Route::get('/bankit-users', [App\Http\Controllers\Admin\MemberController::class, 'bankit_user']);
            Route::get('/bankit-user-api', [App\Http\Controllers\Admin\MemberController::class, 'bankit_user_api']);
            Route::get('/iserveu-users', [App\Http\Controllers\Admin\MemberController::class, 'iserveu_user']);
            Route::get('/iserveu-user-api', [App\Http\Controllers\Admin\MemberController::class, 'iserveu_user_api']);
            Route::post('/update-iserveu-user-onboard', [App\Http\Controllers\Admin\MemberController::class, 'update_iserveu_user_onboard']);
            Route::get('/merchant-users', [App\Http\Controllers\Admin\MemberController::class, 'merchantUsers']);
            Route::get('/merchant-user-api', [App\Http\Controllers\Admin\MemberController::class, 'merchantUsersApi']);
            Route::get('/create-merchant', [App\Http\Controllers\Admin\MemberController::class, 'merchantUsersCreate']);
            Route::post('/store-merchant-members', [App\Http\Controllers\Admin\MemberController::class, 'storeMerchantUsers']);
            Route::post('/view-merchant-details', [App\Http\Controllers\Admin\MemberController::class, 'viewMerchantDetails']);
            Route::get('/view-update-merchants/{id}', [App\Http\Controllers\Admin\MemberController::class, 'viewMerchantUsers']);
            Route::post('/update-members-merchant', [App\Http\Controllers\Admin\MemberController::class, 'updateMerchantUsers']);
            Route::post('/view-merchant-active-services', [App\Http\Controllers\Admin\MemberController::class, 'view_merchant_active_services']);
            Route::post('/add-merchant-balance', [App\Http\Controllers\Admin\MemberController::class, 'add_merchant_balance']);

            Route::post('/member/view-settings', [App\Http\Controllers\Admin\MemberController::class, 'getViewApiSettings'])->name("admin.member.view.settings");
            Route::get('/member/edit/settings/{id}', [App\Http\Controllers\Admin\MemberController::class, 'getEditApiSettings'])->name("admin.member.update.settings");
            Route::post('/member/update/setting', [App\Http\Controllers\Admin\MemberController::class, 'updateApiSettings'])->name("admin.member.store.settings");


            //Admin users
            Route::get('/super-admin', [App\Http\Controllers\Admin\MemberController::class, 'admin_list']);

            // ApimasterController routes
            Route::get('/provider-master', [App\Http\Controllers\Admin\ApimasterController::class, 'provider_master']);
            Route::get('/provider-master-api', [App\Http\Controllers\Admin\ApimasterController::class, 'provider_master_api']);
            Route::post('/view-provider', [App\Http\Controllers\Admin\ApimasterController::class, 'view_provider']);
            Route::post('/update-provider', [App\Http\Controllers\Admin\ApimasterController::class, 'update_provider']);
            Route::post('/add-provider', [App\Http\Controllers\Admin\ApimasterController::class, 'add_provider']);
            Route::post('/store-provider-logo', [App\Http\Controllers\Admin\ApimasterController::class, 'store_provider_logo']);
            Route::get('/api-master', [App\Http\Controllers\Admin\ApimasterController::class, 'api_master']);
            Route::get('/api-balance', [App\Http\Controllers\Admin\ApimasterController::class, 'api_balance']);
            Route::post('/view-api-credentials', [App\Http\Controllers\Admin\ApimasterController::class, 'viewApiCredentials']);
            Route::post('/update-api-credentials', [App\Http\Controllers\Admin\ApimasterController::class, 'updateApiCredentials']);
            Route::post('/create-new-api', [App\Http\Controllers\Admin\ApimasterController::class, 'create_new_api']);
            Route::post('/view-api-details', [App\Http\Controllers\Admin\ApimasterController::class, 'view_api_details']);
            Route::post('/update-api-details', [App\Http\Controllers\Admin\ApimasterController::class, 'update_api_details']);
            Route::get('/webhook-setting/{id}', [App\Http\Controllers\Admin\ApimasterController::class, 'webhook_setting']);
            Route::post('/update-webhook-url', [App\Http\Controllers\Admin\ApimasterController::class, 'update_webhook_url']);
            Route::get('/response-setting/{id}', [App\Http\Controllers\Admin\ApimasterController::class, 'response_setting']);
            Route::post('/add-new-responses', [App\Http\Controllers\Admin\ApimasterController::class, 'add_new_responses']);
            Route::post('/view-api-responses', [App\Http\Controllers\Admin\ApimasterController::class, 'view_api_responses']);
            Route::post('/update-api-responses', [App\Http\Controllers\Admin\ApimasterController::class, 'update_api_responses']);
            Route::post('/delete-api-responses', [App\Http\Controllers\Admin\ApimasterController::class, 'delete_api_responses']);
            Route::get('/webhooks-logs/{id}', [App\Http\Controllers\Admin\ApimasterController::class, 'webhooks_logs']);
            Route::get('/denomination-wise-api', [App\Http\Controllers\Admin\ApimasterController::class, 'denomination_wise_api']);
            Route::post('/save-denomination-wise-api', [App\Http\Controllers\Admin\ApimasterController::class, 'save_denomination_wise_api']);
            Route::post('/view-denomination-wise-api', [App\Http\Controllers\Admin\ApimasterController::class, 'view_denomination_wise_api']);
            Route::post('/update-denomination-wise-api', [App\Http\Controllers\Admin\ApimasterController::class, 'update_denomination_wise_api']);
            Route::post('/delete-denomination-wise-api', [App\Http\Controllers\Admin\ApimasterController::class, 'delete_denomination_wise_api']);
            Route::post('/view-check-balance-api', [App\Http\Controllers\Admin\ApimasterController::class, 'view_check_balance_api']);
            Route::post('/update-check-balance-api', [App\Http\Controllers\Admin\ApimasterController::class, 'update_check_balance_api']);
            Route::post('/get-api-balance', [App\Http\Controllers\Admin\ApimasterController::class, 'get_api_balance']);
            Route::get('/view-api-provider/{id}', [App\Http\Controllers\Admin\ApimasterController::class, 'view_api_provider']);
            Route::post('/view-api-master-provider', [App\Http\Controllers\Admin\ApimasterController::class, 'view_api_master_provider']);
            Route::post('/update-api-provider', [App\Http\Controllers\Admin\ApimasterController::class, 'update_api_provider']);
            Route::get('/number-series-master', [App\Http\Controllers\Admin\ApimasterController::class, 'number_series_master']);
            Route::post('/view-number-series', [App\Http\Controllers\Admin\ApimasterController::class, 'view_number_series']);
            Route::post('/update-number-series', [App\Http\Controllers\Admin\ApimasterController::class, 'update_number_series']);
            Route::post('/add-number-series', [App\Http\Controllers\Admin\ApimasterController::class, 'add_number_series']);
            Route::get('/state-wise-api', [App\Http\Controllers\Admin\ApimasterController::class, 'state_wise_api']);
            Route::get('/state-provider-setting/{id}', [App\Http\Controllers\Admin\ApimasterController::class, 'state_provider_setting']);
            Route::post('/update-state-wise-api-status', [App\Http\Controllers\Admin\ApimasterController::class, 'update_state_wise_api_status']);
            Route::post('/update-state-wise-api-id', [App\Http\Controllers\Admin\ApimasterController::class, 'update_state_wise_api_id']);
            Route::get('/backup-api-master', [App\Http\Controllers\Admin\ApimasterController::class, 'backup_api_master']);
            Route::post('/save-backup-api', [App\Http\Controllers\Admin\ApimasterController::class, 'save_backup_api']);
            Route::post('/delete-backup-api', [App\Http\Controllers\Admin\ApimasterController::class, 'delete_backup_api']);
            Route::post('/view-backup-api', [App\Http\Controllers\Admin\ApimasterController::class, 'view_backup_api']);
            Route::post('/update-backup-api', [App\Http\Controllers\Admin\ApimasterController::class, 'update_backup_api']);
            Route::get('/api-switching', [App\Http\Controllers\Admin\ApimasterController::class, 'api_switching']);
            Route::post('/update-api-switching', [App\Http\Controllers\Admin\ApimasterController::class, 'update_api_switching']);
            Route::get('/user-operator-limit', [App\Http\Controllers\Admin\ApimasterController::class, 'user_operator_limit']);
            Route::get('/user-operator-limit-api', [App\Http\Controllers\Admin\ApimasterController::class, 'user_operator_limit_api']);
            Route::post('/get-user-by-role', [App\Http\Controllers\Admin\ApimasterController::class, 'get_user_by_role']);
            Route::get('/view-operator-limit/{id}', [App\Http\Controllers\Admin\ApimasterController::class, 'view_operator_limit']);
            Route::post('/update-operator-limit', [App\Http\Controllers\Admin\ApimasterController::class, 'update_operator_limit']);
            Route::get('/all-api-balance', [App\Http\Controllers\Admin\ApibalanceController::class, 'allApiBal']);

            //Api commission master controller
            Route::get('/api-commission-master', [App\Http\Controllers\Admin\ApiCommissionMasterController::class, 'api_commission_master']);
            Route::post('/setup-api-commission-master', [App\Http\Controllers\Admin\ApiCommissionMasterController::class, 'setup_api_commission_master']);
            Route::post('/store-api-commission-master', [App\Http\Controllers\Admin\ApiCommissionMasterController::class, 'store_api_commission_master']);
            Route::post('/view-api-commission-master', [App\Http\Controllers\Admin\ApiCommissionMasterController::class, 'view_api_commission_master']);
            Route::post('/update-api-commission-master', [App\Http\Controllers\Admin\ApiCommissionMasterController::class, 'update_api_commission_master']);
            Route::post('/delete-api-commission-master', [App\Http\Controllers\Admin\ApiCommissionMasterController::class, 'delete_api_commission_master']);

            // MasterController routes
            Route::get('/bank-master', [App\Http\Controllers\Admin\MasterController::class, 'bank_master']);
            Route::post('/view-bank-master', [App\Http\Controllers\Admin\MasterController::class, 'view_bank_master']);
            Route::post('/update-bank-master', [App\Http\Controllers\Admin\MasterController::class, 'update_bank_master']);
            Route::post('/add-banks', [App\Http\Controllers\Admin\MasterController::class, 'add_banks']);
            Route::get('/role-master', [App\Http\Controllers\Admin\MasterController::class, 'role_master']);
            Route::post('/view-role-master', [App\Http\Controllers\Admin\MasterController::class, 'view_role_master']);
            Route::post('/update-role-master', [App\Http\Controllers\Admin\MasterController::class, 'update_role_master']);
            Route::get('/status-master', [App\Http\Controllers\Admin\MasterController::class, 'status_master']);
            Route::post('/view-status-master', [App\Http\Controllers\Admin\MasterController::class, 'view_status_master']);
            Route::post('/update-status-master', [App\Http\Controllers\Admin\MasterController::class, 'update_status_master']);
            Route::get('/service-master', [App\Http\Controllers\Admin\MasterController::class, 'service_master']);
            Route::post('/add-service-master', [App\Http\Controllers\Admin\MasterController::class, 'add_service_master']);
            Route::post('/view-serivce-master', [App\Http\Controllers\Admin\MasterController::class, 'view_serivce_master']);
            Route::post('/update-service-master', [App\Http\Controllers\Admin\MasterController::class, 'update_service_master']);
            Route::post('/upload-service-master-icon', [App\Http\Controllers\Admin\MasterController::class, 'upload_service_master_icon']);
            Route::get('/payment-method', [App\Http\Controllers\Admin\MasterController::class, 'payment_method']);
            Route::get('/service-group-master', [App\Http\Controllers\Admin\MasterController::class, 'service_group_master']);
            Route::post('/add-service-group-master', [App\Http\Controllers\Admin\MasterController::class, 'add_service_group_master']);
            Route::post('/view-service-group-master', [App\Http\Controllers\Admin\MasterController::class, 'view_service_group_master']);
            Route::post('/update-service-group-master', [App\Http\Controllers\Admin\MasterController::class, 'update_service_group_master']);
            Route::post('/view-payment-method', [App\Http\Controllers\Admin\MasterController::class, 'view_payment_method']);
            Route::post('/update-payment-method', [App\Http\Controllers\Admin\MasterController::class, 'update_payment_method']);
            Route::post('/add-payment-method', [App\Http\Controllers\Admin\MasterController::class, 'add_payment_method']);
            Route::get('/payout-beneficiary-master', [App\Http\Controllers\Admin\MasterController::class, 'payout_beneficiary_master']);
            Route::post('/update-payout-beneficiary', [App\Http\Controllers\Admin\MasterController::class, 'update_payout_beneficiary']);
            Route::get('/contact-enquiry', [App\Http\Controllers\Admin\MasterController::class, 'contact_enquiry']);
            Route::post('/delete-contact-enquiry', [App\Http\Controllers\Admin\MasterController::class, 'delete_contact_enquiry']);
            Route::get('/cashfree-gateway-master', [App\Http\Controllers\Admin\MasterController::class, 'cashfree_gateway_master']);
            Route::post('/view-cashfree-gateway-master', [App\Http\Controllers\Admin\MasterController::class, 'view_cashfree_gateway_master']);
            Route::post('/update-cashfree-gateway-master', [App\Http\Controllers\Admin\MasterController::class, 'update_cashfree_gateway_master']);

            Route::get('/role', [RoleController::class, 'index'])->name("admin.roles");
            Route::get('/role/create', [RoleController::class, 'create'])->name('admin.role.create');
            Route::get('/role/{id}/edit', [RoleController::class, 'edit'])->name('admin.role.edit');
            Route::get('/role/{id}/show', [RoleController::class, 'show'])->name('admin.role.show');
            Route::post('/role/destroy', [RoleController::class, 'destroy'])->name('admin.role.destroy');
            Route::post('/role/store', [RoleController::class, 'store'])->name('admin.role.store');
            Route::post('/role/update/{id}', [RoleController::class, 'update'])->name('admin.role.update');


            Route::group(['prefix' => 'gateway-charges'], function () {
                Route::get('/welcome', [App\Http\Controllers\Admin\MasterController::class, 'gateway_charges']);
                Route::post('/view-charges-details', [App\Http\Controllers\Admin\MasterController::class, 'view_gateway_charges']);
                Route::post('/update-charges-details', [App\Http\Controllers\Admin\MasterController::class, 'update_gateway_charges_details']);
            });

            Route::get('/agent-onboarding-list', [App\Http\Controllers\Admin\MasterController::class, 'agent_onboarding_list']);
            Route::get('/agent-onboarding-list-api', [App\Http\Controllers\Admin\MasterController::class, 'agent_onboarding_list_api']);
            Route::post('/agent-onboarding-user-details', [App\Http\Controllers\Admin\MasterController::class, 'agent_onboarding_user_details']);
            Route::post('/save-agent-onboarding', [App\Http\Controllers\Admin\MasterController::class, 'save_agent_onboarding']);
            Route::post('/view-agent-onboarding', [App\Http\Controllers\Admin\MasterController::class, 'view_agent_onboarding']);
            Route::post('/update-agent-onboarding', [App\Http\Controllers\Admin\MasterController::class, 'update_agent_onboarding']);

            // broadcast by admin
            Route::get('/broadcast', [App\Http\Controllers\Admin\MasterController::class, 'broadcast']);
            Route::post('/save-broadcast', [App\Http\Controllers\Admin\MasterController::class, 'save_broadcast']);
            // Credentials routes
            Route::post('/store-credentials', [App\Http\Controllers\Admin\MasterController::class, 'store_credentials'])->name('admin.master.credentials.store');
            Route::post('/update-credentials', [App\Http\Controllers\Admin\MasterController::class, 'update_credentials'])->name('admin.master.credentials.update');

            Route::group(['prefix' => 'report/v1', 'middleware' => 'auth'], function () {
                Route::get('/all-transaction-report-api', [App\Http\Controllers\Admin\ReportController::class, 'all_transaction_report_api']);

                //Balance Enquiries
                Route::get('/balance-enquiries-report', [App\Http\Controllers\Admin\BalanceEnquiryController::class, 'balance_enquiries_report']);
                Route::get('/balance-enquiries-report-api', [App\Http\Controllers\Admin\BalanceEnquiryController::class, 'balance_enquiries_report_api']);
                // Dynamic Report
                Route::get('/welcome/{report_slug}', [App\Http\Controllers\Admin\ReportController::class, 'welcome']);
                Route::get('/search/{report_slug}', [App\Http\Controllers\Admin\ReportController::class, 'search_report']);

                Route::get('/move-to-bank-history', [App\Http\Controllers\Admin\ReportController::class, 'move_to_bank_history']);
                Route::get('/move-to-bank-history-api', [App\Http\Controllers\Admin\ReportController::class, 'move_to_bank_history_api']);


                // Close dynamic report
                Route::post('/view-recharge-details', [App\Http\Controllers\Admin\ReportController::class, 'view_recharge_details']);
                Route::post('/view-transaction-logs', [App\Http\Controllers\Admin\ReportController::class, 'view_transaction_logs']);
                Route::post('/recharge-update-for-refund', [App\Http\Controllers\Admin\ReportController::class, 'recharge_update_for_refund']);
                Route::post('/update-selected-transaction', [App\Http\Controllers\Admin\ReportController::class, 'update_selected_transaction']);
                Route::get('/pending-transaction-api', [App\Http\Controllers\Admin\ReportController::class, 'pending_transaction_api']);
                Route::get('/profit-distribution', [App\Http\Controllers\Admin\ReportController::class, 'profit_distribution']);
                Route::get('/profit-distribution-api', [App\Http\Controllers\Admin\ReportController::class, 'profit_distribution_api']);
                Route::get('/search-refund-manager', [App\Http\Controllers\Admin\ReportController::class, 'search_refund_manager']);
                Route::get('/ledger-report', [App\Http\Controllers\Admin\ReportController::class, 'ledger_report']);
                Route::get('/ledger-report-api', [App\Http\Controllers\Admin\ReportController::class, 'ledger_report_api']);
                Route::get('/user-ledger-report/{id}', [App\Http\Controllers\Admin\ReportController::class, 'user_ledger_report']);
                Route::get('/user-ledger-report-api', [App\Http\Controllers\Admin\ReportController::class, 'user_ledger_report_api']);

                Route::get('/debit-report-api', [App\Http\Controllers\Admin\ReportController::class, 'debit_report_api']);
                Route::get('/credit-report-api', [App\Http\Controllers\Admin\ReportController::class, 'credit_report_api']);
                Route::post('/find-ip-location', [App\Http\Controllers\Admin\ReportController::class, 'find_ip_location']);
                Route::get('/api-profit-loss-report-api', [App\Http\Controllers\Admin\ReportController::class, 'apiProfitLossReportApi']);
                Route::post('/view-dmt-transaction-details', [App\Http\Controllers\Admin\ReportController::class, 'view_dmt_transaction_details']);
                Route::post('/dmt-update-for-refund', [App\Http\Controllers\Admin\ReportController::class, 'dmt_update_for_refund']);
                Route::post('/refund-dmt-resend-otp', [App\Http\Controllers\Admin\ReportController::class, 'refund_dmt_resend_otp']);

                Route::get('/payout-request', [App\Http\Controllers\Admin\ReportController::class, 'aepsPayourRequest']);
                Route::get('/payout-request-api', [App\Http\Controllers\Admin\ReportController::class, 'aepsPayoutRequestApi']);
            });
            // Transfer controller
            Route::get('/whitelist-bank-request', [App\Http\Controllers\Admin\PaymentrequestController::class, 'whitelistBankRequest']);
            Route::get('/whitelist-merchant-bank-request', [App\Http\Controllers\Admin\PaymentrequestController::class, 'whitelistMerchantBankRequest']);
            Route::post('/approve-reject-bank', [App\Http\Controllers\Admin\PaymentrequestController::class, 'approveRejectBank']);
            Route::get('/purchase-balance-api', [App\Http\Controllers\Admin\TransferController::class, 'purchase_balance_api']);
            Route::post('/purchase-balance-now', [App\Http\Controllers\Admin\TransferController::class, 'purchase_balance_now']);

            Route::get('/balance-transfer-api', [App\Http\Controllers\Admin\TransferController::class, 'balance_transfer_api']);

            Route::post('/balance-transfer-now', [App\Http\Controllers\Admin\TransferController::class, 'balance_transfer_now']);

            Route::post('/balance-return-now', [App\Http\Controllers\Admin\TransferController::class, 'balance_return_now']);

            Route::get('/balance-return-request', [App\Http\Controllers\Admin\TransferController::class, 'balance_return_request']);
            Route::post('/view-return-request', [App\Http\Controllers\Admin\TransferController::class, 'view_return_request']);
            Route::post('/approve-payment-return-request', [App\Http\Controllers\Admin\TransferController::class, 'approve_payment_return_request']);

            // Payment request controller
            Route::get('/payment-request', [App\Http\Controllers\Admin\PaymentrequestController::class, 'payment_request']);
            Route::post('/save-payment-request', [App\Http\Controllers\Admin\PaymentrequestController::class, 'save_payment_request']);

            Route::get('/payment-request-view-api', [App\Http\Controllers\Admin\PaymentrequestController::class, 'payment_request_view_api']);

            Route::post('/update-payment-request', [App\Http\Controllers\Admin\PaymentrequestController::class, 'update_payment_request']);
            Route::post('/payment-request-edit-now', [App\Http\Controllers\Admin\PaymentrequestController::class, 'payment_request_edit_now']);
            Route::post('/view-reapprove-payment-request', [App\Http\Controllers\Admin\PaymentrequestController::class, 'view_reapprove_payment_request']);
            Route::post('/update-reapprove-payment-request', [App\Http\Controllers\Admin\PaymentrequestController::class, 'update_reapprove_payment_request']);


            // Dispute controller
            Route::get('/pending-dispute', [App\Http\Controllers\Admin\DisputeController::class, 'pending_dispute']);
            Route::post('/dispute-transaction', [App\Http\Controllers\Admin\DisputeController::class, 'dispute_transaction']);
            Route::post('/view-dispute-conversation', [App\Http\Controllers\Admin\DisputeController::class, 'view_dispute_conversation']);
            Route::post('/get-dispute-chat', [App\Http\Controllers\Admin\DisputeController::class, 'get_dispute_chat']);
            Route::post('/send-chat-message', [App\Http\Controllers\Admin\DisputeController::class, 'send_chat_message']);
            Route::post('/update-complaint-status', [App\Http\Controllers\Admin\DisputeController::class, 'update_complaint_status']);
            Route::get('/solve-dispute', [App\Http\Controllers\Admin\DisputeController::class, 'solve_dispute']);
            Route::get('/solve-dispute-api', [App\Http\Controllers\Admin\DisputeController::class, 'solve_dispute_api']);


            // Website master controller
            Route::get('/home-page-content', [App\Http\Controllers\Admin\WebsiteMasterController::class, 'home_page_content']);
            Route::get('/dynamic-page', [App\Http\Controllers\Admin\WebsiteMasterController::class, 'dynamic_page']);
            Route::get('/front-banners', [App\Http\Controllers\Admin\WebsiteMasterController::class, 'front_banners']);
            Route::post('/store-front-banner', [App\Http\Controllers\Admin\WebsiteMasterController::class, 'store_front_banner']);
            Route::post('/delete-front-banner', [App\Http\Controllers\Admin\WebsiteMasterController::class, 'delete_front_banner']);
            Route::get('/create-navigation', [App\Http\Controllers\Admin\WebsiteMasterController::class, 'create_navigation']);
            Route::post('/store-navigation', [App\Http\Controllers\Admin\WebsiteMasterController::class, 'store_navigation']);
            Route::get('/edit-navigation/{id}', [App\Http\Controllers\Admin\WebsiteMasterController::class, 'edit_navigation']);
            Route::post('/update-navigation', [App\Http\Controllers\Admin\WebsiteMasterController::class, 'update_navigation']);
            Route::post('/delete-navigation', [App\Http\Controllers\Admin\WebsiteMasterController::class, 'delete_navigation']);
            Route::get('/add-content/{id}', [App\Http\Controllers\Admin\WebsiteMasterController::class, 'add_content']);
            Route::post('/update-content', [App\Http\Controllers\Admin\WebsiteMasterController::class, 'update_content']);

            // Income controller
            Route::group(['prefix' => 'income'], function () {
                Route::get('/user-income/{id}', [App\Http\Controllers\Admin\IncomeController::class, 'user_income']);
                Route::get('/user-income-api/{id}', [App\Http\Controllers\Admin\IncomeController::class, 'user_income_api']);
                Route::get('/my-income', [App\Http\Controllers\Admin\IncomeController::class, 'my_income']);
                Route::get('/my-income-api', [App\Http\Controllers\Admin\IncomeController::class, 'my_income_api']);

            });

            //Merchant Report Route
            Route::post('/merchant-file-download', [App\Http\Controllers\Admin\DownloadController::class, 'merchant_download_file']);
            Route::get('/merchant-transaction-report', [App\Http\Controllers\Admin\ReportController::class, 'merchant_all_transaction_report']);
            Route::get('/merchant-transaction-report-api', [App\Http\Controllers\Admin\ReportController::class, 'merchant_all_transaction_report_api']);
            Route::post('/merchant-view-recharge-details', [App\Http\Controllers\Admin\ReportController::class, 'merchant_view_recharge_details']);
            Route::post('/merchant-find-ip-location', [App\Http\Controllers\Admin\ReportController::class, 'merchant_find_ip_location']);

            Route::get('/merchant-payout-report', [App\Http\Controllers\Admin\ReportController::class, 'merchant_payout_report']);
            Route::get('/merchant-payout-report-api', [App\Http\Controllers\Admin\ReportController::class, 'merchant_payout_report_api']);

            Route::get('/merchant-commission-report', [App\Http\Controllers\Admin\ReportController::class, 'merchant_commission_report']);
            Route::get('/merchant-commission-report-api', [App\Http\Controllers\Admin\ReportController::class, 'merchant_commission_report_api']);

            // Profit controller
            Route::get('/my-recharge-commission', [App\Http\Controllers\Admin\ProfitController::class, 'recharge_commission']);
            Route::get('/service-wise-commission/{id}', [App\Http\Controllers\Admin\ProfitController::class, 'service_wise_commission']);
            Route::post('/view-my-comm-slab', [App\Http\Controllers\Admin\ProfitController::class, 'view_my_comm_slab']);

            // Notification controller
            Route::group(['prefix' => 'notification'], function () {
                Route::get('/welcome', [App\Http\Controllers\Admin\NotificationController::class, 'welcome']);
                Route::post('/send-notification', [App\Http\Controllers\Admin\NotificationController::class, 'send_notification']);
                Route::get('/mark-all-read', [App\Http\Controllers\Admin\NotificationController::class, 'mark_all_read']);
                Route::get('/view/{id}', [App\Http\Controllers\Admin\NotificationController::class, 'view_notification']);
            });

            Route::group(['prefix' => 'download/v1', 'middleware' => 'auth'], function () {
                Route::post('/file-download', [App\Http\Controllers\Admin\DownloadController::class, 'download_file']);
                Route::post('/member-download', [App\Http\Controllers\Admin\DownloadController::class, 'member_download']);
                Route::post('/payment-request-view', [App\Http\Controllers\Admin\DownloadController::class, 'payment_request_view']);
                Route::post('/agent-onboarding-download', [App\Http\Controllers\Admin\DownloadController::class, 'agent_onboarding_download']);
                Route::get('/bankit-user-onboarding-download', [App\Http\Controllers\Admin\DownloadController::class, 'bankitUserExportNew']);
                Route::get('/bankit-user-onboarding-zip-download', [App\Http\Controllers\Admin\DownloadController::class, 'downloadBankitZipFile']);
                Route::get('/iserveu-user-onboarding-download', [App\Http\Controllers\Admin\DownloadController::class, 'iServeUUserExport']);
                Route::get('/merchant-users-download', [App\Http\Controllers\Admin\DownloadController::class, 'MerchantUserExport']);
            });

            Route::group(['prefix' => 'send-mail', 'middleware' => 'auth'], function () {
                Route::post('/send-statement', [App\Http\Controllers\Admin\SendmailController::class, 'send_statement']);
            });

            Route::group(['prefix' => 'invoice', 'middleware' => 'auth'], function () {
                Route::get('/gst-invoice', [App\Http\Controllers\Admin\InvoiceController::class, 'gst_invoice']);
                Route::post('/create-invoice', [App\Http\Controllers\Admin\InvoiceController::class, 'create_invoice']);
                Route::get('/generate-invoice/{id}', [App\Http\Controllers\Admin\InvoiceController::class, 'generate_invoice']);
            });

            Route::group(['prefix' => 'site-setting', 'middleware' => 'auth'], function () {
                Route::get('/welcome', [App\Http\Controllers\Admin\SitesettingController::class, 'welcome']);
                Route::post('/update-settings', [App\Http\Controllers\Admin\SitesettingController::class, 'update_settings']);
            });

            Route::group(['prefix' => 'sms-template', 'middleware' => 'auth'], function () {
                Route::get('/welcome', [App\Http\Controllers\Admin\SmstemplateController::class, 'welcome']);
                Route::post('/view-template', [App\Http\Controllers\Admin\SmstemplateController::class, 'view_template']);
                Route::post('/update-template', [App\Http\Controllers\Admin\SmstemplateController::class, 'update_template']);
            });

            Route::group(['prefix' => 'ecommerce', 'middleware' => 'auth'], function () {
                // EcommerceController routes
                Route::get('/main-category', [App\Http\Controllers\Admin\EcommerceController::class, 'main_category']);
                Route::post('/save-category', [App\Http\Controllers\Admin\EcommerceController::class, 'save_category']);
                Route::post('/view-category', [App\Http\Controllers\Admin\EcommerceController::class, 'view_category']);
                Route::post('/update-category', [App\Http\Controllers\Admin\EcommerceController::class, 'update_category']);

                Route::get('/sub-category', [App\Http\Controllers\Admin\EcommerceController::class, 'sub_category']);
                Route::post('/save-sub-category', [App\Http\Controllers\Admin\EcommerceController::class, 'save_sub_category']);
                Route::post('/view-sub-category', [App\Http\Controllers\Admin\EcommerceController::class, 'view_sub_category']);
                Route::post('/update-sub-category', [App\Http\Controllers\Admin\EcommerceController::class, 'update_sub_category']);

                Route::get('/shopping-banners', [App\Http\Controllers\Admin\EcommerceController::class, 'shopping_banners']);
                Route::post('/store-shopping-banners', [App\Http\Controllers\Admin\EcommerceController::class, 'store_shopping_banners']);
                Route::post('/delete-shopping-banners', [App\Http\Controllers\Admin\EcommerceController::class, 'delete_shopping_banners']);

                // BrandController routes
                Route::get('/brands', [App\Http\Controllers\Admin\BrandController::class, 'brands']);
                Route::post('/save-brands', [App\Http\Controllers\Admin\BrandController::class, 'save_brands']);
                Route::post('/view-brand', [App\Http\Controllers\Admin\BrandController::class, 'view_brand']);
                Route::post('/update-brands', [App\Http\Controllers\Admin\BrandController::class, 'update_brands']);

                // ProductController routes
                Route::get('/product-list', [App\Http\Controllers\Admin\ProductController::class, 'product_list']);
                Route::get('/product-list-api', [App\Http\Controllers\Admin\ProductController::class, 'product_list_api']);
                Route::get('/add-products', [App\Http\Controllers\Admin\ProductController::class, 'add_products']);
                Route::post('/get-sub-category', [App\Http\Controllers\Admin\ProductController::class, 'get_sub_category']);
                Route::post('/save-products', [App\Http\Controllers\Admin\ProductController::class, 'save_products']);
                Route::get('/update-product/{id}', [App\Http\Controllers\Admin\ProductController::class, 'update_product']);
                Route::post('/products-update-now', [App\Http\Controllers\Admin\ProductController::class, 'products_update_now']);

                Route::get('/add-product-image/{id}', [App\Http\Controllers\Admin\ProductController::class, 'add_product_image']);
                Route::post('/save-product-image', [App\Http\Controllers\Admin\ProductController::class, 'save_product_image']);
                Route::post('/delete-product-image', [App\Http\Controllers\Admin\ProductController::class, 'delete_product_image']);
                Route::post('/view-product-image', [App\Http\Controllers\Admin\ProductController::class, 'view_product_image']);
                Route::post('/update-product-image', [App\Http\Controllers\Admin\ProductController::class, 'update_product_image']);

                // OrderController routes
                Route::get('/order-report', [App\Http\Controllers\Admin\OrderController::class, 'order_report']);
                Route::get('/order-report-api', [App\Http\Controllers\Admin\OrderController::class, 'order_report_api']);
                Route::post('/view-order-product', [App\Http\Controllers\Admin\OrderController::class, 'view_order_product']);
                Route::post('/view-track-order', [App\Http\Controllers\Admin\OrderController::class, 'view_track_order']);
                Route::get('/product-report', [App\Http\Controllers\Admin\OrderController::class, 'product_report']);
                Route::get('/product-report-api', [App\Http\Controllers\Admin\OrderController::class, 'product_report_api']);
                Route::post('/view-order-product-details', [App\Http\Controllers\Admin\OrderController::class, 'view_order_product_details']);
                Route::post('/view-update-product', [App\Http\Controllers\Admin\OrderController::class, 'view_update_product']);
                Route::post('/update-product-delivery-status', [App\Http\Controllers\Admin\OrderController::class, 'update_product_delivery_status']);
                Route::get('/track-order', [App\Http\Controllers\Admin\OrderController::class, 'track_order']);
            });

            Route::group(['prefix' => 'vendor-payment', 'middleware' => 'auth'], function () {
                Route::get('/welcome', [App\Http\Controllers\Admin\VendorpaymentController::class, 'welcome']);
                Route::post('/add-api', [App\Http\Controllers\Admin\VendorpaymentController::class, 'add_api']);
                Route::post('/view-beneficiary', [App\Http\Controllers\Admin\VendorpaymentController::class, 'view_beneficiary']);
                Route::post('/add-beneficiary', [App\Http\Controllers\Admin\VendorpaymentController::class, 'add_beneficiary']);
                Route::post('/delete-beneficiary', [App\Http\Controllers\Admin\VendorpaymentController::class, 'delete_beneficiary']);
                Route::post('/view-transfer-details', [App\Http\Controllers\Admin\VendorpaymentController::class, 'view_transfer_details']);
                Route::post('/transfer-now', [App\Http\Controllers\Admin\VendorpaymentController::class, 'transfer_now']);
            });

            Route::group(['prefix' => 'whatsapp', 'middleware' => 'auth'], function () {
                Route::get('/role-wise', [App\Http\Controllers\Admin\WhatsappController::class, 'role_wise']);
                Route::post('/role-wise', [App\Http\Controllers\Admin\WhatsappController::class, 'role_wise_send']);
                Route::post('/role-wise-image', [App\Http\Controllers\Admin\WhatsappController::class, 'role_wise_send_image']);
            });

            Route::group(['prefix' => 'company-staff', 'middleware' => 'auth'], function () {
                Route::get('/welcome', [App\Http\Controllers\Admin\CompanystaffController::class, 'welcome']);
                Route::get('/get-users', [App\Http\Controllers\Admin\CompanystaffController::class, 'get_users']);
                Route::get('/permission/{id}', [App\Http\Controllers\Admin\CompanystaffController::class, 'permission']);
                Route::post('/update-permission', [App\Http\Controllers\Admin\CompanystaffController::class, 'update_permission']);
            });

            Route::group(['prefix' => 'api-commission/v1', 'middleware' => 'auth'], function () {
                Route::get('/welcome/{api_id}', [App\Http\Controllers\Admin\ApicommissionController::class, 'welcome']);
                Route::post('/view-providers', [App\Http\Controllers\Admin\ApicommissionController::class, 'view_providers']);
                Route::post('/save-commission', [App\Http\Controllers\Admin\ApicommissionController::class, 'save_commission']);
                Route::post('/view-provider-commission', [App\Http\Controllers\Admin\ApicommissionController::class, 'view_provider_commission']);
                Route::post('/update-commission', [App\Http\Controllers\Admin\ApicommissionController::class, 'update_commission']);
                Route::post('/delete-record', [App\Http\Controllers\Admin\ApicommissionController::class, 'delete_record']);
            });
        });

        // for user dashboard
        Route::prefix('agent')->group(function () {
            // Dashboard routes
            Route::get('/dashboard', [App\Http\Controllers\Agent\DashboardController::class, 'dashboard']);
            Route::get('/dashboard-details-api', [App\Http\Controllers\Agent\DashboardController::class, 'dashboard_details_api']);
            Route::get('/dashboard-chart-api', [App\Http\Controllers\Agent\DashboardController::class, 'dashboard_chart_api']);
            Route::get('/activity-logs', [App\Http\Controllers\Agent\DashboardController::class, 'activity_logs']);
            Route::get('/send-mail', [App\Http\Controllers\Agent\DashboardController::class, 'send_mail']);
            Route::get('/check-cashe', [App\Http\Controllers\Agent\DashboardController::class, 'check_cashe']);
            Route::post('/get-wallet-balance', [App\Http\Controllers\Agent\DashboardController::class, 'getWalletBalance']);

            // My Profile routes
            Route::get('/my-profile', [App\Http\Controllers\Agent\ProfileController::class, 'my_profile']);
            Route::post('/change-password', [App\Http\Controllers\Agent\ProfileController::class, 'change_password']);
            Route::post('/transaction-change-password', [App\Http\Controllers\Agent\ProfileController::class, 'trans_change_password']);
            Route::post('/update-profile', [App\Http\Controllers\Agent\ProfileController::class, 'update_profile']);
            Route::post('/update-profile-photo', [App\Http\Controllers\Agent\ProfileController::class, 'update_profile_photo']);
            Route::post('/update-shop-photo', [App\Http\Controllers\Agent\ProfileController::class, 'update_shop_photo']);
            Route::post('/update-gst-regisration-photo', [App\Http\Controllers\Agent\ProfileController::class, 'update_gst_regisration_photo']);
            Route::post('/update-pancard-photo', [App\Http\Controllers\Agent\ProfileController::class, 'update_pancard_photo']);
            Route::post('/cancel-cheque-photo', [App\Http\Controllers\Agent\ProfileController::class, 'cancel_cheque_photo']);
            Route::post('/address-proof-photo', [App\Http\Controllers\Agent\ProfileController::class, 'address_proof_photo']);
            Route::post('/aadhar-front-photo', [App\Http\Controllers\Agent\ProfileController::class, 'aadhar_front_photo']);
            Route::post('/aadhar-back-photo', [App\Http\Controllers\Agent\ProfileController::class, 'aadhar_back_photo']);
            Route::post('/agreement-form-doc', [App\Http\Controllers\Agent\ProfileController::class, 'agreement_form_doc']);
            Route::post('/get-distric-by-state', [App\Http\Controllers\Agent\ProfileController::class, 'get_distric_by_state']);
            Route::post('/update-verify-profile', [App\Http\Controllers\Agent\ProfileController::class, 'update_verify_profile']);
            Route::post('/verify-mobile', [App\Http\Controllers\Agent\ProfileController::class, 'verify_mobile']);
            Route::post('/verify-mobile-otp', [App\Http\Controllers\Agent\ProfileController::class, 'verify_mobile_otp']);
            Route::get('/view-kyc', [App\Http\Controllers\Agent\ProfileController::class, 'view_kyc']);
            Route::get('/my-settings', [App\Http\Controllers\Agent\ProfileController::class, 'my_settings']);
            Route::post('/save-settings', [App\Http\Controllers\Agent\ProfileController::class, 'save_settings']);
            Route::get('/transaction-pin', [App\Http\Controllers\Agent\ProfileController::class, 'transaction_pin']);
            Route::get('/latlong-security', [App\Http\Controllers\Agent\ProfileController::class, 'latlongSecurity']);
            Route::post('/regenerate-keys', [App\Http\Controllers\Agent\ProfileController::class, 'regenerate_keys'])->name('agent.regenerate_keys');


            Route::group(['prefix' => 'telecom/v1'], function () {
                Route::get('/welcome/{slug}', [App\Http\Controllers\Agent\ServiceController::class, 'welcome']);
            });

            Route::get('/prepaid-mobile', [App\Http\Controllers\Agent\ServiceController::class, 'prepaid_mobile']);
            Route::get('/dth', [App\Http\Controllers\Agent\ServiceController::class, 'dth']);
            Route::get('/postpaid', [App\Http\Controllers\Agent\ServiceController::class, 'postpaid']);
            Route::get('/electricity', [App\Http\Controllers\Agent\ServiceController::class, 'electricity']);
            Route::get('/landline', [App\Http\Controllers\Agent\ServiceController::class, 'landline']);
            Route::get('/water', [App\Http\Controllers\Agent\ServiceController::class, 'water']);
            Route::get('/gas', [App\Http\Controllers\Agent\ServiceController::class, 'gas']);
            Route::get('/fastag-recharge', [App\Http\Controllers\Agent\ServiceController::class, 'fastag_recharge']);
            Route::get('/insurance', [App\Http\Controllers\Agent\ServiceController::class, 'insurance']);
            Route::get('/loan-payment', [App\Http\Controllers\Agent\ServiceController::class, 'loan_payment']);
            Route::get('/broadband', [App\Http\Controllers\Agent\ServiceController::class, 'broadband']);
            Route::get('/subscription', [App\Http\Controllers\Agent\ServiceController::class, 'subscription']);
            Route::get('/housing-society', [App\Http\Controllers\Agent\ServiceController::class, 'housing_society']);
            Route::get('/cable-tv', [App\Http\Controllers\Agent\ServiceController::class, 'cable_tv']);
            Route::get('/lpg-gas', [App\Http\Controllers\Agent\ServiceController::class, 'lpg_gas']);
            Route::post('/generate-millisecond', [App\Http\Controllers\Agent\ServiceController::class, 'generate_millisecond']);
            Route::get('/certificate', [App\Http\Controllers\Agent\ServiceController::class, 'certificate']);

            // Recharge controller
            Route::post('/view-recharge-details', [App\Http\Controllers\Agent\RechargeController::class, 'view_recharge_details']);
            Route::post('/web-recharge-now', [App\Http\Controllers\Agent\RechargeController::class, 'web_recharge_now']);
            Route::post('/bbps-bill-verify', [App\Http\Controllers\Agent\RechargeController::class, 'bbps_bill_verify']);
            Route::post('/check-provider-validation', [App\Http\Controllers\Agent\RechargeController::class, 'check_provider_validation']);
            Route::get('/get-provider', [App\Http\Controllers\Agent\RechargeController::class, 'get_provider']);

            // Report controller
            Route::group(['prefix' => 'report/v1'], function () {
                Route::get('/all-transaction-report', [App\Http\Controllers\Agent\ReportController::class, 'all_transaction_report']);
                Route::get('/all-transaction-report-api', [App\Http\Controllers\Agent\ReportController::class, 'all_transaction_report_api']);

                Route::get('/balance-enquiries-report', [App\Http\Controllers\Agent\BalanceEnquiryController::class, 'balance_enquiries_report']);
                Route::get('/balance-enquiries-report-api', [App\Http\Controllers\Agent\BalanceEnquiryController::class, 'balance_enquiries_report_api']);
                Route::get('/ledger-report', [App\Http\Controllers\Agent\ReportController::class, 'ledger_report'])->name("admin.report.ledger_report");
                Route::get('/ledger-report-api', [App\Http\Controllers\Agent\ReportController::class, 'ledger_report_api']);
                Route::post('/view-transaction-details', [App\Http\Controllers\Agent\ReportController::class, 'view_recharge_details']); // Assuming this method is correctly defined
                Route::get('/welcome/{report_slug}', [App\Http\Controllers\Agent\ReportController::class, 'welcome']);
                Route::get('/search/{report_slug}', [App\Http\Controllers\Agent\ReportController::class, 'search_report']);

                Route::get('/move-to-bank-history', [App\Http\Controllers\Agent\ReportController::class, 'move_to_bank_history']);
                Route::get('/move-to-bank-history-api', [App\Http\Controllers\Agent\ReportController::class, 'move_to_bank_history_api']);
            });

            // Income Controller
            Route::get('/income-report', [App\Http\Controllers\Agent\SalesController::class, 'income_report']);
            Route::get('/income-report-api', [App\Http\Controllers\Agent\SalesController::class, 'income_report_api']);
            Route::get('/income-report-aeps-api', [App\Http\Controllers\Agent\SalesController::class, 'income_report_aeps_api']);

            Route::get('/operator-report', [App\Http\Controllers\Agent\SalesController::class, 'operator_report']);
            Route::get('/operator-report-api', [App\Http\Controllers\Agent\SalesController::class, 'operator_report_api']);

            // AEPS Report
            Route::get('/aeps-ledger-report', [App\Http\Controllers\Agent\AepsreportController::class, 'ledger_report']);
            Route::get('/aeps-ledger-report-api', [App\Http\Controllers\Agent\AepsreportController::class, 'ledger_report_api']);
            Route::get('/aeps-report', [App\Http\Controllers\Agent\AepsreportController::class, 'aeps_report']);
            Route::get('/aeps-report-api', [App\Http\Controllers\Agent\AepsreportController::class, 'aeps_report_api']);
            Route::get('/payout-settlement-report', [App\Http\Controllers\Agent\AepsreportController::class, 'payout_settlement_report']);
            Route::get('/payout-settlement-report-api', [App\Http\Controllers\Agent\AepsreportController::class, 'payout_settlement_report_api']);

            // Invoice
            Route::get('/transaction-receipt/{id}', [App\Http\Controllers\Agent\InvoiceController::class, 'transaction_receipt']);
            Route::get('/mobile-receipt/{id}', [App\Http\Controllers\Agent\InvoiceController::class, 'mobile_receipt']);
            Route::get('/money-receipt/{id}', [App\Http\Controllers\Agent\InvoiceController::class, 'money_receipt']);
            Route::get('/thermal-printer-receipt/{id}', [App\Http\Controllers\Agent\InvoiceController::class, 'thermal_printer_receipt']);

            Route::get('/cms-transaction-receipt/{id}', [App\Http\Controllers\Agent\InvoiceController::class, 'cmsTransactionReceipt']);
            Route::get('/cms-mobile-receipt/{id}', [App\Http\Controllers\Agent\InvoiceController::class, 'cmsMobileReceipt']);


            // Payment Request Controller
            Route::get('/payment-request', [App\Http\Controllers\Agent\PaymentrequestController::class, 'payment_request']);
            Route::get('/balance-return-request', [App\Http\Controllers\Agent\PaymentrequestController::class, 'balance_return_request']);

            // Payout Request Controller
            Route::get('/test-iserveu-payout-request', [App\Http\Controllers\Agent\AepsPayoutController::class, 'testIserveUPayout']);
            Route::get('/payout-request', [App\Http\Controllers\Agent\AepsPayoutController::class, 'index']);
            Route::get('/payout-request-api', [App\Http\Controllers\Agent\AepsPayoutController::class, 'payoutRequestApi']);
            Route::post('/send-payout-request', [App\Http\Controllers\Agent\AepsPayoutController::class, 'sendPayoutRequest']);
            Route::post('/add-account', [App\Http\Controllers\Agent\AepsPayoutController::class, 'addAccount']);
            Route::post('/upload-document', [App\Http\Controllers\Agent\AepsPayoutController::class, 'uploadDocument']);
            Route::get('/update-bank-master', [App\Http\Controllers\Agent\AepsPayoutController::class, 'updateBankMaster']);
            Route::get('/update-payout-account', [App\Http\Controllers\Agent\AepsPayoutController::class, 'updatePayoutAccount']);
            Route::post('/account-status-check', [App\Http\Controllers\Agent\AepsPayoutController::class, 'accountStatusCheck']);
            Route::post('/add-account-iserveu', [App\Http\Controllers\Agent\AepsPayoutController::class, 'addAccountIserveU']);
            Route::post('/send-payout-request-iserveu', [App\Http\Controllers\Agent\AepsPayoutController::class, 'sendPayoutRequestIserveU']);
            Route::post('/delete-account-iserveu', [App\Http\Controllers\Agent\AepsPayoutController::class, 'deleteAccountIserveU']);


            // Plan Controller
            Route::group(['prefix' => 'plan/v1'], function () {
                Route::post('/prepaid-plan', [App\Http\Controllers\Agent\PlanController::class, 'prepaid_plan']);
                Route::post('/dth-plan', [App\Http\Controllers\Agent\PlanController::class, 'dth_plan']);
                Route::post('/roffer-plan', [App\Http\Controllers\Agent\PlanController::class, 'roffer_plan']);
            });

            Route::post('/dth-customer-info', [App\Http\Controllers\Agent\PlanController::class, 'dth_customer_info']);
            Route::post('/find-operator', [App\Http\Controllers\Agent\PlanController::class, 'find_operator']);
            Route::post('/dth-refresh', [App\Http\Controllers\Agent\PlanController::class, 'dth_refresh']);
            Route::post('/dth-roffer', [App\Http\Controllers\Agent\PlanController::class, 'dth_roffer']);

            // Dispute Controller
            Route::get('/pending-dispute', [App\Http\Controllers\Agent\DisputeController::class, 'pending_dispute']);
            Route::post('/dispute-transaction', [App\Http\Controllers\Agent\DisputeController::class, 'dispute_transaction']);
            Route::post('/view-dispute-conversation', [App\Http\Controllers\Agent\DisputeController::class, 'view_dispute_conversation']);
            Route::post('/get-dispute-chat', [App\Http\Controllers\Agent\DisputeController::class, 'get_dispute_chat']);
            Route::post('/send-chat-message', [App\Http\Controllers\Agent\DisputeController::class, 'send_chat_message']);
            Route::get('/solve-dispute', [App\Http\Controllers\Agent\DisputeController::class, 'solve_dispute']);
            Route::post('/reopen-dispute', [App\Http\Controllers\Agent\DisputeController::class, 'reopen_dispute']);

            // Profit Controller
            Route::get('/my-recharge-commission', [App\Http\Controllers\Agent\ProfitController::class, 'recharge_commission']);
            Route::get('/service-wise-commission/{id}', [App\Http\Controllers\Agent\ProfitController::class, 'service_wise_commission']);
            Route::post('/view-my-comm-slab', [App\Http\Controllers\Agent\ProfitController::class, 'view_my_comm_slab']);

            // AEPS Controller
            Route::group(['prefix' => 'aeps/v1'], function () {
                Route::get('/agent-onboarding', [App\Http\Controllers\Agent\AepsController::class, 'agent_onboarding']);
                Route::post('/save-agent-onboarding', [App\Http\Controllers\Agent\AepsController::class, 'save_agent_onboarding']);
                Route::get('/route-1', [App\Http\Controllers\Agent\AepsController::class, 'aeps_route_1']);
                Route::get('/route-2', [App\Http\Controllers\Agent\AepsController::class, 'aeps_route_2']);
                Route::get('/route-1-landing', [App\Http\Controllers\Agent\AepsController::class, 'aeps_route_1_landing']);
                Route::get('/route-2-landing', [App\Http\Controllers\Agent\AepsController::class, 'aeps_route_2_landing']);
            });

            Route::group(['prefix' => 'aeps/v2', 'middleware' => 'auth'], function () {
                Route::get('/agent-onboarding', [App\Http\Controllers\Agent\PaysprintAepsController::class, 'agent_onboarding']);
                Route::post('/agent-onboarding', [App\Http\Controllers\Agent\PaysprintAepsController::class, 'agent_onboarding_save']);
                Route::get('/welcome', [App\Http\Controllers\Agent\PaysprintAepsController::class, 'welcome']);
                Route::post('initiate-transaction', [App\Http\Controllers\Agent\PaysprintAepsController::class, 'aeps_initiate_transaction']);
                Route::get('/withdrawal-check-status', [App\Http\Controllers\Agent\PaysprintAepsController::class, 'withdrawal_check_status']);
                Route::get('/aadhar-pay-check-status', [App\Http\Controllers\Agent\PaysprintAepsController::class, 'aadhar_pay_check_status']);
                Route::get('/two-factor-authentication', [App\Http\Controllers\Agent\PaysprintAepsController::class, 'twoFactorAuthentication']);
                Route::post('/two-factor-authentication', [App\Http\Controllers\Agent\PaysprintAepsController::class, 'twoFactorAuthenticationWeb']);
                Route::post('/merchant-auth-initiate', [App\Http\Controllers\Agent\PaysprintAepsController::class, 'merchantAuthInitiateWeb']);
            });

            Route::group(['prefix' => 'cash-deposit/v1', 'middleware' => 'auth'], function () {
                Route::get('/welcome', [App\Http\Controllers\Agent\CashDepositController::class, 'welcome']);
                Route::post('/initiate', [App\Http\Controllers\Agent\CashDepositController::class, 'initiateWeb']);
            });

            Route::group(['prefix' => 'payout/v1', 'middleware' => 'auth'], function () {
                Route::get('/move-to-wallet', [App\Http\Controllers\Agent\PayoutController::class, 'move_to_wallet']);
                Route::get('/move-to-bank', [App\Http\Controllers\Agent\PayoutController::class, 'move_to_bank']);
                Route::post('/move-to-wallet', [App\Http\Controllers\Agent\PayoutController::class, 'move_to_wallet_web']);
                Route::post('/beneficiary-list', [App\Http\Controllers\Agent\PayoutController::class, 'beneficiary_list']);
                Route::post('/account-validate', [App\Http\Controllers\Agent\PayoutController::class, 'account_validate']);
                Route::post('/add-beneficiary', [App\Http\Controllers\Agent\PayoutController::class, 'add_beneficiary']);
                Route::post('/delete-beneficiary', [App\Http\Controllers\Agent\PayoutController::class, 'delete_beneficiary']);
                Route::post('/transfer-now', [App\Http\Controllers\Agent\PayoutController::class, 'transfer_now']);
            });

            Route::group(['prefix' => 'money/v1', 'middleware' => 'auth'], function () {
                Route::get('/welcome', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'welcome']);
                Route::post('/get-customer', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'getCustomer']);
                Route::post('/add-sender', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'addSender']);
                Route::post('/confirm-sender', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'confirmSender']);
                Route::post('/sender-resend-otp', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'senderResendOtp']);
                Route::post('/get-all-beneficiary', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'getAllBeneficiary']);
                Route::post('/search-by-account', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'searchByAccount']);
                Route::post('/get-ifsc-code', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'getIfscCode']);
                Route::post('/add-beneficiary', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'addBeneficiary']);
                Route::post('/confirm-beneficiary', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'confirmBeneficiary']);
                Route::post('/delete-beneficiary', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'deleteBeneficiary']);
                Route::post('/confirm-delete-beneficiary', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'confirmDeleteBeneficiary']);
                Route::post('/account-verify', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'accountVerifyWeb']);
                Route::post('/view-account-transfer', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'viewAccountTransfer']);
                Route::post('/transfer-now', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'transferNowWeb']);
                Route::post('/get-transaction-charges', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'getTransactionCharges']);
                Route::post('/add-bankit-sender', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'addBankItSender']);
                Route::post('/sender-resend-otp-bankit', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'senderResendOtpBankIt']);
                Route::post('/add-beneficiary-bankit', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'addBeneficiaryBankIt']);
                Route::post('/get-bankit-beneficiary', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'getAllBeneficiaryBankIt']);
                Route::post('/account-verify-bankit', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'accountVerifyBankIt']);
                Route::post('/delete-beneficiary-bankit', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'deleteBeneficiaryBankIt']);
                Route::post('/view-account-transfer-bankit', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'viewAccountTransferBankIt']);
                Route::post('/transfer-now-bankit', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'transferNowWebBankIt']);

                /**/
                Route::get('/iServeU-test', [App\Http\Controllers\Agent\iServeUDmtController::class, 'index']);
                Route::post('/get-iServeU-customer', [App\Http\Controllers\Agent\iServeUDmtController::class, 'getCustomer']);
                Route::post('/get-iServeU-beneficiary', [App\Http\Controllers\Agent\iServeUDmtController::class, 'getAllBeneficiaryBankIt']);
                Route::post('/account-verify-iserveU', [App\Http\Controllers\Agent\iServeUDmtController::class, 'accountVerify']);
                Route::post('/get-ifsc-code-iserveU', [App\Http\Controllers\Agent\iServeUDmtController::class, 'getIfscCode']);
                Route::post('/delete-beneficiary-iserveU', [App\Http\Controllers\Agent\iServeUDmtController::class, 'deleteBeneficiary']);
                Route::post('/view-account-transfer-iserveU', [App\Http\Controllers\Agent\iServeUDmtController::class, 'viewAccountTransfer']);
                Route::post('/transfer-now-iserveU', [App\Http\Controllers\Agent\iServeUDmtController::class, 'transferNowWeb']);
                Route::post('/confirm-sender-iServeU', [App\Http\Controllers\Agent\iServeUDmtController::class, 'confirmSender']);
                Route::post('/iserveU-resend-otp', [App\Http\Controllers\Agent\iServeUDmtController::class, 'resendOtp']);
                Route::post('/iserveU-send-otp', [App\Http\Controllers\Agent\iServeUDmtController::class, 'sendOtp']);
            });

            Route::group(['prefix' => 'money/v2', 'middleware' => 'auth'], function () {
                Route::get('/welcome', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'welcome']);
                Route::post('/get-customer', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'getCustomer']);
                Route::post('/add-sender', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'addSender']);
                Route::post('/confirm-sender', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'confirmSender']);
                Route::post('/sender-resend-otp', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'senderResendOtp']);
                Route::post('/get-all-beneficiary', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'getAllBeneficiary']);
                Route::post('/search-by-account', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'searchByAccount']);
                Route::post('/get-ifsc-code', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'getIfscCode']);
                Route::post('/add-beneficiary', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'addBeneficiary']);
                Route::post('/confirm-beneficiary', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'confirmBeneficiary']);
                Route::post('/delete-beneficiary', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'deleteBeneficiary']);
                Route::post('/confirm-delete-beneficiary', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'confirmDeleteBeneficiary']);
                Route::post('/account-verify', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'accountVerifyWeb']);
                Route::post('/view-account-transfer', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'viewAccountTransfer']);
                Route::post('/transfer-now', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'transferNowWeb']);
                Route::post('/get-transaction-charges', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'getTransactionCharges']);
            });

            Route::group(['prefix' => 'notification', 'middleware' => 'auth'], function () {
                Route::get('/view/{id}', [App\Http\Controllers\Agent\NotificationController::class, 'view_notification']);
                Route::get('/mark-all-read', [App\Http\Controllers\Agent\NotificationController::class, 'mark_all_read']);
            });

            Route::group(['prefix' => 'developer', 'middleware' => 'auth'], function () {
                Route::get('/settings', [App\Http\Controllers\Agent\DeveloperController::class, 'settings']);
                Route::post('/generate-token-otp', [App\Http\Controllers\Agent\DeveloperController::class, 'generate_token_otp']);
                Route::post('/generate-token-save', [App\Http\Controllers\Agent\DeveloperController::class, 'generate_token_save']);
                Route::post('/add-ipaddress-otp', [App\Http\Controllers\Agent\DeveloperController::class, 'add_ipaddress_otp']);
                Route::post('/ip-address-save', [App\Http\Controllers\Agent\DeveloperController::class, 'ip_address_save']);
                Route::post('/update-call-back-url', [App\Http\Controllers\Agent\DeveloperController::class, 'update_call_back_url']);
                Route::get('/provider-list', [App\Http\Controllers\Agent\DeveloperController::class, 'provider_list']);
                Route::get('/call-back-logs', [App\Http\Controllers\Agent\DeveloperController::class, 'call_back_logs']);
                Route::post('/view-callback-logs', [App\Http\Controllers\Agent\DeveloperController::class, 'view_callback_logs']);
                Route::post('/resend-callback-url', [App\Http\Controllers\Agent\DeveloperController::class, 'resend_callback_url']);
                Route::get('/prepaid-and-dth', [App\Http\Controllers\Agent\DeveloperController::class, 'prepaid_and_dth']);
                Route::get('/bill-payment', [App\Http\Controllers\Agent\DeveloperController::class, 'bill_payment']);
                Route::get('/money-transfer-docs', [App\Http\Controllers\Agent\DeveloperController::class, 'money_transfer_docs']);
                Route::get('/bank-transfer-docs', [App\Http\Controllers\Agent\DeveloperController::class, 'bank_transfer_docs']);
                Route::get('/outlet-list', [App\Http\Controllers\Agent\DeveloperController::class, 'outlet_list']);
                Route::get('/outlet-list-api', [App\Http\Controllers\Agent\DeveloperController::class, 'outlet_list_api']);
                Route::post('/remove-ip-address-otp', [App\Http\Controllers\Agent\DeveloperController::class, 'remove_ip_address_otp']);
                Route::post('/remove-ip-address-save', [App\Http\Controllers\Agent\DeveloperController::class, 'remove_ip_address_save']);
            });

            Route::group(['prefix' => 'pancard', 'middleware' => 'auth'], function () {
                Route::get('/welcome', [App\Http\Controllers\Agent\PancardController::class, 'welcome']);
                Route::post('/buy-coupons', [App\Http\Controllers\Agent\PancardController::class, 'buy_coupons']);
                Route::get('/reports', [App\Http\Controllers\Agent\PancardController::class, 'reports']);
                Route::get('/reports-api', [App\Http\Controllers\Agent\PancardController::class, 'reports_api']);
            });

            Route::group(['prefix' => 'giftcard', 'middleware' => 'auth'], function () {
                Route::get('/amazon-coupons', [App\Http\Controllers\Agent\GiftcardController::class, 'amazon_coupons']);
                Route::post('/purchase-amazon-coupons', [App\Http\Controllers\Agent\GiftcardController::class, 'purchase_amazon_coupons']);
                Route::get('/reports', [App\Http\Controllers\Agent\GiftcardController::class, 'reports']);
                Route::get('/reports-api', [App\Http\Controllers\Agent\GiftcardController::class, 'reports_api']);
            });

            Route::group(['prefix' => 'download/v1', 'middleware' => 'auth'], function () {
                Route::post('/file-download', [App\Http\Controllers\Agent\DownloadController::class, 'download_file']);
            });

            Route::group(['prefix' => 'gst', 'middleware' => 'auth'], function () {
                Route::get('/invoice', [App\Http\Controllers\Agent\InvoiceController::class, 'invoice']);
            });

            Route::group(['prefix' => 'ecommerce', 'middleware' => 'auth'], function () {
                Route::get('/page/{slug}', [App\Http\Controllers\Agent\ShopController::class, 'shop_page']);
                Route::get('/welcome', [App\Http\Controllers\Agent\ShopController::class, 'welcome']);
                Route::get('/product-details/{id}', [App\Http\Controllers\Agent\ShopController::class, 'product_details']);
                Route::post('/add-to-cart', [App\Http\Controllers\Agent\ShopController::class, 'add_to_cart']);
                Route::get('/view-cart', [App\Http\Controllers\Agent\ShopController::class, 'view_cart']);
                Route::post('/delete-product-from-cart', [App\Http\Controllers\Agent\ShopController::class, 'delete_product_from_cart']);
                Route::post('/update-quantity-in-cart', [App\Http\Controllers\Agent\ShopController::class, 'update_quantity_in_cart']);
                Route::post('/save-to-wishlist', [App\Http\Controllers\Agent\ShopController::class, 'save_to_wishlist']);
                Route::get('/my-wishlist', [App\Http\Controllers\Agent\ShopController::class, 'my_wishlist']);
                Route::get('/searchProductAjax', [App\Http\Controllers\Agent\ShopController::class, 'searchProductAjax']);
                Route::get('/search-product', [App\Http\Controllers\Agent\ShopController::class, 'search_product']);

                // Checkout
                Route::get('/checkout', [App\Http\Controllers\Agent\CheckoutController::class, 'checkout']);
                Route::post('/save-delivery-addresses', [App\Http\Controllers\Agent\CheckoutController::class, 'save_delivery_addresses']);
                Route::post('/view-delivery-addresses', [App\Http\Controllers\Agent\CheckoutController::class, 'view_delivery_addresses']);
                Route::post('/update-delivery-addresses', [App\Http\Controllers\Agent\CheckoutController::class, 'update_delivery_addresses']);
                Route::post('/place-order', [App\Http\Controllers\Agent\CheckoutController::class, 'place_order']);

                // My Orders
                Route::get('/my-orders', [App\Http\Controllers\Agent\OrderController::class, 'my_orders']);
                Route::get('/my-orders-api', [App\Http\Controllers\Agent\OrderController::class, 'my_orders_api']);
                Route::post('/view-order-product', [App\Http\Controllers\Agent\OrderController::class, 'view_order_product']);
                Route::post('/view-track-order', [App\Http\Controllers\Agent\OrderController::class, 'view_track_order']);

                // Track Orders
                Route::get('/track-orders', [App\Http\Controllers\Agent\OrderController::class, 'track_orders']);
            });

            Route::group(['prefix' => 'ecommerce-seller', 'middleware' => 'auth'], function () {
                Route::get('/product-list', [App\Http\Controllers\Agent\SellerController::class, 'product_list']);
                Route::get('/product-list-api', [App\Http\Controllers\Agent\SellerController::class, 'product_list_api']);
                Route::get('/add-products', [App\Http\Controllers\Agent\SellerController::class, 'add_products']);
                Route::get('/update-product/{id}', [App\Http\Controllers\Agent\SellerController::class, 'update_product']);
                Route::get('/add-product-image/{id}', [App\Http\Controllers\Agent\SellerController::class, 'add_product_image']);
                Route::get('/my-product', [App\Http\Controllers\Agent\SellerController::class, 'my_product']);

                // Order Request
                Route::get('/order-request', [App\Http\Controllers\Agent\OrderrequestController::class, 'order_request']);
                Route::get('/order-request-api', [App\Http\Controllers\Agent\OrderrequestController::class, 'order_request_api']);
                Route::post('/view-order-product-details', [App\Http\Controllers\Agent\OrderrequestController::class, 'view_order_product_details']);
                Route::post('/view-update-product', [App\Http\Controllers\Agent\OrderrequestController::class, 'view_update_product']);
                Route::post('/update-product-delivery-status', [App\Http\Controllers\Agent\OrderrequestController::class, 'update_product_delivery_status']);
            });

            Route::group(['prefix' => 'add-money/v1', 'middleware' => 'auth'], function () {
                Route::get('/welcome', [App\Http\Controllers\Agent\CashfreeController::class, 'welcome']);
                Route::post('/create-order', [App\Http\Controllers\Agent\CashfreeController::class, 'create_order']);
                Route::get('/return-url', [App\Http\Controllers\Agent\CashfreeController::class, 'return_url']);
            });

            Route::group(['prefix' => 'referral', 'middleware' => 'auth'], function () {
                Route::get('/refer-and-earn', [App\Http\Controllers\Agent\ReferralController::class, 'welcome']);
            });

            Route::group(['prefix' => 'upi-transfer/v1', 'middleware' => 'auth'], function () {
                Route::get('/welcome', [App\Http\Controllers\Agent\UpitransferController::class, 'welcome']);
                Route::post('/getUpiextensions', [App\Http\Controllers\Agent\UpitransferController::class, 'getUpiextensions']);
                Route::post('/fatch-name', [App\Http\Controllers\Agent\UpitransferController::class, 'fatchNameWeb']);
                Route::post('/view-transaction', [App\Http\Controllers\Agent\UpitransferController::class, 'viewTransaction']);
            });

            //paysprint airtel cms
            Route::group(['prefix' => 'airtel-cms/v1', 'middleware' => 'auth'], function () {
                Route::get('/welcome', [App\Http\Controllers\Agent\AirtelCmsController::class, 'welcome']);
                Route::post('/generate-url', [App\Http\Controllers\Agent\AirtelCmsController::class, 'generateUrl']);
            });

            //paysprint airtel cms
            Route::group(['prefix' => 'recharge/v1', 'middleware' => 'auth'], function () {
                Route::get('/welcome', [App\Http\Controllers\Agent\Recharge1Controller::class, 'welcome']);
                Route::post('/create', [App\Http\Controllers\Agent\Recharge1Controller::class, 'create']);
                Route::post('/get-plans', [App\Http\Controllers\Agent\Recharge1Controller::class, 'getPlans']);
            });

            Route::group(['prefix' => 'recharge-2/v1', 'middleware' => 'auth'], function () {
                Route::get('/welcome', [App\Http\Controllers\Agent\Recharge2Controller::class, 'welcome']);
                Route::post('/store-recharge', [App\Http\Controllers\Agent\Recharge2Controller::class, 'storeRecharge']);
            });

            Route::group(['prefix' => 'gift-card/v1', 'middleware' => 'auth'], function () {
                Route::get('/welcome', [App\Http\Controllers\Agent\GiftCardController::class, 'welcome']);
                Route::post('/check_balance', [App\Http\Controllers\Agent\GiftCardController::class, 'checkBalance']);
                Route::post('/get-vouchers', [App\Http\Controllers\Agent\GiftCardController::class, 'getVouchers']);
                Route::get('/voucher-history', [App\Http\Controllers\Agent\GiftCardController::class, 'voucherHistory']);
                Route::get('/voucher-history-api', [App\Http\Controllers\Agent\GiftCardController::class, 'ajaxVoucherHistory']);
                Route::post('/view-voucher-details', [App\Http\Controllers\Agent\GiftCardController::class, 'ajaxVoucherDetails']);
            });

            Route::group(['prefix' => 'mnp/v1', 'middleware' => 'auth'], function () {
                Route::get('/welcome', [App\Http\Controllers\Agent\MnpController::class, 'welcome']);
                Route::post('/store-mnp', [App\Http\Controllers\Agent\MnpController::class, 'storeMnp']);
                Route::get('/get-mnp-balance', [App\Http\Controllers\Agent\MnpController::class, 'getMnpBalance']);
            });

            Route::group(['prefix' => 'bbps/v1', 'middleware' => 'auth'], function () {
                Route::get('/welcome', [App\Http\Controllers\Agent\BbpsController::class, 'welcome']);
                Route::get('/get-service-detail', [App\Http\Controllers\Agent\BbpsController::class, 'getServiceDetail']);
            });
            Route::get('/white-label-add', [App\Http\Controllers\Agent\PaymentrequestController::class, 'addWhiteLabel']);
            Route::post('/white-label-add', [App\Http\Controllers\Agent\PaymentrequestController::class, 'storeWhiteLabel']);

            Route::get('/white-label-edit/{id}', [App\Http\Controllers\Agent\PaymentrequestController::class, 'editWhiteLabel']);
            Route::post('/white-label-edit', [App\Http\Controllers\Agent\PaymentrequestController::class, 'updateWhiteLabel']);

            Route::get('/white-label-delete/{id}', [App\Http\Controllers\Agent\PaymentrequestController::class, 'deleteWhiteLabel']);
            Route::post('/white-label-delete', [App\Http\Controllers\Agent\PaymentrequestController::class, 'destroyBank']);

            Route::get('/virtual-account-static-qr', [App\Http\Controllers\Agent\VirtualAccountStaticQRController::class, 'index']);
            Route::get('/virtual-account-static-qr/all-data-api', [App\Http\Controllers\Agent\VirtualAccountStaticQRController::class, 'all_data_api']);
            Route::post('/virtual-account/status-change', [App\Http\Controllers\Agent\VirtualAccountStaticQRController::class, 'statusChange']);
        });
    });
});
Route::post('agent/iserveu-aeps-callback', [App\Http\Controllers\Agent\PaysprintAepsController::class, 'iserveuAepsCallback']);
Route::get('/password/expired', [App\Http\Controllers\Auth\ExpiredPasswordController::class, 'expired'])->name('password.expired');
Route::post('/password/post_expired', [App\Http\Controllers\Auth\ExpiredPasswordController::class, 'postExpired'])->name('password.post_expired');

Route::get('iserveu-payout-status-test', function (\Illuminate\Http\Request $request) {
    $payout = new \App\IServeU\Payout();
    $ref_id = $request->ref_id;
    $sdate = $request->sdate;
    $edate = $request->edate;
    $res = $payout->transactionStatusCheck($ref_id, $request->tran_id, $sdate, $edate);
    pre($res);
});
Route::get('iserveu-aeps-status-test', function (\Illuminate\Http\Request $request) {
    $payout = new \App\IServeU\Payout();
    $ref_id = $request->ref_id;
    $sdate = $request->sdate;
    $edate = $request->edate;
    $res = $payout->aepsTransactionStatusCheck($ref_id, $sdate, $edate);
    pre($res);
});

Route::get('test-api', function (\Illuminate\Http\Request $request) {
    $password = '87B7072E43|TEST|EBBE337D50';
    echo  $hashed = hash("sha512", $password);

    exit;
    $url = "";
    // Data to send in JSON format
    $CorporateNumber = "";
    $MobileNumber = "";
    $Provider = "Vi";
    $reference = \Str::random(20);
    $amount = "15";
    // Ensure the reference is alphanumeric and does not include special characters
    $reference = preg_replace('/[^a-zA-Z0-9]/', '', $reference);

    // If the length exceeds, truncate it
    $SystemReference = ""; // substr($reference, 0, 20);
    $shaSecretKey = "";
    $dataToHash = $CorporateNumber . $MobileNumber . $SystemReference . $shaSecretKey;

    // $dataToHash = $CorporateNumber . "Saikat123@" . $shaSecretKey;
    // Generate the SHA-256 hash and convert it to lowercase
    //    echo $APIChecksum = hash('sha256', $dataToHash);
    // exit;

    $url = "";
    $data = array(
        "CorporateNumber" => "",
        "Password" => "",
    );
    $response = \Http::withHeaders(["content-type" => "application/json"])->post($url, $data)->json();
    print_r($response);
});

Route::get('send-test-mail', function (\Illuminate\Http\Request $request) {
    try {
        \Mail::raw('Hi, test email!', function ($message) {
            $message->to("anil.mathukiya@payomatix.com")
                ->subject("this is test email");
        });
        echo 'Test mail send successfully.';
        //code...
    } catch (\Exception $th) {
        echo $th->getMessage();
    }
});

Route::get('api-test', function () {
    $aeps = new \App\Bankit\Aeps();
    $res = $aeps->generateToken(50, 'ICICI');
    dd($res);
});

Route::get('payout-status-api-test', function (\Illuminate\Http\Request $request) {
    $payout = new \App\Paysprint\Payout();
    $ref_id = $request->ref_id;
    $res = $payout->transactionStatusCheck($ref_id, $request->tran_id);
    dd($res);
});

Route::get('token', function () {
    $data = '{ "client_id": "","client_secret": "","epoch": ' . time() . '}';
    $key = '';
    // Generate a random IV
    $iv = openssl_random_pseudo_bytes(16);
    // Decode the key from base64
    $decodedKey = base64_decode($key);
    // Encrypt the data using AES CBC mode and PKCS7 padding
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $decodedKey, OPENSSL_RAW_DATA, $iv);
    // Combine the IV and the encrypted data
    $combined = $iv . $encrypted;
    // Encode the result as base64
    echo base64_encode($combined);
});




Route::get('iserveu-aeps-test-api', function () {

    if (!auth()->check()) {
        return redirect('/login');
    }

    $data['page_title'] =  'Aadhaar Enabled Payment System (AePS)';


    $ref_id = tempgenerateReferenceIDT();
    $token = tempgenerateIserveuToken();

    $data['pass_key'] = env('ISU_PASS_KEY');
    $data['token'] = $token;
    $data['api_username'] = env('ISU_API_USERNAME');
    $data['username'] = auth()->user()->cms_agent_id;
    $data['ref_id'] = $ref_id;
    $data['is_receipt'] = 'true';
    $data['callback_url'] = url('iserveu-aeps-callback');


    return view('agent.aeps.iserveu', $data);
});

Route::get('payout-test-api', function () {
    $clint_id = "";
    $clint_secret = "";
    $token_key = "";
    $pass_key = "";
    $api_username = "";
    $username = "";

    $data = '{ "client_id": "' . $clint_id . '","client_secret": "' . $clint_secret . '","epoch": "' . time() . '"}';

    $iv = openssl_random_pseudo_bytes(16);
    $decodedKey = base64_decode($token_key);
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $decodedKey, OPENSSL_RAW_DATA, $iv);
    $combined = $iv . $encrypted;
    echo $token = base64_encode($combined);
});


Route::any('iserveu-aeps-callback', function (\Illuminate\Http\Request $request) {
    dd($request->all());
});
