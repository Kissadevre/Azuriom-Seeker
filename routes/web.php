<?php

use Azuriom\Plugin\Seeker\Controllers\CommissionCompletionController;
use Azuriom\Plugin\Seeker\Controllers\ConversationController;
use Azuriom\Plugin\Seeker\Controllers\ConversationReportController;
use Azuriom\Plugin\Seeker\Controllers\MessageController;
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

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('publications/{publication}/contact', [ConversationController::class, 'create'])->name('conversations.create');
    Route::post('publications/{publication}/contact', [ConversationController::class, 'store'])->middleware('throttle:10,1')->name('conversations.store');
    Route::get('conversations', [ConversationController::class, 'index'])->name('conversations.index');
    Route::get('conversations/{conversation}/report', [ConversationReportController::class, 'create'])->name('conversations.reports.create');
    Route::post('conversations/{conversation}/report', [ConversationReportController::class, 'store'])->middleware('throttle:5,1')->name('conversations.reports.store');
    Route::post('conversations/{conversation}/completion/request', [CommissionCompletionController::class, 'requestCompletion'])->middleware('throttle:10,1')->name('conversations.completion.request');
    Route::get('conversations/{conversation}/completion', [CommissionCompletionController::class, 'show'])->name('conversations.completion.show');
    Route::post('conversations/{conversation}/completion/confirm', [CommissionCompletionController::class, 'confirm'])->middleware('throttle:10,1')->name('conversations.completion.confirm');
    Route::post('conversations/{conversation}/completion/reject', [CommissionCompletionController::class, 'reject'])->middleware('throttle:10,1')->name('conversations.completion.reject');
    Route::get('conversations/{conversation}', [ConversationController::class, 'show'])->name('conversations.show');
    Route::post('conversations/{conversation}/messages', [MessageController::class, 'store'])->middleware('throttle:30,1')->name('conversations.messages.store');
});

Route::get('publications/{publication}', [PublicationController::class, 'show'])->name('publications.show');
Route::get('images/{image}', [PublicationImageController::class, 'show'])->name('images.show');
