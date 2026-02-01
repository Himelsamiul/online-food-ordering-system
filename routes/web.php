<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Backend\AuthController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\CategoryController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\RegistrationController;
use App\Http\Controllers\Frontend\LoginController;
use App\Http\Controllers\Backend\SubcategoryController;
use App\Http\Controllers\Backend\UnitController;
use App\Http\Controllers\Backend\FoodController;


//frontend routes
// frontend routes
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('guest:frontend')->group(function () {
    Route::get('/register', [RegistrationController::class, 'create'])->name('register');
    Route::post('/register', [RegistrationController::class, 'store'])->name('register.store');
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/about', [HomeController::class, 'Aboutus'])->name('about');

Route::middleware('auth:frontend')->group(function () {
    Route::get('/profile', [RegistrationController::class, 'profile'])->name('profile');
    Route::get('/profile/edit', [RegistrationController::class, 'editProfile'])->name('profile.edit');
    Route::post('/profile/update', [RegistrationController::class, 'updateProfile'])->name('profile.update');
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
      
        // Login history
        Route::get('/login-history', [LoginController::class, 'loginHistory'])->name('login.history');

        // Subcategory Module
Route::get('/subcategories', [SubcategoryController::class, 'index'])->name('subcategory.index');
Route::post('/subcategories', [SubcategoryController::class, 'store'])->name('subcategory.store');
Route::get('/subcategories/{subcategory}/edit', [SubcategoryController::class, 'edit'])->name('subcategory.edit');
Route::post('/subcategories/{subcategory}/update', [SubcategoryController::class, 'update'])->name('subcategory.update');
Route::delete('/subcategories/{subcategory}/delete', [SubcategoryController::class, 'destroy'])->name('subcategory.delete');

//unit module
Route::get('/units', [UnitController::class, 'index'])->name('units.index');
Route::post('/units', [UnitController::class, 'store'])->name('units.store');
Route::get('/units/{id}/edit', [UnitController::class, 'edit'])->name('units.edit');
Route::put('/units/{id}', [UnitController::class, 'update'])->name('units.update');
Route::delete('/units/{id}/delete', [UnitController::class, 'delete'])->name('units.delete');


/* Food */
Route::get('/foods', [FoodController::class, 'index'])->name('foods.index');
Route::get('/foods/create', [FoodController::class, 'create'])->name('foods.create');
Route::post('/foods/store', [FoodController::class, 'store'])->name('foods.store');
Route::get('/foods/{id}/edit', [FoodController::class, 'edit'])->name('foods.edit');
Route::put('/foods/{id}/update', [FoodController::class, 'update'])->name('foods.update');
Route::delete('/foods/{id}/delete', [FoodController::class, 'delete'])->name('foods.delete');
Route::get('/foods/{food}', [FoodController::class, 'show'])->name('foods.show');       
});
