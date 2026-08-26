<?php

use Azuriom\Plugin\Seeker\Controllers\PublicationController;
use Azuriom\Plugin\Seeker\Controllers\PublicationImageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicationController::class, 'index'])->name('index');

Route::middleware(['auth', 'verified'])->prefix('publications')->name('publications.')->group(function () {
    Route::get('mine', [PublicationController::class, 'mine'])->name('mine');
    Route::get('create', [PublicationController::class, 'create'])->name('create');
    Route::post('/', [PublicationController::class, 'store'])->middleware('throttle:10,1')->name('store');
    Route::get('{publication}/edit', [PublicationController::class, 'edit'])->name('edit');
    Route::put('{publication}', [PublicationController::class, 'update'])->middleware('throttle:20,1')->name('update');
    Route::patch('{publication}/status', [PublicationController::class, 'updateStatus'])->name('status');
    Route::delete('{publication}', [PublicationController::class, 'destroy'])->name('destroy');
});

Route::get('publications/{publication}', [PublicationController::class, 'show'])->name('publications.show');
Route::get('images/{image}', [PublicationImageController::class, 'show'])->name('images.show');
