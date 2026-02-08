<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web'])
    ->prefix('erp/h-r')
    ->name('erp.h_r.')
    ->group(function () {
        Route::get('/', [\Erp\HR\App\Http\Controllers\HRController::class, 'index'])->name('index');
    });
