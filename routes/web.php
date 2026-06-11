<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\SlaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

// ── Auth ──────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout',          [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile',          [AuthController::class, 'profile'])->name('profile');
    Route::patch('/profile',        [AuthController::class, 'updateProfile'])->name('profile.update');

    // ── SLA Notifications (JSON API) ──────────────────────────────────────────
    Route::get('/sla/check', [SlaController::class, 'check']);
    Route::get('api/sla-notifications',              [SlaController::class, 'index'])->name('sla.notifications');
    Route::post('api/sla-notifications/{id}/dismiss',[SlaController::class, 'dismiss'])->name('sla.dismiss');
    Route::post('api/sla-notifications/dismiss-all', [SlaController::class, 'dismissAll'])->name('sla.dismiss-all');

    // ── Tickets ───────────────────────────────────────────────────────────────
    Route::resource('tickets', TicketController::class);
    Route::post('tickets/{ticket}/transition', [TicketController::class, 'transition'])->name('tickets.transition');
    Route::post('tickets/{ticket}/assign', [TicketController::class, 'assign'])->name('tickets.assign');

    // ── Comments ──────────────────────────────────────────────────────────────
    Route::post('tickets/{ticket}/comments',[CommentController::class, 'store'])->name('tickets.comments.store');
    Route::delete('tickets/{ticket}/comments/{comment}',[CommentController::class, 'destroy'])->name('tickets.comments.destroy');
    Route::delete('tickets/{ticket}/comments/{comment}/attachments/{attachment}',[CommentController::class, 'destroyAttachment'])->name('tickets.comments.attachments.destroy');
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    // ── Admin ─────────────────────────────────────────────────────────────────
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::resource('admin', AdminController::class);

        // ── Assets ─────────────────────────────────────────
        Route::get('assets',               [AssetController::class, 'index'])->name('assets.index');
        Route::get('assets/create',        [AssetController::class, 'create'])->name('assets.create');
        Route::post('assets',              [AssetController::class, 'store'])->name('assets.store');
        Route::get('assets/{asset}',       [AssetController::class, 'show'])->name('assets.show');
        Route::get('assets/{asset}/edit',  [AssetController::class, 'edit'])->name('assets.edit');
        Route::put('assets/{asset}',       [AssetController::class, 'update'])->name('assets.update');
        Route::delete('assets/{asset}',    [AssetController::class, 'destroy'])->name('assets.destroy');
 
        Route::post('assets/{asset}/assign',      [AssetController::class, 'assign'])->name('assets.assign');
        Route::post('assets/{asset}/return',      [AssetController::class, 'returnAsset'])->name('assets.return');
        Route::post('assets/{asset}/retire',      [AssetController::class, 'retire'])->name('assets.retire');
        Route::post('assets/{asset}/maintenance', [AssetController::class, 'storeMaintenance'])->name('assets.maintenance.store');

        // ── User management ───────────────────────────────────────────────────
        Route::get('users',                  [AdminController::class, 'index'])->name('users.index');
        Route::get('users/create',           [AdminController::class, 'create'])->name('users.create');
        Route::post('users',                 [AdminController::class, 'store'])->name('users.store');
        Route::get('users/{user}',           [AdminController::class, 'show'])->name('users.show');
        Route::get('users/{user}/edit',      [AdminController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}',           [AdminController::class, 'update'])->name('users.update');
        Route::delete('users/{user}',        [AdminController::class, 'destroy'])->name('users.destroy');
        Route::patch('/users/{user}/role', [AdminController::class, 'updateUserRole'])->name('users.role');
        Route::patch('users/{user}/toggle-status', [AdminController::class, 'toggleStatus'])->name('users.toggle-status');
    });
});

// ── Root redirect ─────────────────────────────────────────────────────────────
Route::get('/', fn () => redirect()->route('admin.dashboard'));