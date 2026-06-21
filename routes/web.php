<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::livewire('admin/clients', 'pages::client.index')->name('admin.clients');
    Route::livewire('admin/devices', 'pages::device.index')->name('admin.devices');
    Route::livewire('admin/fingerprint-raw-logs', 'pages::fingerprintRawLog.index')->name('admin.fingerprintRawLogs');
});

require __DIR__.'/settings.php';
