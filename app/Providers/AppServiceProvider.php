<?php

namespace App\Providers;
use App\Models\Category;
use Illuminate\Support\Facades\View;
use App\Models\Food;
use Illuminate\Support\Facades\URL;

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
        // Only force HTTPS when using ngrok
    //if (str_contains(config('app.url'), 'ngrok')) {
        //URL::forceScheme('https');
    //}

    View::composer('frontend.partials.header', function ($view) {
        $view->with(
            'categories',
            Category::where('status', 1)->get()
        );
    });

            // ADMIN HEADER (low stock notifications)
        View::composer('backend.partials.header', function ($view) {
            $lowStockFoods = Food::whereColumn('quantity', '<=', 'low_stock_alert')
                ->where('status', 1)
                ->get();

            $view->with('lowStockFoods', $lowStockFoods);
        });
}
}
