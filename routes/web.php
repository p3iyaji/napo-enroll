<?php

use App\Http\Controllers\EnrollementController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::get('enrollments', [EnrollementController::class, 'create'])->name('enrollments');
    Route::post('users', [UserController::class, 'store'])->name('users.store');
    Route::post('enrollments', [EnrollementController::class, 'store'])->name('enrollments.store');
});

require __DIR__ . '/settings.php';
