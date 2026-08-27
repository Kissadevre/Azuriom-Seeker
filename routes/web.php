<?php

use Azuriom\Plugin\Seeker\Controllers\CommissionCompletionController;
use Azuriom\Plugin\Seeker\Controllers\ConversationController;
use Azuriom\Plugin\Seeker\Controllers\ConversationReportController;
use Azuriom\Plugin\Seeker\Controllers\MessageController;
use Azuriom\Plugin\Seeker\Controllers\MessageImageController;
use Azuriom\Plugin\Seeker\Controllers\ProfileController;
use Azuriom\Plugin\Seeker\Controllers\ProfileReportController;
use Azuriom\Plugin\Seeker\Controllers\PublicationController;
use Azuriom\Plugin\Seeker\Controllers\PublicationImageController;
use Azuriom\Plugin\Seeker\Controllers\PublicationMediaController;
use Azuriom\Plugin\Seeker\Controllers\PublicationReportController;
use Azuriom\Plugin\Seeker\Controllers\ReviewController;
use Azuriom\Plugin\Seeker\Controllers\RestrictionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicationController::class, 'index'])->name('index');

Route::get('profiles/{user}', [ProfileController::class, 'show'])->name('profiles.show');

Route::middleware(['auth', 'verified'])->prefix('publications')->name('publications.')->group(function () {
    Route::get('mine', [PublicationController::class, 'mine'])->name('mine');
    Route::get('create', [PublicationController::class, 'create'])->name('create');
    Route::post('/', [PublicationController::class, 'store'])->middleware(['throttle:seeker.publications.create', 'captcha'])->name('store');
    Route::get('{publication}/edit', [PublicationController::class, 'edit'])->name('edit');
    Route::put('{publication}', [PublicationController::class, 'update'])->middleware(['throttle:seeker.publications.edit', 'captcha'])->name('update');
    Route::patch('{publication}/status', [PublicationController::class, 'updateStatus'])->name('status');
    Route::delete('{publication}', [PublicationController::class, 'destroy'])->name('destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('restrictions/{type}', [RestrictionController::class, 'show'])
        ->whereIn('type', \Azuriom\Plugin\Seeker\Models\UserRestriction::types())
        ->name('restrictions.show');
    Route::get('profiles/{user}/edit', [ProfileController::class, 'edit'])->name('profiles.edit');
    Route::put('profiles/{user}', [ProfileController::class, 'update'])->middleware('throttle:10,1')->name('profiles.update');
    Route::get('profiles/{user}/report', [ProfileReportController::class, 'create'])->name('profiles.reports.create');
    Route::post('profiles/{user}/report', [ProfileReportController::class, 'store'])->middleware('throttle:5,1')->name('profiles.reports.store');
    Route::get('publications/{publication}/report', [PublicationReportController::class, 'create'])->name('publications.reports.create');
    Route::post('publications/{publication}/report', [PublicationReportController::class, 'store'])->middleware('throttle:5,1')->name('publications.reports.store');
    Route::get('publications/{publication}/contact', [ConversationController::class, 'create'])->name('conversations.create');
    Route::post('publications/{publication}/contact', [ConversationController::class, 'store'])->middleware('throttle:10,1')->name('conversations.store');
    Route::get('conversations', [ConversationController::class, 'index'])->name('conversations.index');
    Route::get('conversations/{conversation}/report', [ConversationReportController::class, 'create'])->name('conversations.reports.create');
    Route::post('conversations/{conversation}/report', [ConversationReportController::class, 'store'])->middleware('throttle:5,1')->name('conversations.reports.store');
    Route::post('conversations/{conversation}/completion/request', [CommissionCompletionController::class, 'requestCompletion'])->middleware('throttle:10,1')->name('conversations.completion.request');
    Route::get('conversations/{conversation}/completion', [CommissionCompletionController::class, 'show'])->name('conversations.completion.show');
    Route::post('conversations/{conversation}/completion/confirm', [CommissionCompletionController::class, 'confirm'])->middleware('throttle:10,1')->name('conversations.completion.confirm');
    Route::post('conversations/{conversation}/completion/reject', [CommissionCompletionController::class, 'reject'])->middleware('throttle:10,1')->name('conversations.completion.reject');
    Route::get('conversations/{conversation}/review', [ReviewController::class, 'create'])->name('conversations.reviews.create');
    Route::post('conversations/{conversation}/review', [ReviewController::class, 'store'])->middleware('throttle:5,1')->name('conversations.reviews.store');
    Route::get('conversations/{conversation}', [ConversationController::class, 'show'])->name('conversations.show');
    Route::post('conversations/{conversation}/messages', [MessageController::class, 'store'])->middleware('throttle:30,1')->name('conversations.messages.store');
    Route::get('messages/{message}/image', [MessageImageController::class, 'show'])->name('messages.images.show');
});

Route::get('publications/{publication}', [PublicationController::class, 'show'])->name('publications.show');
Route::get('images/{image}', [PublicationImageController::class, 'show'])->name('images.show');
Route::get('media/{media}', [PublicationMediaController::class, 'show'])->name('media.show');
