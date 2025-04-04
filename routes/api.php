<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// payout api
Route::post('/test-mode/payout/create', [App\Http\Controllers\Api\TestPayoutController::class, 'store']);
Route::post('/prod-mode/payout/create', [App\Http\Controllers\Api\PayoutController::class, 'store']);

Route::post('/test-mode/payout/status', [App\Http\Controllers\Api\TestPayoutController::class, 'status']);
Route::post('/prod-mode/payout/status', [App\Http\Controllers\Api\PayoutController::class, 'status']);

Route::post('/stage/payout/create', [App\Http\Controllers\Api\TestPayoutController::class, 'store']);
Route::post('/production/payout/create', [App\Http\Controllers\Api\PayoutRetailorController::class, 'store']);

// dynamic qr payin api
Route::post('/prod-mod/payin-static-qr/create', [App\Http\Controllers\Api\PayinStaticQrController::class, 'store']);
Route::post('/prod-mod/payin-static-qr/get', [App\Http\Controllers\Api\PayinStaticQrController::class, 'get']);

// dynamic qr payin api
Route::post('/test-mode/payin-dynamic-qr/create', [App\Http\Controllers\Api\TestPayinDynamicQRController::class, 'store']);
Route::post('/prod-mode/payin-dynamic-qr/create', [App\Http\Controllers\Api\PayinDynamicQRController::class, 'store']);

Route::post('/test-mode/payin-dynamic-qr/status', [App\Http\Controllers\Api\TestPayinDynamicQRController::class, 'status']);
Route::post('/prod-mode/payin-dynamic-qr/status', [App\Http\Controllers\Api\PayinDynamicQRController::class, 'status']);


Route::prefix('call-back')->group(function () {
    Route::post('/merchant-pay2all', [App\Http\Controllers\Agent\RefundController::class, 'merchant_pay2all']);
    Route::post('/smart-outlet', [App\Http\Controllers\Agent\SmartoutletController::class, 'smart_outlet']);
    Route::get('/recharge-response/{id}', [App\Http\Controllers\Agent\RefundController::class, 'dynamic_response']);
    Route::post('/recharge-response/{id}', [App\Http\Controllers\Agent\RefundController::class, 'dynamic_response']);
    Route::post('/cashfree-gateway', [App\Http\Controllers\Agent\CashfreeController::class, 'cashfree_callback']);
    Route::post('/paysprint-webhook', [App\Http\Controllers\Agent\PaysprintWebhookController::class, 'webhookRequest']);
    Route::get('/recharge2-callback', [App\Http\Controllers\Agent\Recharge2Controller::class, 'webhookCallback']);
    Route::post('/bankit-cms-pre-webhook', [App\Http\Controllers\Agent\BankitCmsWebhookController::class, 'webhookPreRequest']);
    Route::post('/bankit-cms-post-webhook', [App\Http\Controllers\Agent\BankitCmsWebhookController::class, 'webhookPostRequest']);

    Route::post('/bankit-aeps-webhook', [\App\Http\Controllers\Agent\BankitAepsWebhookController::class, 'webhookPostRequest']);
    Route::post('/bankit-dmt-callback', [App\Http\Controllers\Agent\BankitDmtCallbackController::class, 'callBackRequest']);
    Route::post('/iServeU-dmt-callback', [App\Http\Controllers\Agent\iServeUDmtCallbackController::class, 'callBackRequest']);
    Route::post('/iserveu-payout-callback', [App\Http\Controllers\Agent\IServeUPayoutCallbackController::class, 'payoutCallBackRequest']);
    Route::post('/iserveu-aeps-webhook', [\App\Http\Controllers\Agent\IserveUWebhookController::class, 'iserveuAepsWebhook']);
    Route::post('/easebuzz-payin-webhook', [App\Http\Controllers\Agent\EasebuzzWebhookController::class, 'webhookPostRequest']);
});

Route::post('/asvalidate', [App\Http\Controllers\Agent\AxisController::class, 'asValidate']);
Route::post('/astransaction', [App\Http\Controllers\Agent\AxisController::class, 'asTransaction']);
Route::post('/cdmastransaction', [App\Http\Controllers\Agent\AxisController::class, 'cdmAsTransaction']);
Route::post('/easypay-validate', [App\Http\Controllers\Agent\AxisController::class, 'easyPayValidate']);
Route::post('/easypay-transaction', [App\Http\Controllers\Agent\AxisController::class, 'easyPayTransaction']);

Route::prefix('application/v1')->group(function () {
    Route::post('/login', [App\Http\Controllers\ApplicationController::class, 'login']);
    // Route::post('/api-login', [App\Http\Controllers\ApplicationController::class, 'apiLogin']);
    Route::post('/resend-login-otp', [App\Http\Controllers\Auth\LoginController::class, 'resend_login_otp_app']);
    Route::post('/validate-login', [App\Http\Controllers\ApplicationController::class, 'validate_login']);
    Route::post('/check-balance', [App\Http\Controllers\ApplicationController::class, 'check_balance'])->middleware('auth:api');
    Route::post('/state-list', [App\Http\Controllers\ApplicationController::class, 'state_list']);
    Route::post('/change-password', [App\Http\Controllers\ApplicationController::class, 'change_password'])->middleware('auth:api');
    Route::post('/update-profile', [App\Http\Controllers\ApplicationController::class, 'update_profile'])->middleware('auth:api');
    Route::post('/verify-mobile', [App\Http\Controllers\ApplicationController::class, 'verify_mobile'])->middleware('auth:api');
    Route::post('/confirm-verify-mobile', [App\Http\Controllers\ApplicationController::class, 'confirm_verify_mobile'])->middleware('auth:api');
    Route::post('/notification/mark-all-read', [App\Http\Controllers\ApplicationController::class, 'mark_all_read'])->middleware('auth:api');
    Route::post('/notification/read-notification', [App\Http\Controllers\ApplicationController::class, 'read_notification'])->middleware('auth:api');
    Route::post('/company-contact-details', [App\Http\Controllers\ApplicationController::class, 'company_contact_details'])->middleware('auth:api');
    Route::get('/ekyc-update', [App\Http\Controllers\ApplicationController::class, 'ekyc_update'])->middleware('auth:api');

    Route::post('/change-transaction-password', [App\Http\Controllers\ApplicationController::class, 'changeTransactionPassword'])->middleware('auth:api');
    // kyc
    Route::post('/update-profile-photo', [App\Http\Controllers\ApplicationController::class, 'update_profile_photo'])->middleware('auth:api');
    Route::post('/update-shop-photo', [App\Http\Controllers\ApplicationController::class, 'update_shop_photo'])->middleware('auth:api');
    Route::post('/update-gst-regisration-photo', [App\Http\Controllers\ApplicationController::class, 'update_gst_regisration_photo'])->middleware('auth:api');
    Route::post('/update-pancard-photo', [App\Http\Controllers\ApplicationController::class, 'update_pancard_photo'])->middleware('auth:api');
    Route::post('/update-cancel-cheque-photo', [App\Http\Controllers\ApplicationController::class, 'cancel_cheque_photo'])->middleware('auth:api');
    Route::post('/update-address-proof-photo', [App\Http\Controllers\ApplicationController::class, 'address_proof_photo'])->middleware('auth:api');
    Route::post('/update-aadhar-front-photo', [App\Http\Controllers\ApplicationController::class, 'update_aadhar_front_photo'])->middleware('auth:api');
    Route::post('/update-aadhar-back-photo', [App\Http\Controllers\ApplicationController::class, 'update_aadhar_back_photo'])->middleware('auth:api');
    Route::post('/update-agreement-form-doc', [App\Http\Controllers\ApplicationController::class, 'update_agreement_form_doc'])->middleware('auth:api');

    Route::post('/get-service', [App\Http\Controllers\ApplicationController::class, 'get_service'])->middleware('auth:api');
    Route::post('/get-provider', [App\Http\Controllers\ApplicationController::class, 'get_provider'])->middleware('auth:api');
    Route::post('/provider-validation', [App\Http\Controllers\Agent\RechargeController::class, 'check_provider_validation'])->middleware('auth:api');
    Route::post('/bill-verify', [App\Http\Controllers\Agent\RechargeController::class, 'bbps_bill_verify_app'])->middleware('auth:api');
    Route::post('/recharge-now', [App\Http\Controllers\Agent\RechargeController::class, 'app_recharge_now'])->middleware('auth:api');
    Route::post('/aeps-outlet-id', [App\Http\Controllers\ApplicationController::class, 'aeps_outlet_id'])->middleware('auth:api');
    Route::post('/sign-up', [App\Http\Controllers\Auth\SignupController::class, 'register_now']);
    Route::post('/forgot-password-otp', [App\Http\Controllers\Auth\LoginController::class, 'forgot_password_otp']);
    Route::post('/confirm-forgot-password', [App\Http\Controllers\Auth\LoginController::class, 'confirm_forgot_password']);
    Route::post('/agent-onboarding', [App\Http\Controllers\Agent\AepsController::class, 'save_agent_onboarding'])->middleware('auth:api');
    Route::get('/page-content', [App\Http\Controllers\ApplicationController::class, 'page_content']);

    // common list
    Route::post('/common-list', [App\Http\Controllers\ApplicationController::class, 'commonList']);
    Route::post('/send-transaction-pin-otp', [App\Http\Controllers\Admin\ProfileController::class, 'send_transaction_pin_otp'])->middleware('auth:api');
    Route::post('/create-transaction-pin', [App\Http\Controllers\Admin\ProfileController::class, 'create_transaction_pin'])->middleware('auth:api');

    // delete-account
    Route::post('/delete-account', [App\Http\Controllers\ApplicationController::class, 'delete_account'])->middleware('auth:api');
});

Route::prefix('dmt/v1')->group(function () {
    Route::post('/bank-list', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'bank_list'])->middleware('auth:api');
    Route::post('/get-customer', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'getCustomer'])->middleware('auth:api');
    Route::post('/add-sender', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'addSender'])->middleware('auth:api');
    Route::post('/confirm-sender', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'confirmSender'])->middleware('auth:api');
    Route::post('/sender-resend-otp', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'senderResendOtp'])->middleware('auth:api');
    Route::post('/get-all-beneficiary', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'getAllBeneficiary'])->middleware('auth:api');
    Route::post('/search-by-account', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'searchByAccount'])->middleware('auth:api');
    Route::post('/add-beneficiary', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'addBeneficiary'])->middleware('auth:api');
    Route::post('/confirm-beneficiary', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'confirmBeneficiary'])->middleware('auth:api');
    Route::post('/delete-beneficiary', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'deleteBeneficiary'])->middleware('auth:api');
    Route::post('/confirm-delete-beneficiary', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'confirmDeleteBeneficiary'])->middleware('auth:api');
    Route::post('/account-verify', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'accountVerifyApp'])->middleware('auth:api');
    Route::post('/transfer-now', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'transferNowApp'])->middleware('auth:api');
    Route::post('/get-transaction-charges', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'getTransactionCharges'])->middleware('auth:api');
});
Route::prefix('dmt-iservu/v1')->group(function () {
    Route::post('/bank-list-iServeU', [App\Http\Controllers\Agent\iServeUDmtController::class, 'bank_list_iServeU'])->middleware('auth:api');
    Route::post('/get-iServeU-customer', [App\Http\Controllers\Agent\iServeUDmtController::class, 'getCustomer'])->middleware('auth:api');
    Route::post('/get-iServeU-beneficiary', [App\Http\Controllers\Agent\iServeUDmtController::class, 'getAllBeneficiaryBankIt'])->middleware('auth:api');
    Route::post('/account-verify-iserveU', [App\Http\Controllers\Agent\iServeUDmtController::class, 'accountVerify'])->middleware('auth:api');
    Route::post('/get-ifsc-code-iserveU', [App\Http\Controllers\Agent\iServeUDmtController::class, 'getIfscCode'])->middleware('auth:api');
    Route::post('/delete-beneficiary-iserveU', [App\Http\Controllers\Agent\iServeUDmtController::class, 'deleteBeneficiary'])->middleware('auth:api');
    Route::post('/view-account-transfer-iserveU', [App\Http\Controllers\Agent\iServeUDmtController::class, 'viewAccountTransfer'])->middleware('auth:api');
    Route::post('/transfer-now-iserveU', [App\Http\Controllers\Agent\iServeUDmtController::class, 'transferNowApp'])->middleware('auth:api');
    Route::post('/confirm-sender-iServeU', [App\Http\Controllers\Agent\iServeUDmtController::class, 'confirmSender'])->middleware('auth:api');
    Route::post('/iserveU-resend-otp', [App\Http\Controllers\Agent\iServeUDmtController::class, 'resendOtp'])->middleware('auth:api');
    Route::post('/iserveU-send-otp', [App\Http\Controllers\Agent\iServeUDmtController::class, 'sendOtp'])->middleware('auth:api');
});

Route::prefix('dmt/v2')->group(function () {
    Route::post('/bank-list', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'bank_list'])->middleware('auth:api');
    Route::post('/get-customer', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'getCustomer'])->middleware('auth:api');
    Route::post('/add-sender', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'addSender'])->middleware('auth:api');
    Route::post('/confirm-sender', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'confirmSender'])->middleware('auth:api');
    Route::post('/sender-resend-otp', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'senderResendOtp'])->middleware('auth:api');
    Route::post('/get-all-beneficiary', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'getAllBeneficiary'])->middleware('auth:api');
    Route::post('/search-by-account', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'searchByAccount'])->middleware('auth:api');
    Route::post('/add-beneficiary', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'addBeneficiary'])->middleware('auth:api');
    Route::post('/confirm-beneficiary', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'confirmBeneficiary'])->middleware('auth:api');
    Route::post('/delete-beneficiary', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'deleteBeneficiary'])->middleware('auth:api');
    Route::post('/confirm-delete-beneficiary', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'confirmDeleteBeneficiary'])->middleware('auth:api');
    Route::post('/account-verify', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'accountVerifyApp'])->middleware('auth:api');
    Route::post('/transfer-now', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'transferNowApp'])->middleware('auth:api');
    Route::post('/get-transaction-charges', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'getTransactionCharges'])->middleware('auth:api');
});
Route::prefix('recharge2/v1')->group(function () {
    Route::post('/recharge-provider', [App\Http\Controllers\Agent\Recharge2Controller::class, 'providerList'])->middleware('auth:api');
    Route::post('/store-recharge', [App\Http\Controllers\Agent\Recharge2Controller::class, 'storeRecharge'])->middleware('auth:api');
});

Route::prefix('mnp/v1')->group(function () {

    Route::post('/store-mnp', [App\Http\Controllers\Agent\MnpController::class, 'storeMnp'])->middleware('auth:api');
    Route::get('/get-mnp-balance', [App\Http\Controllers\Agent\MnpController::class, 'getMnpBalance'])->middleware('auth:api');
});

Route::prefix('reports/v1')->group(function () {
    Route::get('/all-transaction-report', [App\Http\Controllers\ReportController::class, 'all_transaction_report'])->middleware('auth:api');
    Route::get('/ledger-report', [App\Http\Controllers\ReportController::class, 'ledger_report'])->middleware('auth:api');
    Route::get('/welcome/{report_slug}', [App\Http\Controllers\ReportController::class, 'welcome'])->middleware('auth:api');
    Route::get('/operator-report', [App\Http\Controllers\ReportController::class, 'operator_report'])->middleware('auth:api');
    Route::get('/income-report', [App\Http\Controllers\ReportController::class, 'income_report'])->middleware('auth:api');
});

Route::prefix('fund-request')->group(function () {
    Route::get('/bank-list', [App\Http\Controllers\ApplicationController::class, 'fund_request_bank_list'])->middleware('auth:api');
    Route::get('/payment-method', [App\Http\Controllers\ApplicationController::class, 'payment_method'])->middleware('auth:api');
    Route::post('/payment-request-now', [App\Http\Controllers\ApplicationController::class, 'payment_request_now'])->middleware('auth:api');
    Route::get('/request-report', [App\Http\Controllers\ApplicationController::class, 'fund_request_report'])->middleware('auth:api');
});

Route::prefix('commission')->group(function () {
    Route::get('/service-list', [App\Http\Controllers\ApplicationController::class, 'commission_service_list'])->middleware('auth:api');
    Route::get('/providers', [App\Http\Controllers\ApplicationController::class, 'commission_providers'])->middleware('auth:api');
    Route::get('/my-commission', [App\Http\Controllers\ApplicationController::class, 'my_commission'])->middleware('auth:api');
});

Route::prefix('settlement')->group(function () {
    Route::post('/move-to-wallet', [App\Http\Controllers\Agent\PayoutController::class, 'move_to_wallet_app'])->middleware('auth:api');
    Route::post('/beneficiary-list', [App\Http\Controllers\Agent\PayoutController::class, 'beneficiary_list'])->middleware('auth:api');
    Route::post('/account-validate', [App\Http\Controllers\Agent\PayoutController::class, 'account_validate_app'])->middleware('auth:api');
    Route::post('/add-beneficiary', [App\Http\Controllers\Agent\PayoutController::class, 'add_beneficiary'])->middleware('auth:api');
    Route::post('/delete-beneficiary', [App\Http\Controllers\Agent\PayoutController::class, 'delete_beneficiary'])->middleware('auth:api');
    Route::post('/transfer-now', [App\Http\Controllers\Agent\PayoutController::class, 'transfer_now_app'])->middleware('auth:api');
});

Route::prefix('pancard')->group(function () {
    Route::post('/purchase-coupons', [App\Http\Controllers\Agent\PancardController::class, 'buy_coupons_app'])->middleware('auth:api');
});

Route::prefix('wallet')->group(function () {
    Route::post('/verify-user', [App\Http\Controllers\Agent\WalletController::class, 'verify_user'])->middleware('auth:api');
    Route::post('/transfer-now', [App\Http\Controllers\Agent\WalletController::class, 'transfer_now'])->middleware('auth:api');
});

Route::prefix('dispute')->group(function () {
    Route::post('/reason', [App\Http\Controllers\Agent\DisputeController::class, 'reason_application'])->middleware('auth:api');
    Route::post('/save-dispute', [App\Http\Controllers\Agent\DisputeController::class, 'dispute_transaction'])->middleware('auth:api');
    Route::post('/view-dispute-details', [App\Http\Controllers\Agent\DisputeController::class, 'view_dispute_conversation'])->middleware('auth:api');
    Route::post('/pending-dispute', [App\Http\Controllers\Agent\DisputeController::class, 'pending_dispute_app'])->middleware('auth:api');
    Route::post('/solve-dispute', [App\Http\Controllers\Agent\DisputeController::class, 'solve_dispute_app'])->middleware('auth:api');
    Route::post('/view-conversation', [App\Http\Controllers\Agent\DisputeController::class, 'view_conversation_application'])->middleware('auth:api');
    Route::post('/send-chat-message', [App\Http\Controllers\Agent\DisputeController::class, 'send_chat_message'])->middleware('auth:api');
});

Route::prefix('telecom/v1')->group(function () {
    Route::get('/payment', [App\Http\Controllers\Agent\RechargeController::class, 'api_recharge_now'])->middleware('auth:api');
    Route::get('/check-balance', [App\Http\Controllers\Agent\RechargeController::class, 'check_balance_api'])->middleware('auth:api');
    Route::get('/check-status', [App\Http\Controllers\Agent\RechargeController::class, 'check_status_api'])->middleware('auth:api');
    Route::post('/bill-verify', [App\Http\Controllers\Agent\RechargeController::class, 'bbps_bill_verify_api'])->middleware('auth:api');
    Route::post('/provider-validation', [App\Http\Controllers\Agent\RechargeController::class, 'check_provider_validation'])->middleware('auth:api');
});

Route::prefix('plans/v1')->group(function () {
    Route::get('/type', [App\Http\Controllers\Agent\PlanController::class, 'plan_type'])->middleware('auth:api');
    Route::get('/prepaid-plans', [App\Http\Controllers\Agent\PlanController::class, 'prepaid_plan'])->middleware('auth:api');
    Route::get('/roffer-plan', [App\Http\Controllers\Agent\PlanController::class, 'roffer_plan'])->middleware('auth:api');
    Route::get('/dth-plan', [App\Http\Controllers\Agent\PlanController::class, 'dth_plan'])->middleware('auth:api');
    Route::get('/dth-customer-info', [App\Http\Controllers\Agent\PlanController::class, 'dth_customer_info'])->middleware('auth:api');
    Route::get('/dth-refresh', [App\Http\Controllers\Agent\PlanController::class, 'dth_refresh'])->middleware('auth:api');
    Route::get('/dth-roffer-plan', [App\Http\Controllers\Agent\PlanController::class, 'dth_roffer'])->middleware('auth:api');
    Route::get('/prepaid-auto-find', [App\Http\Controllers\Agent\PlanController::class, 'find_operator'])->middleware('auth:api');
    Route::get('/dth-info-by-mobile', [App\Http\Controllers\Agent\PlanController::class, 'dth_info_by_mobile'])->middleware('auth:api');
});

Route::prefix('admin')->group(function () {
    Route::post('/get-roles', [App\Http\Controllers\ApplicationController::class, 'get_roles'])->middleware('auth:api');
    Route::post('/add-members', [App\Http\Controllers\Admin\MemberController::class, 'store_members'])->middleware('auth:api');
    Route::post('/get-users', [App\Http\Controllers\ApplicationController::class, 'get_users'])->middleware('auth:api');
    Route::post('/balance-transfer', [App\Http\Controllers\Admin\TrasnferController::class, 'balance_trasnfer_application'])->middleware('auth:api');
});

Route::prefix('aeps/v1')->group(function () {
    Route::post('/agent-onboarding', [App\Http\Controllers\Agent\AepsController::class, 'agent_onboarding_api'])->middleware('auth:api');
    Route::get('/aeps-landing', [App\Http\Controllers\Agent\AepsController::class, 'aeps_landing_api'])->middleware('auth:api');
    Route::get('/aeps-outlet-id', [App\Http\Controllers\Agent\AepsController::class, 'aeps_outlet_id'])->middleware('auth:api');
});

Route::prefix('aeps/v2')->group(function () {
    Route::post('/bank-list', [App\Http\Controllers\Agent\PaysprintAepsController::class, 'bankList'])->middleware('auth:api');
    Route::post('/agent-onboarding', [App\Http\Controllers\Agent\PaysprintAepsController::class, 'agentOnboardingApp'])->middleware('auth:api');
    Route::post('/two-factor-authentication', [App\Http\Controllers\Agent\PaysprintAepsController::class, 'twoFactorAuthenticationApp'])->middleware('auth:api');
    Route::post('/transaction', [App\Http\Controllers\Agent\PaysprintAepsController::class, 'initiate_transaction_app'])->middleware('auth:api');

});

Route::prefix('ecommerce/v1')->group(function () {
    Route::get('/banners', [App\Http\Controllers\EcommerceController::class, 'banners']);
    Route::get('/get-category', [App\Http\Controllers\EcommerceController::class, 'get_category']);
    Route::get('/home-page-product', [App\Http\Controllers\EcommerceController::class, 'home_page_product']);
    Route::get('/product-by-category', [App\Http\Controllers\EcommerceController::class, 'product_by_category']);
    Route::get('/search-product', [App\Http\Controllers\EcommerceController::class, 'search_product']);
    // add to cart
    Route::post('add-to-cart', [App\Http\Controllers\EcommerceController::class, 'add_to_cart'])->middleware('auth:api');
    Route::post('view-cart-item', [App\Http\Controllers\EcommerceController::class, 'view_cart_item'])->middleware('auth:api');
    Route::post('delete-cart-item', [App\Http\Controllers\EcommerceController::class, 'delete_cart_item'])->middleware('auth:api');
    Route::post('update-cart-item', [App\Http\Controllers\EcommerceController::class, 'update_cart_item'])->middleware('auth:api');
    // add to wishlist
    Route::post('add-to-wishlist', [App\Http\Controllers\EcommerceController::class, 'add_to_wishlist'])->middleware('auth:api');
    Route::post('my-wishlist', [App\Http\Controllers\EcommerceController::class, 'my_wishlist'])->middleware('auth:api');
    // shipping address
    Route::post('save-delivery-addresses', [App\Http\Controllers\EcommerceController::class, 'save_delivery_addresses'])->middleware('auth:api');
    Route::post('my-delivery-addresses', [App\Http\Controllers\EcommerceController::class, 'my_delivery_addresses'])->middleware('auth:api');
    Route::post('update-delivery-addresses', [App\Http\Controllers\EcommerceController::class, 'update_delivery_addresses'])->middleware('auth:api');
    // buy product
    Route::post('payment-methods', [App\Http\Controllers\EcommerceController::class, 'payment_method'])->middleware('auth:api');
    Route::post('confirm-order', [App\Http\Controllers\EcommerceController::class, 'confirm_order'])->middleware('auth:api');
    // reports
    Route::post('order-report', [App\Http\Controllers\EcommerceController::class, 'order_report'])->middleware('auth:api');
});

Route::prefix('add-money/v1')->group(function () {
    Route::post('/create-order', [App\Http\Controllers\Agent\CashfreeController::class, 'createOrderApp'])->middleware('auth:api');
});

Route::prefix('matm/v1')->group(function () {
    Route::post('/merchant-details', [App\Http\Controllers\Agent\PaysprintmatmController::class, 'merchantDetails'])->middleware('auth:api');
});

Route::prefix('airtel-cms/v1')->group(function () {
    Route::post('/generate-url', [App\Http\Controllers\Agent\AirtelCmsController::class, 'generateUrl'])->middleware('auth:api');
});

Route::prefix('cash-deposit/v1')->group(function () {
    Route::post('/bank-list', [App\Http\Controllers\Agent\CashDepositController::class, 'bankList'])->middleware('auth:api');
    Route::post('/initiate', [App\Http\Controllers\Agent\CashDepositController::class, 'initiateApp'])->middleware('auth:api');
});

Route::prefix('referral')->group(function () {
    Route::get('/details', [App\Http\Controllers\Agent\ReferralController::class, 'details_app'])->middleware('auth:api');
});

Route::prefix('payout-iservu/v1')->group(function () {
    Route::post('/add-beneficiary', [App\Http\Controllers\Agent\AepsPayoutController::class, 'addAccountIserveU'])->middleware('auth:api');
    Route::post('/beneficiary-list', [App\Http\Controllers\Agent\AepsPayoutController::class, 'listAccountIserveU'])->middleware('auth:api');
    Route::post('/payout-list', [App\Http\Controllers\Agent\AepsPayoutController::class, 'listPayoutRequest'])->middleware('auth:api');
    Route::post('/delete-beneficiary', [App\Http\Controllers\Agent\AepsPayoutController::class, 'deleteAccountIserveU'])->middleware('auth:api');
    Route::post('/transfer-now', [App\Http\Controllers\Agent\AepsPayoutController::class, 'sendPayoutRequestIserveU'])->middleware('auth:api');
});

 //paysprint airtel cms
 Route::group(['prefix' => 'recharge/v1'], function () {
    //Route::get('/welcome', [App\Http\Controllers\Agent\Recharge1Controller::class, 'welcome']);
    Route::post('/create', [App\Http\Controllers\Agent\Recharge1Controller::class, 'create'])->middleware('auth:api');
    Route::post('/get-plans', [App\Http\Controllers\Agent\Recharge1Controller::class, 'getPlans']);
});

Route::group(['prefix' => 'gift-card/v1'], function () {
    Route::get('/gift-card/list', [App\Http\Controllers\Agent\GiftCardController::class, 'giftCardList'])->middleware('auth:api');
    Route::post('/get-vouchers', [App\Http\Controllers\Agent\GiftCardController::class, 'getVouchers'])->middleware('auth:api');
    Route::get('/voucher-history', [App\Http\Controllers\Agent\GiftCardController::class, 'voucherHistoryList'])->middleware('auth:api');
    Route::post('/view-voucher-details', [App\Http\Controllers\Agent\GiftCardController::class, 'voucherHistoryDetailApi'])->middleware('auth:api');
});

Route::prefix('payout-paysprint/v1')->group(function () {
    Route::post('/add-beneficiary', [App\Http\Controllers\Agent\AepsPayoutController::class, 'addAccount'])->middleware('auth:api');
    Route::post('/beneficiary-list', [App\Http\Controllers\Agent\AepsPayoutController::class, 'listAccountPaysprint'])->middleware('auth:api');
    Route::post('/payout-list', [App\Http\Controllers\Agent\AepsPayoutController::class, 'listPayoutRequest'])->middleware('auth:api');
    Route::post('/transfer-now', [App\Http\Controllers\Agent\AepsPayoutController::class, 'sendPayoutRequestIserveU'])->middleware('auth:api');
});
