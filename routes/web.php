<?php

use App\Http\Controllers\ProcurementContractController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProcurementContractController::class, 'index'])->name('dashboard');
Route::get('/contracts/data', [ProcurementContractController::class, 'data'])->name('contracts.data');
Route::get('/organization/{organization}', [ProcurementContractController::class, 'organizationDetail'])->name('organization.detail');
