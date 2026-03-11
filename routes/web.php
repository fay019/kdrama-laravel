<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\WatchlistController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AdminAuthorController;
use App\Http\Controllers\Admin\AdminIconsController;
use App\Http\Controllers\Admin\AdminContactController;
use App\Http\Controllers\Admin\AdminExportController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\SetupController;
use Illuminate\Support\Facades\Route;

// Language switcher
Route::post('/language/{locale}', function ($locale) {
    $supported = ['fr', 'en', 'de'];
    if (!in_array($locale, $supported)) {
        abort(400);
    }

    session(['locale' => $locale]);
    if (auth()->check()) {
        auth()->user()->update(['preferred_language' => $locale]);
    }

    return back();
})->name('language.switch');

// Setup routes
Route::prefix('setup')->name('setup.')->group(function () {
    Route::get('/', [SetupController::class, 'index'])->name('index');
    Route::post('/', [SetupController::class, 'setup'])->name('process');
});

// Public routes (with password check for authenticated users)
Route::get('/', [ContentController::class, 'index'])->middleware('check.password')->name('home');
Route::get('/kdrams', [ContentController::class, 'catalog'])->middleware('check.password')->name('kdrams.catalog');
Route::get('/kdrams/{id}', [ContentController::class, 'show'])->middleware('check.password')->name('kdrams.show');
Route::get('/api/actor/{id}', [ContentController::class, 'actorDetails'])->middleware('check.password')->name('api.actor.details');

// Contact routes (with password check for authenticated users)
Route::get('/contact', [ContactController::class, 'show'])->middleware('check.password')->name('contact.show');
Route::post('/contact', [ContactController::class, 'store'])->middleware('check.password')->name('contact.store');

// Admin protected routes
Route::middleware(['auth', 'admin', 'check.password'])->group(function () {
    Route::post('/kdrams/{id}/refresh-streaming', [ContentController::class, 'refreshStreaming'])->name('kdrams.refresh-streaming');
});

// User dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified', 'check.password'])->name('dashboard');

// Protected user routes
Route::middleware(['auth', 'check.password'])->group(function () {
    // Password change (always accessible, even if password_must_change is true)
    Route::get('/change-password', [ProfileController::class, 'changePassword'])->name('password.change');
    Route::post('/change-password', [ProfileController::class, 'updatePassword'])->name('change-password.update');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Watchlist routes
    Route::get('/watchlist', [WatchlistController::class, 'index'])->name('watchlist.index');
    Route::delete('/watchlist/{id}', [WatchlistController::class, 'remove'])->name('watchlist.remove');

    // API routes for AJAX
    Route::post('/api/watchlist/toggle/{contentId}', [WatchlistController::class, 'toggleWatchlist'])->name('api.watchlist.toggle');
    Route::post('/api/watched/toggle/{contentId}', [WatchlistController::class, 'toggleWatched'])->name('api.watched.toggle');
    Route::get('/api/watchlist/status/{contentId}', [WatchlistController::class, 'checkStatus'])->name('api.watchlist.status');
    Route::delete('/api/watchlist/{contentId}', [WatchlistController::class, 'deleteItem'])->name('api.watchlist.delete');
    Route::post('/api/rating/{contentId}', [WatchlistController::class, 'rateItem'])->name('api.rating.set');

    // Export routes
    Route::get('/watchlist/export-modal', [WatchlistController::class, 'showExportModal'])->name('watchlist.export-modal');
    Route::post('/watchlist/export', [WatchlistController::class, 'export'])->name('watchlist.export');
});

// Admin routes
Route::middleware(['auth', 'admin', 'check.password'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    Route::resource('users', AdminUserController::class);
    Route::post('users/{id}/reset-password', [AdminUserController::class, 'resetPassword'])->name('users.reset-password');
    Route::get('settings', [AdminSettingsController::class, 'index'])->name('settings.index');
    Route::post('settings', [AdminSettingsController::class, 'update'])->name('settings.update');
    Route::get('author', [AdminAuthorController::class, 'edit'])->name('author.edit');
    Route::post('author', [AdminAuthorController::class, 'update'])->name('author.update');
    Route::post('author/social-links/reorder', [AdminAuthorController::class, 'reorderSocialLinks'])->name('author.social-links.reorder');
    Route::get('icons', [AdminIconsController::class, 'search'])->name('icons.search');

    // Contact messages
    Route::get('contact', [AdminContactController::class, 'index'])->name('contact.index');
    Route::get('contact/{id}', [AdminContactController::class, 'show'])->name('contact.show');
    Route::post('contact/{id}/status', [AdminContactController::class, 'updateStatus'])->name('contact.update-status');
    Route::delete('contact/{id}', [AdminContactController::class, 'destroy'])->name('contact.destroy');
    Route::get('contact/{id}/attachment', [AdminContactController::class, 'downloadAttachment'])->name('contact.download-attachment');

    // Export management
    Route::get('exports/cache', [AdminExportController::class, 'cacheIndex'])->name('exports.cache');
    Route::post('exports/cache/{filename}', [AdminExportController::class, 'deleteCache'])->name('exports.cache.delete');
    Route::post('exports/cache-purge-all', [AdminExportController::class, 'purgeAllCache'])->name('exports.cache.purge-all');
    Route::post('exports/cache-purge-expired', [AdminExportController::class, 'purgeExpiredCache'])->name('exports.cache.purge-expired');
    Route::get('exports/stats', [AdminExportController::class, 'statsIndex'])->name('exports.stats');
    Route::post('exports/user/{userId}', [AdminExportController::class, 'exportUserWatchlist'])->name('exports.user');
});

require __DIR__.'/auth.php';
