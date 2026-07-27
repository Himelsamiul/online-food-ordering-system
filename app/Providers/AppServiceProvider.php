<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\User;
use App\Support\AdminNotifications;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerGates();
        $this->registerViewComposers();
    }

    /**
     * Role-based access control.
     *
     * Superadmin bypasses every ability. Everything else is checked against
     * the admin's granted permissions, where the ability string is either an
     * exact permission ("foods.delete") or a module shorthand ("foods")
     * meaning "holds any action here".
     *
     * Returning null lets normal gate resolution continue (→ denied, since we
     * define no explicit gates), so an unknown ability such as 'manage-admins'
     * ends up superadmin-only for free.
     */
    private function registerGates(): void
    {
        Gate::before(function (User $user, string $ability) {
            if ($user->isSuperadmin()) {
                return true;
            }

            return $user->hasPermission($ability) ? true : null;
        });
    }

    private function registerViewComposers(): void
    {
        View::composer('frontend.partials.header', function ($view) {
            $view->with('categories', Category::where('status', 1)->get());
        });

        /*
         * Admin chrome. The header owns the notification bell and the sidebar
         * shows the same numbers as small counters, so both get one shared
         * lookup rather than querying twice per request.
         */
        View::composer(
            ['backend.partials.header', 'backend.partials.sidebar'],
            function ($view) {
                $admin = auth()->user();

                $view->with([
                    'notifications' => AdminNotifications::for($admin),
                    'sidebarCounts' => AdminNotifications::sidebarCounts($admin),
                ]);
            }
        );
    }
}
