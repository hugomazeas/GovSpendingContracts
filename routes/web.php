<?php

use App\Http\Controllers\Ajax\DashboardController;
use App\Http\Controllers\ProcurementContractController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProcurementContractController::class, 'index'])->name('dashboard');
Route::get('/contracts/data', [ProcurementContractController::class, 'data'])->name('contracts.data');
Route::get('/organizations/data', [ProcurementContractController::class, 'organizationsData'])->name('organizations.data');
Route::get('/organizations', [ProcurementContractController::class, 'organizationsIndex'])->name('organizations.index');
Route::get('/organization/{organization}', [ProcurementContractController::class, 'organizationDetail'])->name('organization.detail');
Route::get('/vendor/{vendor}', [ProcurementContractController::class, 'vendorDetail'])->name('vendor.detail');

// AJAX Routes for lazy loading
Route::prefix('ajax')->group(function () {
    Route::get('/dashboard/stats-grid', [DashboardController::class, 'statsGrid'])->name('ajax.dashboard.stats-grid');
    Route::get('/dashboard/government-spending-chart', [DashboardController::class, 'governmentSpendingChart'])->name('ajax.dashboard.government-spending-chart');
    Route::get('/dashboard/organizations-pie-chart', [DashboardController::class, 'organizationsPieChart'])->name('ajax.dashboard.organizations-pie-chart');
    Route::get('/dashboard/vendor-leaderboards', [DashboardController::class, 'vendorLeaderboards'])->name('ajax.dashboard.vendor-leaderboards');
    Route::get('/dashboard/organization-leaderboard', [DashboardController::class, 'organizationLeaderboard'])->name('ajax.dashboard.organization-leaderboard');
    Route::get('/organization/{organization}/stats', [DashboardController::class, 'organizationStats'])->name('ajax.organization.stats');
    Route::get('/organization/{organization}/spending-chart', [DashboardController::class, 'organizationSpendingChart'])->name('ajax.organization.spending-chart');
    Route::get('/organization/{organization}/details', [DashboardController::class, 'organizationDetails'])->name('ajax.organization.details');
    Route::get('/vendor/{vendor}/stats', [DashboardController::class, 'vendorStats'])->name('ajax.vendor.stats');
    Route::get('/vendor/{vendor}/spending-chart', [DashboardController::class, 'vendorSpendingChart'])->name('ajax.vendor.spending-chart');
    Route::get('/vendor/{vendor}/minister-leaderboard', [DashboardController::class, 'vendorMinisterLeaderboard'])->name('ajax.vendor.minister-leaderboard');
});
