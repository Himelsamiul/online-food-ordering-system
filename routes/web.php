<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Backend\AuthController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\CategoryController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\RegistrationController;
use App\Http\Controllers\Frontend\LoginController;


//frontend routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/register', [RegistrationController::class, 'create'])->name('register');
Route::post('/register', [RegistrationController::class, 'store'])->name('register.store');
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('frontend.auth')->group(function () {

    // About Us (protected as you asked)
    Route::get('/about', [HomeController::class, 'Aboutus'])->name('about');

});


//backend routes
Route::prefix('admin')->name('admin.')->group(function () {

    // Login page
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        // registered user management
        Route::get('/registrations', [RegistrationController::class, 'registrations'])->name('registrations');
        Route::delete('/registrations/{id}/delete', [RegistrationController::class, 'deleteRegistration'])->name('registrations.delete');
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

        // Category Module
        Route::get('/categories', [CategoryController::class, 'index'])->name('category.index');
        Route::post('/categories/store', [CategoryController::class, 'store'])->name('category.store');
        Route::get('/categories/{id}/edit', [CategoryController::class, 'edit'])->name('category.edit');
        Route::post('/categories/{id}/update', [CategoryController::class, 'update'])->name('category.update');     
        Route::delete('/categories/{id}/delete', [CategoryController::class, 'destroy'])->name('category.delete');
});
