<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\Admin\WithdrawalController as AdminWithdrawalController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\ReferralController; 
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\WinnerController as AdminWinnerController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\CampaignController as AdminCampaignController;
use App\Http\Controllers\TransferController;


use App\Http\Controllers\WinnerController; 

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');


Route::get('/dashboard', [DashboardController::class, 'show'], function () {
    return Inertia::render('Dashboard');
})
    ->middleware(['auth']) // <--- SOLO DEJA 'auth'
    ->name('dashboard');

Route::get('/my-referrals', [ReferralController::class, 'index'])
    ->middleware(['auth']) // Protegida para usuarios logueados y verificados
    ->name('referrals.index');


Route::post('/find-user-by-code', [TransferController::class, 'findByReferralCode'])
    ->middleware(['auth'])
    ->name('users.find-by-code');

Route::post('/subscriptions/{subscription}/switch-to-closed', [SubscriptionController::class, 'switchToClosed'])
    ->middleware(['auth'])
    ->name('subscriptions.switch'); 

Route::post('/transfer', [TransferController::class, 'store'])
    ->middleware(['auth'])
    ->name('transfer.store');
    
Route::get('/dashboard/statement/download', [DashboardController::class, 'downloadStatement'])
    ->middleware(['auth'])
    ->name('dashboard.statement.download');
// Grupo de rutas para el ADMIN
// Protegido por 'auth' y por nuestro middleware 'is.admin'
Route::middleware(['auth', 'is.admin'])->name('admin.')->prefix('admin')->group(function () {
    Route::resource('users', UserController::class)->except(['edit', 'create']);
    Route::resource('transactions', TransactionController::class)->except(['show', 'edit', 'create']);
});

Route::get('/winners', [WinnerController::class, 'index'])
    ->middleware(['auth']) // Solo para usuarios logueados
    ->name('winners.index');

Route::middleware(['auth'])->group(function () {
    Route::get('/select-plan', [PlanController::class, 'selection'])->name('plan.selection');
    Route::post('/select-plan', [PlanController::class, 'store'])->name('plan.store');
});

Route::get('/market', function () {
    return Inertia::render('Market/Index');
})->middleware(['auth'])->name('market.index');

// Actualiza la ruta del dashboard para usar el nuevo middleware
Route::get('/dashboard', [DashboardController::class, 'show'])
    ->middleware(['auth', 'verified', 'plan.selected']) // <-- AÑADE 'plan.selected'
    ->name('dashboard');

Route::post('/subscriptions', [SubscriptionController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('subscriptions.store');

Route::post('/withdrawals', [WithdrawalController::class, 'store'])
    ->middleware(['auth'])
    ->name('withdrawals.store');

Route::get('/account-blocked', function () {
    return Inertia::render('auth/Blocked');
})->middleware(['auth'])->name('account.blocked');

Route::middleware(['auth', 'is.admin'])->prefix('admin')->name('admin.')->group(function () {
    // Ruta para mostrar la lista de suscripciones pendientes
    Route::get('/subscriptions/pending', [AdminSubscriptionController::class, 'pending'])
        ->name('subscriptions.pending');

    // Ruta para aprobar una suscripción
    Route::patch('/subscriptions/{subscription}/approve', [AdminSubscriptionController::class, 'approve'])
        ->name('subscriptions.approve');

    Route::get('/withdrawals', [AdminWithdrawalController::class, 'index'])
        ->name('withdrawals.index');

    Route::patch('/withdrawals/{withdrawal}/complete', [AdminWithdrawalController::class, 'complete'])
        ->name('withdrawals.complete');

    Route::get('/dashboard', [AdminDashboardController::class, 'index'])
         ->name('dashboard');
    Route::get('/reports/subscriptions', [AdminReportController::class, 'subscriptions'])
        ->name('reports.subscriptions');
    Route::get('/reports/payments', [AdminReportController::class, 'payments'])
        ->name('reports.payments');
    Route::get('/reports/withdrawals', [AdminReportController::class, 'withdrawals'])
        ->name('reports.withdrawals');
    Route::get('/reports/subscriptions/export', [AdminReportController::class, 'exportSubscriptions'])
        ->name('reports.subscriptions.export');
    Route::get('/reports/payments/export', [AdminReportController::class, 'exportPayments'])
        ->name('reports.payments.export');
    Route::get('/reports/withdrawals/export', [AdminReportController::class, 'exportWithdrawals'])
        ->name('reports.withdrawals.export');
    Route::post('/users/block-all', [AdminUserController::class, 'blockAll'])
        ->name('users.block-all');

    Route::resource('campaigns', AdminCampaignController::class);
    Route::resource('winners', AdminWinnerController::class);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
