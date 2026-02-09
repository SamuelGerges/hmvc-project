<?php

use Illuminate\Support\Facades\Route;
use Erp\HR\App\Http\Controllers\Api\HRController;

Route::group(
    ['middleware' => ['api', 'auth:api'], 'prefix' => 'api'],
    function () {
        Route::apiResource('h-r', ModuleController::class);
        // Add more API routes here
    }
);
