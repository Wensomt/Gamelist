<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;

Route::get('/', function () {
    return view('home');
});

// LOGOWANIE - FORMULARZ (GET) - NAZWA: login
Route::get('/login', function () {
    return view('auth.login');
})->name('login');  // TO JEST WAŻNE: nazwa 'login'

// LOGOWANIE - OBSŁUGA (POST) - TEŻ NAZWA: login (to OK, Laravel rozróżnia metodę)
Route::post('/login', [AuthController::class, 'webLogin'])->name('login');  // TAKA SAMA NAZWA, INNA METODA

// REJESTRACJA - DODAJ POST
Route::get('/register', function () {
    return view('auth.register');
})->name('register');

// DODAJ TĘ LINIĘ:
Route::post('/register', [AuthController::class, 'webRegister'])->name('register');

// GRY - normalna sesja
Route::middleware(['auth'])->group(function () {
    Route::get('/games', function () {
        return view('games');
    })->name('games');
    
    // PROFIL
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::put('/password', [App\Http\Controllers\Auth\PasswordController::class, 'update'])->name('password.update');
});

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// PANEL ADMINA - TYLKO DLA ADMINÓW
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Główny pulpit (plik resources/views/admin/admin.blade.php)
    Route::get('/', [AdminController::class, 'index'])->name('index');
    
    // Lista użytkowników (plik resources/views/admin/admin-users.blade.php)
    Route::get('/users', [AdminController::class, 'listUsers'])->name('users');
    
    // Akcja zmiany uprawnień (Przycisk "Zmień rolę")
    Route::post('/users/{id}/toggle', [AdminController::class, 'toggleAdmin'])->name('toggle-admin');
    
    // Opcjonalnie: Usuwanie użytkownika
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('destroy');
});