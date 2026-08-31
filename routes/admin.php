<?php

use Azuriom\Plugin\Seeker\Controllers\Admin\ConversationController;
use Azuriom\Plugin\Seeker\Controllers\Admin\ProfileReportController;
use Azuriom\Plugin\Seeker\Controllers\Admin\ProfileController;
use Azuriom\Plugin\Seeker\Controllers\Admin\PublicationController;
use Azuriom\Plugin\Seeker\Controllers\Admin\ReportController;
use Azuriom\Plugin\Seeker\Controllers\Admin\RestrictionController;
use Azuriom\Plugin\Seeker\Controllers\Admin\SettingController;
use Azuriom\Plugin\Seeker\Controllers\Admin\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('settings', [SettingController::class, 'show'])->name('settings');
Route::post('settings', [SettingController::class, 'save'])->name('settings.save');
Route::post('settings/discord-webhook/test', [SettingController::class, 'testDiscordWebhook'])->name('settings.discord-webhook.test');
Route::get('publications', [PublicationController::class, 'index'])->name('publications.index');
Route::get('publications/{publication}', [PublicationController::class, 'show'])->withTrashed()->name('publications.show');
Route::patch('publications/{publication}/status', [PublicationController::class, 'updateStatus'])->withTrashed()->name('publications.status');
Route::get('conversations', [ConversationController::class, 'index'])->name('conversations.index');
Route::get('conversations/{conversation}', [ConversationController::class, 'show'])->name('conversations.show');
Route::patch('conversations/{conversation}/close', [ConversationController::class, 'close'])->name('conversations.close');
Route::patch('conversations/{conversation}/reopen', [ConversationController::class, 'reopen'])->name('conversations.reopen');
Route::patch('messages/{message}/hide', [ConversationController::class, 'hideMessage'])->name('messages.hide');
Route::patch('messages/{message}/restore', [ConversationController::class, 'restoreMessage'])->name('messages.restore');
Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
Route::patch('reports/{type}/{report}', [ReportController::class, 'update'])
    ->where('type', 'publication|profile|conversation')
    ->whereNumber('report')
    ->name('reports.update');
Route::get('restrictions', [RestrictionController::class, 'index'])->name('restrictions.index');
Route::get('restrictions/users/search', [RestrictionController::class, 'searchUsers'])->name('restrictions.users.search');
Route::post('restrictions', [RestrictionController::class, 'store'])->name('restrictions.store');
Route::patch('restrictions/{restriction}/revoke', [RestrictionController::class, 'revoke'])->name('restrictions.revoke');
Route::delete('restrictions/users/{user}/publications', [RestrictionController::class, 'removePublications'])->name('restrictions.publications.remove');
Route::get('profile-reports', [ProfileReportController::class, 'index'])->name('profile-reports.index');
Route::patch('profile-reports/{report}', [ProfileReportController::class, 'update'])->name('profile-reports.update');
Route::delete('profiles/{user}/biography', [ProfileController::class, 'clearBiography'])->name('profiles.biography.clear');
Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
