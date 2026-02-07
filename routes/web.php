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
use App\Http\Controllers\Frontend\MenuController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\OrderController;
use App\Http\Controllers\Backend\DeliveryManController;
use App\Http\Controllers\Backend\DeliveryRunController;
use App\Http\Controllers\Backend\OrderExportController;

// frontend routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'Aboutus'])->name('about');
Route::get('/contact-us', [HomeController::class, 'contactPage'])->name('contact.page');
Route::post('/contact-us', [HomeController::class, 'contactStore'])->name('contact.store');

//menu routes
Route::get('/category/{id}', [MenuController::class, 'show'])->name('category.show');
Route::get('/menu/{subcategory}', [MenuController::class, 'foods'])->name('menu.foods');
Route::get('/food/{food}', [MenuController::class, 'foodDetails'])->name('food.details');

//registration and login routes
Route::get('/register', [RegistrationController::class, 'create'])->name('register');
Route::post('/register', [RegistrationController::class, 'store'])->name('register.store');
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');


// Protected routes for authenticated frontend users
Route::middleware('auth:frontend')->group(function () {

// user profile routes
    Route::get('/profile', [RegistrationController::class, 'profile'])->name('profile');
    Route::get('/profile/edit', [RegistrationController::class, 'editProfile'])->name('profile.edit');
    Route::post('/profile/update', [RegistrationController::class, 'updateProfile'])->name('profile.update');
    
// cart routes
    Route::post('/cart/add/{food}', [MenuController::class, 'addToCart'])->name('cart.add');
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{food}', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/update/{food}', [CartController::class, 'update'])->name('cart.update');
    Route::post('/cart/remove/{food}', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

// order routes
    Route::get('/order/place', [OrderController::class, 'create'])->name('order.place');
    Route::post('/order/store', [OrderController::class, 'store'])->name('order.store');
    Route::get('/order/success/{order}', [OrderController::class, 'success'])->name('order.success');
    Route::get('/profile/order/{order}', [RegistrationController::class, 'viewOrder'])->name('profile.order.view');
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
        Route::post('/login-history/bulk-delete', [LoginController::class, 'bulkDelete'])
    ->name('login.history.bulk.delete');


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


// Inactive foods page
Route::get('/foods/inactive', [FoodController::class, 'inactive'])->name('foods.inactive');

// Activate inactive food
Route::patch('/foods/{id}/activate', [FoodController::class, 'activate'])->name('foods.activate');
Route::get('/orders/export', [OrderExportController::class, 'export'])
    ->name('orders.export');
//order status update
Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
// order payment update
Route::patch('/orders/{order}/payment-paid', [OrderController::class, 'markPaymentPaid'])->name('orders.payment.paid');

/* Food */
Route::get('/foods', [FoodController::class, 'index'])->name('foods.index');
Route::get('/foods/create', [FoodController::class, 'create'])->name('foods.create');
Route::post('/foods/store', [FoodController::class, 'store'])->name('foods.store');
Route::get('/foods/{id}/edit', [FoodController::class, 'edit'])->name('foods.edit');
Route::put('/foods/{id}/update', [FoodController::class, 'update'])->name('foods.update');
Route::delete('/foods/{id}/delete', [FoodController::class, 'delete'])->name('foods.delete');
Route::get('/foods/{food}', [FoodController::class, 'show'])->name('foods.show'); 

// Orders Management
Route::get('/orders', [OrderController::class, 'adminIndex'])->name('orders.index');
Route::get('/orders/{order}', [OrderController::class, 'adminShow'])->name('orders.show');

// Delivery Man Module
Route::get('/delivery-men', [DeliveryManController::class, 'index'])->name('delivery-men.index');
Route::post('/delivery-men', [DeliveryManController::class, 'store'])->name('delivery-men.store');
Route::get('/delivery-men/{deliveryMan}/edit', [DeliveryManController::class, 'edit'])->name('delivery-men.edit');
Route::put('/delivery-men/{deliveryMan}', [DeliveryManController::class, 'update'])->name('delivery-men.update');
Route::delete('/delivery-men/{deliveryMan}/delete', [DeliveryManController::class, 'destroy'])->name('delivery-men.delete');
Route::patch('/delivery-men/{deliveryMan}/status', [DeliveryManController::class, 'toggleStatus'])->name('delivery-men.status');

// Delivery Run
Route::get('/delivery-runs', [DeliveryRunController::class, 'index'])->name('delivery-runs.index');
Route::get('/delivery-runs/create', [DeliveryRunController::class, 'create'])->name('delivery-runs.create');
Route::post('/delivery-runs/store', [DeliveryRunController::class, 'store'])->name('delivery-runs.store');
Route::get('/delivery-runs/{id}/edit', [DeliveryRunController::class, 'edit'])->name('delivery-runs.edit');
Route::put('/delivery-runs/{id}', [DeliveryRunController::class, 'update'])->name('delivery-runs.update');
Route::patch('/delivery-runs/{id}/complete', [DeliveryRunController::class, 'complete'])->name('delivery-runs.complete');
Route::delete('/delivery-runs/{id}/delete', [DeliveryRunController::class, 'destroy'])->name('delivery-runs.delete');
Route::post('/delivery-runs/order-details', [DeliveryRunController::class, 'orderDetails'])->name('delivery-runs.order.details');
Route::get('/delivery-runs/{id}',[DeliveryRunController::class, 'show'])->name('delivery-runs.show');


//contact messages

Route::get('/about-us', [HomeController::class, 'adminContactList'])->name('aboutus.index');
;
});
