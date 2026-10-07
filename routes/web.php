<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PortfolioController::class, 'home'])->name('home');
Route::get('/a-propos', [PortfolioController::class, 'about'])->name('about');

Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,10')
    ->name('contact.store');

Route::get('/sitemap.xml', [PortfolioController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [PortfolioController::class, 'robots'])->name('robots');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'entry'])->name('index');
    Route::middleware('guest')->group(function () {
        Route::post('/', [AdminController::class, 'login'])->name('login.store');
    });
    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
        Route::post('/items/{item?}', [AdminController::class, 'saveItem'])->name('items.save');
        Route::post('/uploads', [AdminController::class, 'uploadFiles'])->name('uploads.store');
        Route::delete('/items/{item}', [AdminController::class, 'deleteItem'])->name('items.delete');
        Route::post('/categories/{category?}', [AdminController::class, 'saveCategory'])->name('categories.save');
        Route::delete('/categories/{category}', [AdminController::class, 'deleteCategory'])->name('categories.delete');
        Route::post('/settings', [AdminController::class, 'saveSettings'])->name('settings.save');
        Route::put('/password', [AdminController::class, 'changePassword'])->name('password');
        Route::delete('/messages/{message}', [AdminController::class, 'deleteMessage'])->name('messages.delete');
    });
});
