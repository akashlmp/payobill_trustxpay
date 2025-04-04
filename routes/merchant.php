<?php

use App\Http\Controllers\Merchant\Auth\LoginController;
use App\Http\Controllers\Merchant\DashboardController;
use App\Http\Controllers\Merchant\DownloadController;
use App\Http\Controllers\Merchant\ReportController;
use App\Http\Controllers\Merchant\ProfileController;
use App\Http\Controllers\Merchant\PayoutsRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LoginController::class, 'login'])->name('merchant.login');

Route::post('/login-now', [LoginController::class, 'login_now'])->name('merchant.login_now');

// forgor password here
Route::get('/forgot-password', [LoginController::class, 'forgot_password'])->name('merchant.forgot_password');
Route::post('/forgot-password-otp', [LoginController::class, 'forgot_password_otp'])->name('merchant.forgot_password_otp');
Route::post('/confirm-forgot-password', [LoginController::class, 'confirm_forgot_password'])->name('merchant.confirm_forgot_password');

Route::middleware('merchant_auth')->group(static function () {

    Route::get('logout', [LoginController::class, 'logout'])->name('merchant.logout');

    Route::get('dashboard', [DashboardController::class, 'index'])->name('merchant.dashboard');
    Route::get('transactions', [ReportController::class, 'all_transaction_report'])->name('merchant.transaction');
    Route::get('test-transactions', [ReportController::class, 'test_transaction_report'])->name('merchant.test.transaction');
    Route::get('all-transaction-report-api', [ReportController::class, 'all_transaction_report_api']);
    Route::get('test-all-transaction-report-api', [ReportController::class, 'test_all_transaction_report_api']);
    Route::post('/view-transaction-details', [ReportController::class, 'view_recharge_details']); // Assuming this method is correctly defined

    Route::get('/my-profile', [ProfileController::class, 'my_profile'])->name('merchant.profile');
    Route::post('/change-password', [ProfileController::class, 'change_password'])->name('merchant.changePassword');
    Route::get('/my-settings', [ProfileController::class, 'my_settings'])->name('merchant.my_setting');
    Route::post('/save-settings', [ProfileController::class, 'save_settings'])->name('merchant.save_setting');
    Route::post('/regenerate-keys', [ProfileController::class, 'regenerate_keys'])->name('merchant.regenerate_keys');

    Route::post('/file-download', [DownloadController::class, 'download_file']);
    Route::get('/payouts', [ReportController::class, 'payoutReports']);
    Route::get('/payouts-api', [ReportController::class, 'payoutReportsApi']);

    Route::get('/payment-request', [PayoutsRequestController::class, 'paymentRequest']);
    Route::get('/white-label-add', [PayoutsRequestController::class, 'addWhiteLabel']);
    Route::post('/white-label-add', [PayoutsRequestController::class, 'storeWhiteLabel']);

    Route::get('/white-label-edit/{id}', [PayoutsRequestController::class, 'editWhiteLabel']);
    Route::post('/white-label-edit', [PayoutsRequestController::class, 'updateWhiteLabel']);

    Route::get('/white-label-delete/{id}', [PayoutsRequestController::class, 'deleteWhiteLabel']);
    Route::post('/white-label-delete', [PayoutsRequestController::class, 'destroyBank']);

});
