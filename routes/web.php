<?php

use App\Http\Controllers\AdminController;
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

// Route::middleware(['auth', 'role:admin'])
//     ->group(function () {

//     });

Route::middleware('auth')->group(function () {
    Route::post('/logout',          [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile',          [AuthController::class, 'profile'])->name('profile');
    Route::patch('/profile',        [AuthController::class, 'updateProfile'])->name('profile.update');

    // ── Tickets ───────────────────────────────────────────────────────────────
    Route::resource('tickets', TicketController::class);
    Route::post('tickets/{ticket}/transition', [TicketController::class, 'transition'])
         ->name('tickets.transition');
    Route::post('tickets/{ticket}/assign',     [TicketController::class, 'assign'])
         ->name('tickets.assign');

    // ── Comments ──────────────────────────────────────────────────────────────
    Route::post('tickets/{ticket}/comments',              [CommentController::class, 'store'])
         ->name('tickets.comments.store');
    Route::delete('tickets/{ticket}/comments/{comment}', [CommentController::class, 'destroy'])
         ->name('tickets.comments.destroy');

    // ── Admin ─────────────────────────────────────────────────────────────────
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('admin', AdminController::class);
        Route::get('/',                [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/users',           [AdminController::class, 'users'])->name('users');
        Route::patch('/users/{user}/role', [AdminController::class, 'updateUserRole'])
             ->name('users.role');
    });
});

// ── Root redirect ─────────────────────────────────────────────────────────────
Route::get('/', fn () => redirect()->route('tickets.index'));