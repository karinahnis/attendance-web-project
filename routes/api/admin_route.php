<?php

use App\Http\Controllers\AuthController;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::prefix('class')->name('class.')->group(function () {
        Route::get('list', [\App\Http\Controllers\Controller::class, ''])->name('list');
        Route::post('add', [\App\Http\Controllers\Controller::class, ''])->name('add');
        Route::prefix('{id}')->group(function () {
            Route::put('edit', [\App\Http\Controllers\Controller::class, ''])->name('edit');
            Route::delete('delete', [\App\Http\Controllers\Controller::class, ''])->name('delete');
        });
    });

    Route::prefix('user')->name('user.')->group(function () {
        Route::get('list', [\App\Http\Controllers\Controller::class, ''])->name('list');
        Route::post('add', [\App\Http\Controllers\Controller::class, ''])->name('add');

        Route::prefix('{id}')->group(function () {
            Route::post('reset', [\App\Http\Controllers\Controller::class, ''])->name('reset');
            Route::put('edit', [\App\Http\Controllers\Controller::class, ''])->name('edit');
            Route::delete('delete', [\App\Http\Controllers\Controller::class, ''])->name('delete');
        });
    });
});
