<?php

use Azuriom\Plugin\Seeker\Controllers\Admin\ProfileReportController;
use Azuriom\Plugin\Seeker\Controllers\Admin\PublicationController;
use Azuriom\Plugin\Seeker\Controllers\Admin\SectionController;
use Azuriom\Plugin\Seeker\Controllers\Admin\SettingController;
use Illuminate\Support\Facades\Route;

Route::get('settings', [SettingController::class, 'show'])->name('settings');
Route::post('settings', [SettingController::class, 'save'])->name('settings.save');
Route::get('publications', [PublicationController::class, 'index'])->name('publications.index');
Route::get('publications/{publication}', [PublicationController::class, 'show'])->name('publications.show');
Route::patch('publications/{publication}/status', [PublicationController::class, 'updateStatus'])->name('publications.status');
Route::get('conversations', [SectionController::class, 'conversations'])->name('conversations.index');
Route::get('profile-reports', [ProfileReportController::class, 'index'])->name('profile-reports.index');
Route::patch('profile-reports/{report}', [ProfileReportController::class, 'update'])->name('profile-reports.update');
Route::get('transactions', [SectionController::class, 'transactions'])->name('transactions.index');
