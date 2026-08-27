<?php

use Azuriom\Plugin\Seeker\Controllers\Admin\PublicationController;
use Azuriom\Plugin\Seeker\Controllers\Admin\ProfileReportController;
use Illuminate\Support\Facades\Route;

Route::get('publications', [PublicationController::class, 'index'])->name('publications.index');
Route::patch('publications/{publication}/status', [PublicationController::class, 'updateStatus'])->name('publications.status');
Route::get('profile-reports', [ProfileReportController::class, 'index'])->name('profile-reports.index');
Route::patch('profile-reports/{report}', [ProfileReportController::class, 'update'])->name('profile-reports.update');
