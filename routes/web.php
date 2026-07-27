<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Backend\AccountRequestController as AdminAccountRequestController;
use App\Http\Controllers\Backend\ActivityLogController;
use App\Http\Controllers\Backend\AdminActivationRequestController;
use App\Http\Controllers\Backend\AdminLoginHistoryController;
use App\Http\Controllers\Backend\AdminUserController;
use App\Http\Controllers\Backend\Auth\ForgotPasswordController as AdminForgotPasswordController;
use App\Http\Controllers\Backend\Auth\PasswordAssistanceController;
use App\Http\Controllers\Backend\Auth\ResetLinkController as AdminResetLinkController;
use App\Http\Controllers\Backend\AuthController;
use App\Http\Controllers\Backend\CategoryController;
use App\Http\Controllers\Backend\ChatController as AdminChatController;
use App\Http\Controllers\Backend\CouponController;
use App\Http\Controllers\Backend\CustomerLoginHistoryController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\DeliveryManController;
use App\Http\Controllers\Backend\DeliveryRunController;
use App\Http\Controllers\Backend\FoodController;
use App\Http\Controllers\Backend\NotificationController;
use App\Http\Controllers\Backend\OrderExportController;
use App\Http\Controllers\Backend\PasswordResetRequestController;
use App\Http\Controllers\Backend\PosController;
use App\Http\Controllers\Backend\PromotionController;
use App\Http\Controllers\Backend\SubcategoryController;
use App\Http\Controllers\Backend\UnitController;
use App\Http\Controllers\Frontend\AccountRequestController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\ChatController;
use App\Http\Controllers\Frontend\CouponController as FrontendCouponController;
use App\Http\Controllers\Frontend\ForgotPasswordController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\LoginController;
use App\Http\Controllers\Frontend\MenuController;
use App\Http\Controllers\Frontend\OrderController;
use App\Http\Controllers\Frontend\RegistrationController;
use App\Http\Controllers\Frontend\ResetLinkController;

/*
|--------------------------------------------------------------------------
| Storefront
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'Aboutus'])->name('about');
Route::get('/contact-us', [HomeController::class, 'contactPage'])->name('contact.page');
Route::post('/contact-us', [HomeController::class, 'contactStore'])->name('contact.store');

// menu
Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');
Route::get('/food/{food}', [MenuController::class, 'foodDetails'])->name('food.details');

// legacy drill-down urls — both redirect into /menu with the filter pre-applied
Route::get('/category/{id}', [MenuController::class, 'show'])->name('category.show');
Route::get('/menu/{subcategory}', [MenuController::class, 'foods'])->name('menu.foods');

/*
|--------------------------------------------------------------------------
| Customer authentication
|--------------------------------------------------------------------------
|
| The throttles here are a second line of defence: the controllers already
| rate-limit per email, this bounds a single IP hammering many addresses.
|
*/

Route::get('/register', [RegistrationController::class, 'create'])->name('register');
Route::post('/register', [RegistrationController::class, 'store'])
    ->middleware('throttle:10,1')->name('register.store');
Route::get('/register/verify', [RegistrationController::class, 'showVerify'])->name('register.verify');
Route::post('/register/verify', [RegistrationController::class, 'verify'])
    ->middleware('throttle:20,1')->name('register.verify.submit');
Route::post('/register/resend', [RegistrationController::class, 'resendOtp'])
    ->middleware('throttle:6,1')->name('register.resend');

Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])
    ->middleware('throttle:20,1')->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
 * Self-service password reset: email → code → new password.
 * Each step has its own session gate; see ForgotPasswordController.
 */
Route::get('/forgot-password', [ForgotPasswordController::class, 'showRequest'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendCode'])
    ->middleware('throttle:10,1')->name('password.email');

Route::get('/forgot-password/verify', [ForgotPasswordController::class, 'showVerify'])->name('password.verify');
Route::post('/forgot-password/verify', [ForgotPasswordController::class, 'verify'])
    ->middleware('throttle:20,1')->name('password.verify.submit');
Route::post('/forgot-password/resend', [ForgotPasswordController::class, 'resend'])
    ->middleware('throttle:6,1')->name('password.resend');

Route::get('/reset-password', [ForgotPasswordController::class, 'showReset'])->name('password.reset');
Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])
    ->middleware('throttle:10,1')->name('password.update');

/*
 * Redeeming a reset link an admin issued. `signed` rejects a tampered or
 * expired URL before the controller ever runs.
 */
Route::get('/password/reset-link', [ResetLinkController::class, 'show'])
    ->middleware('signed')->name('password.reset.link');
Route::post('/password/reset-link', [ResetLinkController::class, 'update'])
    ->middleware('throttle:10,1')->name('password.reset.link.update');

/*
 * "I can't get in, please help" — public on purpose, since everyone who needs
 * these forms is by definition locked out. Both validate that the address
 * given is exactly the one on the account.
 */
Route::get('/account-help/{type?}', [AccountRequestController::class, 'create'])
    ->whereIn('type', ['password', 'activation'])
    ->name('account.help');
Route::post('/account-help/password', [AccountRequestController::class, 'storePasswordRequest'])
    ->middleware('throttle:6,60')->name('account.help.password');
Route::post('/account-help/activation', [AccountRequestController::class, 'storeActivationRequest'])
    ->middleware('throttle:6,60')->name('account.help.activation');

/*
|--------------------------------------------------------------------------
| Signed-in customers
|--------------------------------------------------------------------------
*/

Route::middleware('auth:frontend')->group(function () {
    Route::get('/profile', [RegistrationController::class, 'profile'])->name('profile');
    Route::get('/profile/edit', [RegistrationController::class, 'editProfile'])->name('profile.edit');
    Route::post('/profile/update', [RegistrationController::class, 'updateProfile'])->name('profile.update');

    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{food}', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/update/{food}', [CartController::class, 'update'])->name('cart.update');
    Route::post('/cart/remove/{food}', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

    Route::post('/coupon/apply', [FrontendCouponController::class, 'apply'])->name('coupon.apply');
    Route::post('/coupon/remove', [FrontendCouponController::class, 'remove'])->name('coupon.remove');

    Route::get('/order/place', [OrderController::class, 'create'])->name('order.place');
    Route::post('/order/store', [OrderController::class, 'store'])->name('order.store');
    Route::get('/order/success/{order}', [OrderController::class, 'success'])->name('order.success');

    // stripe hosted checkout return urls
    Route::get('/order/payment/return/{order}', [OrderController::class, 'stripeReturn'])->name('order.payment.return');
    Route::get('/order/payment/cancel/{order}', [OrderController::class, 'stripeCancel'])->name('order.payment.cancel');

    Route::get('/profile/order/{order}', [RegistrationController::class, 'viewOrder'])->name('profile.order.view');

    /*
     * Live support chat. Inside auth:frontend on purpose — the widget renders
     * for anonymous visitors but only ever offers them the login page, and
     * there is no endpoint here they could reach without a session.
     */
    Route::get('/support/chat/poll', [ChatController::class, 'poll'])
        ->middleware('throttle:120,1')->name('chat.poll');
    Route::post('/support/chat/send', [ChatController::class, 'send'])
        ->middleware('throttle:' . config('security.chat.rate_per_minute', 20) . ',1')->name('chat.send');
});

/*
|--------------------------------------------------------------------------
| Admin — public
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:20,1')->name('login.submit');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    /*
     * Self-service reset — SUPER ADMIN ONLY. Every other admin is sent to the
     * assistance request below, so a compromised staff inbox cannot be turned
     * into a panel login without a superadmin approving it.
     */
    Route::get('/forgot-password', [AdminForgotPasswordController::class, 'showRequest'])->name('password.request');
    Route::post('/forgot-password', [AdminForgotPasswordController::class, 'sendCode'])
        ->middleware('throttle:10,1')->name('password.email');

    Route::get('/forgot-password/verify', [AdminForgotPasswordController::class, 'showVerify'])->name('password.verify');
    Route::post('/forgot-password/verify', [AdminForgotPasswordController::class, 'verify'])
        ->middleware('throttle:20,1')->name('password.verify.submit');
    Route::post('/forgot-password/resend', [AdminForgotPasswordController::class, 'resend'])
        ->middleware('throttle:6,1')->name('password.resend');

    Route::get('/forgot-password/reset', [AdminForgotPasswordController::class, 'showReset'])->name('password.reset');
    Route::post('/forgot-password/reset', [AdminForgotPasswordController::class, 'reset'])
        ->middleware('throttle:10,1')->name('password.update');

    // Password assistance — every admin who is not a superadmin.
    Route::get('/password-assistance', [PasswordAssistanceController::class, 'createPasswordRequest'])
        ->name('password.assistance');
    Route::post('/password-assistance', [PasswordAssistanceController::class, 'storePasswordRequest'])
        ->middleware('throttle:6,60')->name('password.assistance.store');

    // Reactivation request — for an admin whose account has been switched off.
    Route::get('/activation-request', [PasswordAssistanceController::class, 'createActivationRequest'])
        ->name('activation.request');
    Route::post('/activation-request', [PasswordAssistanceController::class, 'storeActivationRequest'])
        ->middleware('throttle:6,60')->name('activation.request.store');

    // Redeeming the link a superadmin issued.
    Route::get('/password/reset-link', [AdminResetLinkController::class, 'show'])
        ->middleware('signed')->name('password.reset.link');
    Route::post('/password/reset-link', [AdminResetLinkController::class, 'update'])
        ->middleware('throttle:10,1')->name('password.reset.link.update');
});

/*
|--------------------------------------------------------------------------
| Admin — authenticated
|--------------------------------------------------------------------------
|
| Permissions are granular: "<module>.<action>". Read routes are gated on
| .view, which is also what makes the sidebar entry appear, so an admin never
| sees a link they would only get a 403 from. Write routes are gated on
| .create / .edit / .delete independently, so "can edit but not delete" is a
| real, enforceable grant rather than a UI suggestion.
|
| Two abilities are deliberately absent from the permission catalogue and so
| resolve to superadmin-only through Gate::before:
|   manage-admins          — creating and editing admin accounts
|   manage-admin-requests  — approving admin password resets and reactivations
|
| Route ORDER matters where a group has both a literal path and a wildcard
| (/foods/create vs /foods/{food}); the wildcard show routes are therefore
| registered last, after every literal sibling.
|
*/

Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {

    // Dashboard — every admin gets this, it is the landing page after login.
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

    // Notification bell.
    Route::post('/notifications/read', [NotificationController::class, 'markAllRead'])->name('notifications.read');
    Route::get('/notifications/feed', [NotificationController::class, 'feed'])->name('notifications.feed');

    // ================= POS =================
    Route::middleware('can:pos.view')->group(function () {
        Route::get('/pos/sales', [PosController::class, 'sales'])->name('pos.sales');
        Route::get('/pos/invoice/{id}', [PosController::class, 'invoice'])->name('pos.invoice');
    });
    Route::middleware('can:pos.create')->group(function () {
        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::post('/pos/store', [PosController::class, 'store'])->name('pos.store');
    });

    // ================= Categories =================
    Route::middleware('can:categories.view')->group(function () {
        Route::get('/categories', [CategoryController::class, 'index'])->name('category.index');
    });
    Route::middleware('can:categories.create')->group(function () {
        Route::post('/categories/store', [CategoryController::class, 'store'])->name('category.store');
    });
    Route::middleware('can:categories.edit')->group(function () {
        Route::get('/categories/{id}/edit', [CategoryController::class, 'edit'])->name('category.edit');
        Route::post('/categories/{id}/update', [CategoryController::class, 'update'])->name('category.update');
    });
    Route::middleware('can:categories.delete')->group(function () {
        Route::delete('/categories/{id}/delete', [CategoryController::class, 'destroy'])->name('category.delete');
    });

    // ================= Subcategories =================
    Route::middleware('can:subcategories.view')->group(function () {
        Route::get('/subcategories', [SubcategoryController::class, 'index'])->name('subcategory.index');
    });
    Route::middleware('can:subcategories.create')->group(function () {
        Route::post('/subcategories', [SubcategoryController::class, 'store'])->name('subcategory.store');
    });
    Route::middleware('can:subcategories.edit')->group(function () {
        Route::get('/subcategories/{subcategory}/edit', [SubcategoryController::class, 'edit'])->name('subcategory.edit');
        Route::post('/subcategories/{subcategory}/update', [SubcategoryController::class, 'update'])->name('subcategory.update');
    });
    Route::middleware('can:subcategories.delete')->group(function () {
        Route::delete('/subcategories/{subcategory}/delete', [SubcategoryController::class, 'destroy'])->name('subcategory.delete');
    });

    // ================= Units =================
    Route::middleware('can:units.view')->group(function () {
        Route::get('/units', [UnitController::class, 'index'])->name('units.index');
    });
    Route::middleware('can:units.create')->group(function () {
        Route::post('/units', [UnitController::class, 'store'])->name('units.store');
    });
    Route::middleware('can:units.edit')->group(function () {
        Route::get('/units/{id}/edit', [UnitController::class, 'edit'])->name('units.edit');
        Route::put('/units/{id}', [UnitController::class, 'update'])->name('units.update');
    });
    Route::middleware('can:units.delete')->group(function () {
        Route::delete('/units/{id}/delete', [UnitController::class, 'delete'])->name('units.delete');
    });

    // ================= Foods =================
    Route::middleware('can:foods.create')->group(function () {
        Route::get('/foods/create', [FoodController::class, 'create'])->name('foods.create');
        Route::post('/foods/store', [FoodController::class, 'store'])->name('foods.store');
    });
    Route::middleware('can:foods.edit')->group(function () {
        Route::get('/foods/{id}/edit', [FoodController::class, 'edit'])->name('foods.edit');
        Route::put('/foods/{id}/update', [FoodController::class, 'update'])->name('foods.update');
        Route::patch('/foods/{id}/activate', [FoodController::class, 'activate'])->name('foods.activate');
    });
    Route::middleware('can:foods.delete')->group(function () {
        Route::delete('/foods/{id}/delete', [FoodController::class, 'delete'])->name('foods.delete');
    });
    Route::middleware('can:foods.view')->group(function () {
        Route::get('/foods/inactive', [FoodController::class, 'inactive'])->name('foods.inactive');
        Route::get('/foods', [FoodController::class, 'index'])->name('foods.index');
        // wildcard last — otherwise /foods/create would resolve to it
        Route::get('/foods/{food}', [FoodController::class, 'show'])->name('foods.show');
    });

    // ================= Coupons =================
    Route::middleware('can:coupons.view')->group(function () {
        Route::get('/coupons', [CouponController::class, 'index'])->name('coupons.index');
    });
    Route::middleware('can:coupons.create')->group(function () {
        Route::post('/coupons', [CouponController::class, 'store'])->name('coupons.store');
    });
    Route::middleware('can:coupons.edit')->group(function () {
        Route::get('/coupons/{coupon}/edit', [CouponController::class, 'edit'])->name('coupons.edit');
        Route::put('/coupons/{coupon}', [CouponController::class, 'update'])->name('coupons.update');
    });
    Route::middleware('can:coupons.delete')->group(function () {
        Route::delete('/coupons/{coupon}/delete', [CouponController::class, 'destroy'])->name('coupons.delete');
    });

    // ================= Promo banners =================
    Route::middleware('can:promotions.view')->group(function () {
        Route::get('/promotions', [PromotionController::class, 'index'])->name('promotions.index');
    });
    Route::middleware('can:promotions.create')->group(function () {
        Route::post('/promotions', [PromotionController::class, 'store'])->name('promotions.store');
    });
    Route::middleware('can:promotions.edit')->group(function () {
        Route::get('/promotions/{promotion}/edit', [PromotionController::class, 'edit'])->name('promotions.edit');
        Route::put('/promotions/{promotion}', [PromotionController::class, 'update'])->name('promotions.update');
    });
    Route::middleware('can:promotions.delete')->group(function () {
        Route::delete('/promotions/{promotion}/delete', [PromotionController::class, 'destroy'])->name('promotions.delete');
    });

    // ================= Orders =================
    Route::middleware('can:orders.edit')->group(function () {
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
        Route::patch('/orders/{order}/payment-paid', [OrderController::class, 'markPaymentPaid'])->name('orders.payment.paid');
    });
    Route::middleware('can:orders.view')->group(function () {
        Route::get('/orders/export', [OrderExportController::class, 'export'])->name('orders.export');
        Route::get('/orders', [OrderController::class, 'adminIndex'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'adminShow'])->name('orders.show');
    });

    // ================= Delivery men =================
    Route::middleware('can:delivery_men.view')->group(function () {
        Route::get('/delivery-men', [DeliveryManController::class, 'index'])->name('delivery-men.index');
    });
    Route::middleware('can:delivery_men.create')->group(function () {
        Route::post('/delivery-men', [DeliveryManController::class, 'store'])->name('delivery-men.store');
    });
    Route::middleware('can:delivery_men.edit')->group(function () {
        Route::get('/delivery-men/{deliveryMan}/edit', [DeliveryManController::class, 'edit'])->name('delivery-men.edit');
        Route::put('/delivery-men/{deliveryMan}', [DeliveryManController::class, 'update'])->name('delivery-men.update');
        Route::patch('/delivery-men/{deliveryMan}/status', [DeliveryManController::class, 'toggleStatus'])->name('delivery-men.status');
    });
    Route::middleware('can:delivery_men.delete')->group(function () {
        Route::delete('/delivery-men/{deliveryMan}/delete', [DeliveryManController::class, 'destroy'])->name('delivery-men.delete');
    });

    // ================= Delivery runs =================
    Route::middleware('can:delivery_runs.create')->group(function () {
        Route::get('/delivery-runs/create', [DeliveryRunController::class, 'create'])->name('delivery-runs.create');
        Route::post('/delivery-runs/store', [DeliveryRunController::class, 'store'])->name('delivery-runs.store');
    });
    Route::middleware('can:delivery_runs.edit')->group(function () {
        Route::get('/delivery-runs/{id}/edit', [DeliveryRunController::class, 'edit'])->name('delivery-runs.edit');
        Route::put('/delivery-runs/{id}', [DeliveryRunController::class, 'update'])->name('delivery-runs.update');
        Route::patch('/delivery-runs/{id}/complete', [DeliveryRunController::class, 'complete'])->name('delivery-runs.complete');
    });
    Route::middleware('can:delivery_runs.delete')->group(function () {
        Route::delete('/delivery-runs/{id}/delete', [DeliveryRunController::class, 'destroy'])->name('delivery-runs.delete');
    });
    Route::middleware('can:delivery_runs.view')->group(function () {
        Route::get('/delivery-runs', [DeliveryRunController::class, 'index'])->name('delivery-runs.index');
        Route::post('/delivery-runs/order-details', [DeliveryRunController::class, 'orderDetails'])->name('delivery-runs.order.details');
        // wildcard last
        Route::get('/delivery-runs/{id}', [DeliveryRunController::class, 'show'])->name('delivery-runs.show');
    });

    // ================= Customers =================
    Route::middleware('can:customers.view')->group(function () {
        Route::get('/registrations', [RegistrationController::class, 'registrations'])->name('registrations');
    });
    Route::middleware('can:customers.edit')->group(function () {
        Route::patch('/registrations/{id}/status', [RegistrationController::class, 'toggleStatus'])->name('registrations.status');
    });
    Route::middleware('can:customers.delete')->group(function () {
        Route::delete('/registrations/{id}/delete', [RegistrationController::class, 'deleteRegistration'])->name('registrations.delete');
    });

    // ================= Customer account requests =================
    Route::middleware('can:account_requests.view')->group(function () {
        Route::get('/account-requests', [AdminAccountRequestController::class, 'index'])->name('account-requests.index');
        Route::get('/account-requests/export', [AdminAccountRequestController::class, 'export'])->name('account-requests.export');
    });
    Route::middleware('can:account_requests.edit')->group(function () {
        Route::post('/account-requests/{accountRequest}/approve', [AdminAccountRequestController::class, 'approve'])->name('account-requests.approve');
        Route::post('/account-requests/{accountRequest}/reject', [AdminAccountRequestController::class, 'reject'])->name('account-requests.reject');
    });
    Route::middleware('can:account_requests.delete')->group(function () {
        Route::post('/account-requests/bulk-delete', [AdminAccountRequestController::class, 'bulkDelete'])->name('account-requests.bulk-delete');
        Route::delete('/account-requests/{accountRequest}/delete', [AdminAccountRequestController::class, 'destroy'])->name('account-requests.delete');
    });

    // ================= Live support chat =================
    Route::middleware('can:chat.view')->group(function () {
        Route::get('/chat', [AdminChatController::class, 'index'])->name('chat.index');
        Route::get('/chat/poll', [AdminChatController::class, 'poll'])
            ->middleware('throttle:180,1')->name('chat.poll');
    });
    Route::middleware('can:chat.create')->group(function () {
        Route::post('/chat/{conversation}/send', [AdminChatController::class, 'send'])
            ->middleware('throttle:' . config('security.chat.rate_per_minute', 20) . ',1')->name('chat.send');
    });
    Route::middleware('can:chat.edit')->group(function () {
        Route::patch('/chat/{conversation}/status', [AdminChatController::class, 'updateStatus'])->name('chat.status');
    });
    Route::middleware('can:chat.delete')->group(function () {
        Route::delete('/chat/{conversation}/delete', [AdminChatController::class, 'destroy'])->name('chat.delete');
    });

    // ================= Contact messages =================
    Route::middleware('can:contact_messages.view')->group(function () {
        Route::get('/about-us', [HomeController::class, 'adminContactList'])->name('aboutus.index');
    });
    Route::middleware('can:contact_messages.delete')->group(function () {
        Route::delete('/about-us/{id}/delete', [HomeController::class, 'adminContactDelete'])->name('aboutus.delete');
    });

    // ================= Customer login history =================
    Route::middleware('can:login_history.view')->group(function () {
        Route::get('/login-history', [CustomerLoginHistoryController::class, 'index'])->name('login.history');
        Route::get('/login-history/export', [CustomerLoginHistoryController::class, 'export'])->name('login.history.export');
    });
    Route::middleware('can:login_history.delete')->group(function () {
        Route::post('/login-history/bulk-delete', [CustomerLoginHistoryController::class, 'bulkDelete'])->name('login.history.bulk.delete');
    });

    // ================= Admin login history =================
    Route::middleware('can:admin_login_history.view')->group(function () {
        Route::get('/admin-login-history', [AdminLoginHistoryController::class, 'index'])->name('admin-login-history.index');
        Route::get('/admin-login-history/export', [AdminLoginHistoryController::class, 'export'])->name('admin-login-history.export');
    });
    Route::middleware('can:admin_login_history.delete')->group(function () {
        Route::post('/admin-login-history/bulk-delete', [AdminLoginHistoryController::class, 'bulkDelete'])->name('admin-login-history.bulk-delete');
    });

    // ================= Audit trail =================
    Route::middleware('can:activity_log.view')->group(function () {
        Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');
        Route::get('/activity-log/export', [ActivityLogController::class, 'export'])->name('activity-log.export');
        Route::get('/activity-log/{activityLog}', [ActivityLogController::class, 'show'])->name('activity-log.show');
    });
    Route::middleware('can:activity_log.delete')->group(function () {
        Route::post('/activity-log/bulk-delete', [ActivityLogController::class, 'bulkDelete'])->name('activity-log.bulk-delete');
        Route::post('/activity-log/prune', [ActivityLogController::class, 'prune'])->name('activity-log.prune');
        Route::delete('/activity-log/{activityLog}/delete', [ActivityLogController::class, 'destroy'])->name('activity-log.delete');
    });

    /*
     * ================= Admin requests (SUPERADMIN ONLY) =================
     * 'manage-admin-requests' is never a grantable permission, so only a
     * superadmin passes it via Gate::before. Approving a password request
     * issues a signed single-use link; no password is ever generated or sent.
     */
    Route::middleware('can:manage-admin-requests')->group(function () {
        Route::get('/password-reset-requests', [PasswordResetRequestController::class, 'index'])->name('password-reset-requests.index');
        Route::get('/password-reset-requests/export', [PasswordResetRequestController::class, 'export'])->name('password-reset-requests.export');
        Route::post('/password-reset-requests/bulk-delete', [PasswordResetRequestController::class, 'bulkDelete'])->name('password-reset-requests.bulk-delete');
        Route::post('/password-reset-requests/{accountRequest}/approve', [PasswordResetRequestController::class, 'approve'])->name('password-reset-requests.approve');
        Route::post('/password-reset-requests/{accountRequest}/reject', [PasswordResetRequestController::class, 'reject'])->name('password-reset-requests.reject');
        Route::delete('/password-reset-requests/{accountRequest}/delete', [PasswordResetRequestController::class, 'destroy'])->name('password-reset-requests.delete');

        Route::get('/admin-activation-requests', [AdminActivationRequestController::class, 'index'])->name('admin-activation-requests.index');
        Route::get('/admin-activation-requests/export', [AdminActivationRequestController::class, 'export'])->name('admin-activation-requests.export');
        Route::post('/admin-activation-requests/bulk-delete', [AdminActivationRequestController::class, 'bulkDelete'])->name('admin-activation-requests.bulk-delete');
        Route::post('/admin-activation-requests/{accountRequest}/approve', [AdminActivationRequestController::class, 'approve'])->name('admin-activation-requests.approve');
        Route::post('/admin-activation-requests/{accountRequest}/reject', [AdminActivationRequestController::class, 'reject'])->name('admin-activation-requests.reject');
        Route::delete('/admin-activation-requests/{accountRequest}/delete', [AdminActivationRequestController::class, 'destroy'])->name('admin-activation-requests.delete');
    });

    // ================= Admin users & roles (SUPERADMIN ONLY) =================
    Route::middleware('can:manage-admins')->group(function () {
        Route::get('/admin-users', [AdminUserController::class, 'index'])->name('admin-users.index');
        Route::get('/admin-users/create', [AdminUserController::class, 'create'])->name('admin-users.create');
        Route::post('/admin-users', [AdminUserController::class, 'store'])->name('admin-users.store');
        Route::get('/admin-users/{adminUser}/edit', [AdminUserController::class, 'edit'])->name('admin-users.edit');
        Route::put('/admin-users/{adminUser}', [AdminUserController::class, 'update'])->name('admin-users.update');
        Route::patch('/admin-users/{adminUser}/status', [AdminUserController::class, 'toggleStatus'])->name('admin-users.status');
        Route::delete('/admin-users/{adminUser}/delete', [AdminUserController::class, 'destroy'])->name('admin-users.delete');
    });
});
