<?php

use App\Http\Controllers\Admin\VersionImportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware('auth:admin')->group(function () {
    Route::get('/me', fn (Request $request) => $request->user());
    Route::post('/versions', VersionImportController::class);
});

Route::get('/user', fn (Request $request) => $request->user())
    ->middleware('auth:users');
