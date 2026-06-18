<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\SystemSettingController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn () => redirect()->route('account.index'))->name('dashboard');
    Route::get('/account', AccountController::class)->name('account.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin', 'admin.locale'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/settings', [SystemSettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SystemSettingController::class, 'update'])->name('settings.update');
    Route::put('/languages/{language}/set-default', [LanguageController::class, 'setDefault'])->name('languages.set-default');
    Route::resource('languages', LanguageController::class)->except('show');
});

require __DIR__.'/auth.php';
