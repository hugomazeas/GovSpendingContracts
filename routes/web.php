<?php

use App\Http\Controllers\Ajax\DashboardController;
use App\Http\Controllers\ProcurementContractController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProcurementContractController::class, 'index'])->name('dashboard');
Route::get('/contracts/data', [ProcurementContractController::class, 'data'])->name('contracts.data');
Route::get('/organization/{organization}', [ProcurementContractController::class, 'organizationDetail'])->name('organization.detail');

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
});
