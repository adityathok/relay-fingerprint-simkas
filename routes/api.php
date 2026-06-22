<?php

use App\Http\Controllers\FingerPrintController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('iclock')->group(function () {
    Route::match(['GET', 'POST'], '/cdata', [FingerPrintController::class, 'cdata']);
    Route::get('/getrequest', [FingerPrintController::class, 'getrequest']);
    Route::get('/devicecmd', [FingerPrintController::class, 'devicecmd']);
});
