<?php

use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Erp\HR\App\Http\Controllers\Admin\HRController;

//Route::group(
//    [
//        'prefix' => LaravelLocalization::setLocale(),
//        'middleware' => [
//            'auth:web',
//            '2faMiddelware',
//            'web',
//            'localeSessionRedirect',
//            'localizationRedirect',
//            'localeViewPath'
//        ]
//    ],
//    function () {
//        Route::resource('h-r', HRController::class);
//
//
//
//    }
//);

Route::resource('h-r', HRController::class);
