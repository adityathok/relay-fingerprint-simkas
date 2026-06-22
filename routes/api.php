<?php

use App\Http\Controllers\FingerPrintController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('iclock')->group(function () {
    Route::post('/rtdata', [FingerPrintController::class, 'rtdata']);
    Route::post('/getrequest', [FingerPrintController::class, 'getrequest']);
    Route::post('/cdata', [FingerPrintController::class, 'devicecmd']);
});
