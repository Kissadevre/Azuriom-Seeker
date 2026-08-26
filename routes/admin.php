<?php

use Azuriom\Plugin\Seeker\Controllers\Admin\PublicationController;
use Illuminate\Support\Facades\Route;

Route::get('publications', [PublicationController::class, 'index'])->name('publications.index');
Route::patch('publications/{publication}/status', [PublicationController::class, 'updateStatus'])->name('publications.status');
