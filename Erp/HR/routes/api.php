<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['api'])
    ->prefix('api/erp/h-r')
    ->name('erp.h_r.api.')
    ->group(function () {
        Route::get('/', [\Erp\HR\App\Http\Controllers\HRController::class, 'index'])->name('index');
    });
