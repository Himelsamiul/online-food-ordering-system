<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Backend\AuthController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\CategoryController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\RegistrationController;
use App\Http\Controllers\Frontend\LoginController;
use App\Http\Controllers\Frontend\ForgotPasswordController;
use App\Http\Controllers\Backend\SubcategoryController;
use App\Http\Controllers\Backend\UnitController;
use App\Http\Controllers\Backend\FoodController;
use App\Http\Controllers\Frontend\MenuController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\OrderController;
use App\Http\Controllers\Backend\DeliveryManController;
use App\Http\Controllers\Backend\DeliveryRunController;
use App\Http\Controllers\Backend\OrderExportController;
use App\Http\Controllers\Backend\PosController;
use App\Http\Controllers\Backend\AdminUserController;

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

// registration 2-step email verification
Route::get('/register/verify', [RegistrationController::class, 'showVerify'])->name('register.verify');
Route::post('/register/verify', [RegistrationController::class, 'verify'])->name('register.verify.submit');
Route::post('/register/resend', [RegistrationController::class, 'resendOtp'])->name('register.resend');

Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// forgot / reset password (OTP based)
Route::get('/forgot-password', [ForgotPasswordController::class, 'showRequest'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendCode'])->name('password.email');
Route::get('/reset-password', [ForgotPasswordController::class, 'showReset'])->name('password.reset');
Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');


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

    // Dashboard — available to every admin
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

    // ================= POS ================= (permission: pos)
    Route::middleware('can:pos')->group(function () {
        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::post('/pos/store', [PosController::class, 'store'])->name('pos.store');
        Route::get('/pos/sales', [PosController::class, 'sales'])->name('pos.sales');
        Route::get('/pos/invoice/{id}', [PosController::class, 'invoice'])->name('pos.invoice');
    });

    // ================= Category ================= (permission: categories)
    Route::middleware('can:categories')->group(function () {
        Route::get('/categories', [CategoryController::class, 'index'])->name('category.index');
        Route::post('/categories/store', [CategoryController::class, 'store'])->name('category.store');
        Route::get('/categories/{id}/edit', [CategoryController::class, 'edit'])->name('category.edit');
        Route::post('/categories/{id}/update', [CategoryController::class, 'update'])->name('category.update');
        Route::delete('/categories/{id}/delete', [CategoryController::class, 'destroy'])->name('category.delete');
    });

    // ================= Subcategory ================= (permission: subcategories)
    Route::middleware('can:subcategories')->group(function () {
        Route::get('/subcategories', [SubcategoryController::class, 'index'])->name('subcategory.index');
        Route::post('/subcategories', [SubcategoryController::class, 'store'])->name('subcategory.store');
        Route::get('/subcategories/{subcategory}/edit', [SubcategoryController::class, 'edit'])->name('subcategory.edit');
        Route::post('/subcategories/{subcategory}/update', [SubcategoryController::class, 'update'])->name('subcategory.update');
        Route::delete('/subcategories/{subcategory}/delete', [SubcategoryController::class, 'destroy'])->name('subcategory.delete');
    });

    // ================= Units ================= (permission: units)
    Route::middleware('can:units')->group(function () {
        Route::get('/units', [UnitController::class, 'index'])->name('units.index');
        Route::post('/units', [UnitController::class, 'store'])->name('units.store');
        Route::get('/units/{id}/edit', [UnitController::class, 'edit'])->name('units.edit');
        Route::put('/units/{id}', [UnitController::class, 'update'])->name('units.update');
        Route::delete('/units/{id}/delete', [UnitController::class, 'delete'])->name('units.delete');
    });

    // ================= Food ================= (permission: foods)
    Route::middleware('can:foods')->group(function () {
        Route::get('/foods/inactive', [FoodController::class, 'inactive'])->name('foods.inactive');
        Route::patch('/foods/{id}/activate', [FoodController::class, 'activate'])->name('foods.activate');
        Route::get('/foods', [FoodController::class, 'index'])->name('foods.index');
        Route::get('/foods/create', [FoodController::class, 'create'])->name('foods.create');
        Route::post('/foods/store', [FoodController::class, 'store'])->name('foods.store');
        Route::get('/foods/{id}/edit', [FoodController::class, 'edit'])->name('foods.edit');
        Route::put('/foods/{id}/update', [FoodController::class, 'update'])->name('foods.update');
        Route::delete('/foods/{id}/delete', [FoodController::class, 'delete'])->name('foods.delete');
        Route::get('/foods/{food}', [FoodController::class, 'show'])->name('foods.show');
    });

    // ================= Orders ================= (permission: orders)
    Route::middleware('can:orders')->group(function () {
        Route::get('/orders/export', [OrderExportController::class, 'export'])->name('orders.export');
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
        Route::patch('/orders/{order}/payment-paid', [OrderController::class, 'markPaymentPaid'])->name('orders.payment.paid');
        Route::get('/orders', [OrderController::class, 'adminIndex'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'adminShow'])->name('orders.show');
    });

    // ================= Delivery Men ================= (permission: delivery_men)
    Route::middleware('can:delivery_men')->group(function () {
        Route::get('/delivery-men', [DeliveryManController::class, 'index'])->name('delivery-men.index');
        Route::post('/delivery-men', [DeliveryManController::class, 'store'])->name('delivery-men.store');
        Route::get('/delivery-men/{deliveryMan}/edit', [DeliveryManController::class, 'edit'])->name('delivery-men.edit');
        Route::put('/delivery-men/{deliveryMan}', [DeliveryManController::class, 'update'])->name('delivery-men.update');
        Route::delete('/delivery-men/{deliveryMan}/delete', [DeliveryManController::class, 'destroy'])->name('delivery-men.delete');
        Route::patch('/delivery-men/{deliveryMan}/status', [DeliveryManController::class, 'toggleStatus'])->name('delivery-men.status');
    });

    // ================= Delivery Runs ================= (permission: delivery_runs)
    Route::middleware('can:delivery_runs')->group(function () {
        Route::get('/delivery-runs', [DeliveryRunController::class, 'index'])->name('delivery-runs.index');
        Route::get('/delivery-runs/create', [DeliveryRunController::class, 'create'])->name('delivery-runs.create');
        Route::post('/delivery-runs/store', [DeliveryRunController::class, 'store'])->name('delivery-runs.store');
        Route::get('/delivery-runs/{id}/edit', [DeliveryRunController::class, 'edit'])->name('delivery-runs.edit');
        Route::put('/delivery-runs/{id}', [DeliveryRunController::class, 'update'])->name('delivery-runs.update');
        Route::patch('/delivery-runs/{id}/complete', [DeliveryRunController::class, 'complete'])->name('delivery-runs.complete');
        Route::delete('/delivery-runs/{id}/delete', [DeliveryRunController::class, 'destroy'])->name('delivery-runs.delete');
        Route::post('/delivery-runs/order-details', [DeliveryRunController::class, 'orderDetails'])->name('delivery-runs.order.details');
        Route::get('/delivery-runs/{id}', [DeliveryRunController::class, 'show'])->name('delivery-runs.show');
    });

    // ================= Customers ================= (permission: customers)
    Route::middleware('can:customers')->group(function () {
        Route::get('/registrations', [RegistrationController::class, 'registrations'])->name('registrations');
        Route::delete('/registrations/{id}/delete', [RegistrationController::class, 'deleteRegistration'])->name('registrations.delete');
        Route::get('/login-history', [LoginController::class, 'loginHistory'])->name('login.history');
        Route::post('/login-history/bulk-delete', [LoginController::class, 'bulkDelete'])->name('login.history.bulk.delete');
    });

    // ================= Contact Messages ================= (permission: contact_messages)
    Route::middleware('can:contact_messages')->group(function () {
        Route::get('/about-us', [HomeController::class, 'adminContactList'])->name('aboutus.index');
        Route::delete('/about-us/{id}/delete', [HomeController::class, 'adminContactDelete'])->name('aboutus.delete');
    });

    // ================= Admin Users & Roles ================= (SUPERADMIN ONLY)
    // 'manage-admins' is never a grantable permission, so only superadmin
    // passes (via Gate::before). Normal admins get 403 here.
    Route::middleware('can:manage-admins')->group(function () {
        Route::get('/admin-users', [AdminUserController::class, 'index'])->name('admin-users.index');
        Route::get('/admin-users/create', [AdminUserController::class, 'create'])->name('admin-users.create');
        Route::post('/admin-users', [AdminUserController::class, 'store'])->name('admin-users.store');
        Route::get('/admin-users/{adminUser}/edit', [AdminUserController::class, 'edit'])->name('admin-users.edit');
        Route::put('/admin-users/{adminUser}', [AdminUserController::class, 'update'])->name('admin-users.update');
        Route::delete('/admin-users/{adminUser}/delete', [AdminUserController::class, 'destroy'])->name('admin-users.delete');
    });
});
