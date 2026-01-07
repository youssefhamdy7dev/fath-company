<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerPaymentController;
use App\Http\Controllers\CustomerPurchaseController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FruitController;
use App\Http\Controllers\TruckController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::group(['prefix' => 'auth'], function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::get('password-reset', [AuthController::class, 'showResetPasswordForm'])->name('password');
    Route::post('password-reset', [AuthController::class, 'resetPassword'])->name('password.reset');
});

// Route::middleware('auth')->group(function () {

// });

Route::resource('fruits', FruitController::class);
Route::resource('drivers', DriverController::class);
Route::resource('clients', ClientController::class);
Route::resource('trucks', TruckController::class);
Route::resource('bills', BillController::class);

Route::resource('employees', EmployeeController::class);
Route::prefix('employees')->name('employees.')->controller(EmployeeController::class)->group(function () {
    // Add holiday for a specific employee
    Route::post('{employee}/holidays', 'holidayAdd')->name('holidayAdd');
    // Add withdrawal for a specific employee
    Route::post('{employee}/withdrawals', 'withdraw')->name('withdraw');
    // Delete a specific holiday
    Route::delete('holidays/{holiday}', 'holidayDelete')->name('holidayDelete');
    // Delete a specific withdrawal
    Route::delete('withdrawals/{withdrawal}', 'deleteWithdrawal')->name('deleteWithdrawal');
    Route::post('{employee}/monthly-wage-preview', 'previewMonthlyWage')->name('monthlyWage.preview');
    Route::post('{employee}/monthly-wage-store', 'storeMonthlyWage')->name('monthlyWage');
    Route::post('{employee}/clear-wages-history', 'clearWagesHistory')->name('clearWagesHistory');
});

Route::prefix('customers')->name('customers.')->controller(CustomerController::class)->group(function () {
    Route::get('reports', 'reports')->name('reports');
    Route::get('{customer}/payments', 'payments')->name('payments');
    Route::get('{customer}/purchases', 'purchases')->name('purchases');
});
Route::resource('customers', CustomerController::class);

Route::prefix('customer-purchases')->name('customer-purchases.')->controller(CustomerPurchaseController::class)->group(function () {
    // Store new purchase (AJAX)
    Route::post('/store', 'store')->name('store');
    // Update existing purchase (AJAX)
    Route::post('/update/{purchase}', 'update')->name('update');
    // Delete a purchase
    Route::delete('/delete/{purchase}', 'destroy')->name('destroy');
});

Route::prefix('customer-payments')->name('customer-payments.')->controller(CustomerPaymentController::class)->group(function () {
    // Dates index (distinct dates)
    Route::get('/', 'index')->name('index');
    // Payments for a date (payments-by-date page)
    Route::get('/date/{date}', 'byDate')->name('byDate');
    // Get single payment data (for edit modal)
    Route::get('/payment/{payment}', 'payment')->name('payment');
    // Store new single payment (AJAX)
    Route::post('/store', 'store')->name('store');
    // Update existing payment (AJAX)
    Route::post('/update/{payment}', 'update')->name('update');
    // Delete a payment (non-AJAX)
    Route::delete('/delete/{payment}', 'destroy')->name('destroy');
    // Daily/bulk page (إضافة يومية تحصيل)
    Route::get('/daily/create', 'dailyCreate')->name('daily.create');
    // Bulk store (daily submission)
    Route::post('/daily/store', 'storeDaily')->name('daily.store');
    // AJAX daily balance retrieve
    Route::post('/daily/balance', 'dailyBalance')->name('daily.balance');
});
