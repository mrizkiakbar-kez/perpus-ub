<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BorrowingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ReportController;

Route::get('/', function () {
    if (Illuminate\Support\Facades\Auth::check()) {
        return redirect()->route('admin.dashboard');
    } elseif (session()->has('member_id')) {
        return redirect()->route('member.dashboard');
    }
    return redirect()->route('login');
});

// Authentication
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Admin Routes - Full Management Access
Route::middleware(['auth', 'is_admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');

    // Book Management
    Route::resource('books', BookController::class);

    // Category Management
    Route::resource('categories', CategoryController::class);

    // Member Management
    Route::resource('members', MemberController::class);
    
    // Borrowing Management (Admin sees all)
    Route::resource('borrowings', BorrowingController::class)->only(['index','show']);

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports');
});

// Member Routes - Limited Access
Route::middleware(['is_member'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'member'])->name('member.dashboard');

    // Members can browse books
    Route::get('/books', [BookController::class, 'index'])->name('books.index');
    Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');
    
    // Members can view and create borrowing requests
    Route::get('/borrowings', [BorrowingController::class, 'index'])->name('borrowings.index');
    Route::get('/borrowings/create', [BorrowingController::class, 'create'])->name('borrowings.create');
    Route::post('/borrowings', [BorrowingController::class, 'store'])->name('borrowings.store');
    Route::get('/borrowings/{id}', [BorrowingController::class, 'show'])->name('borrowings.show');
    Route::post('/borrowings/{id}/return', [BorrowingController::class, 'returnBook'])->name('borrowings.return');
    Route::post('/borrow/{book_id}', [BorrowingController::class, 'borrowDirect'])->name('books.borrow');
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('member.profile');
    Route::post('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('member.profile.update');
});
