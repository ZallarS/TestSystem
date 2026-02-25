<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PluginController;

Route::get('/', function () {
    return view('welcome');
});
Route::prefix('admin')->name('admin.')->middleware(['web'])->group(function () {
    Route::get('/plugins', [PluginController::class, 'index'])->name('plugins.index');
    Route::post('/plugins/{plugin}/activate', [PluginController::class, 'activate'])->name('plugins.activate');
    Route::post('/plugins/{plugin}/deactivate', [PluginController::class, 'deactivate'])->name('plugins.deactivate');
    Route::post('/plugins/sync', [PluginController::class, 'sync'])->name('plugins.sync');
});
