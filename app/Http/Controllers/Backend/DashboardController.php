<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AccountRequest;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Order;
use App\Models\Food;
use App\Models\Subcategory;
use App\Models\Unit;
use App\Models\Registration;
use App\Models\DeliveryMan;
use App\Models\DeliveryRun;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function dashboard()
    {
        /* ================= USERS ================= */
        $totalUsers = Registration::count();

        /* ================= ORDERS ================= */
        $totalOrders = Order::count();

        $pendingOrders        = Order::where('order_status', 'pending')->count();
        $cookingOrders        = Order::where('order_status', 'cooking')->count();
        $outForDeliveryOrders = Order::where('order_status', 'out_for_delivery')->count();
        $deliveredOrders      = Order::where('order_status', 'delivered')->count();
        $cancelledOrders      = Order::where('order_status', 'cancelled')->count();

        /* ================= PAYMENTS (IMPORTANT) ================= */
        // total revenue (paid only)
        $totalOrderAmount = Order::where('payment_status', 'paid')
            ->sum('total_amount');

        /* ================= COD (payment_status based) ================= */
        $codOrdersCount = Order::where('payment_method', 'cod')->count();

        $codTotalAmount = Order::where('payment_method', 'cod')
            ->sum('total_amount');

        $codCollectedAmount = Order::where('payment_method', 'cod')
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        $codPendingAmount = Order::where('payment_method', 'cod')
            ->where('payment_status', 'pending')
            ->sum('total_amount');

        /* ================= STRIPE ================= */
        $stripeOrdersCount = Order::where('payment_method', 'stripe')->count();

        $stripeTotalAmount = Order::where('payment_method', 'stripe')
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        /* ================= INVENTORY ================= */
        $totalFoods         = Food::count();
        $totalSubcategories = Subcategory::count();
        $totalUnits         = Unit::count();
        $totalcontactmessage= ContactMessage::count();
        $totalcategory         = Category::count();

        
        /* ================= DELIVERY ================= */
        $totalDeliveryMen = DeliveryMan::count();
        $totalDeliveryRuns = DeliveryRun::count();
        $activeDeliveryRuns = DeliveryRun::where('status', 'on_the_way')->count();
        $completedDeliveryRuns = DeliveryRun::where('status', 'completed')->count();

        /* ================= CUSTOMER ACCOUNTS ================= */
        $activeUsers   = Registration::where('is_active', true)->count();
        $inactiveUsers = Registration::where('is_active', false)->count();

        /* ================= REVENUE TREND (last 14 days) ================= */
        // Charted on the dashboard. Built from a date range rather than the
        // query result so days with no orders still show as zero instead of
        // being silently skipped, which would distort the line.
        $trend = collect();

        for ($i = 13; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);

            $trend->push([
                'label'  => $day->format('d M'),
                'orders' => Order::whereDate('created_at', $day)->count(),
                'amount' => (float) Order::whereDate('created_at', $day)
                    ->where('payment_status', 'paid')
                    ->sum('total_amount'),
            ]);
        }

        /* ================= LIVE PANELS (permission aware) ================= */
        $admin = auth()->user();

        $recentOrders = $admin->hasPermission('orders.view')
            ? Order::latest()->limit(6)->get()
            : collect();

        $recentActivity = $admin->hasPermission('activity_log.view')
            ? ActivityLog::latest('id')->limit(8)->get()
            : collect();

        $pendingRequests = $admin->hasPermission('account_requests.view')
            ? AccountRequest::pending()->latest()->limit(5)->get()
            : collect();

        $lowStockFoods = $admin->hasPermission('foods.view')
            ? Food::whereColumn('quantity', '<=', 'low_stock_alert')
                ->where('status', 1)
                ->orderBy('quantity')
                ->limit(6)
                ->get()
            : collect();

        return view('backend.pages.dashboard', compact(
            'trend',
            'recentOrders',
            'recentActivity',
            'pendingRequests',
            'lowStockFoods',
            'activeUsers',
            'inactiveUsers',
            'totalUsers',
            'totalOrders',
            'pendingOrders',
            'cookingOrders',
            'outForDeliveryOrders',
            'deliveredOrders',
            'cancelledOrders',
            'totalOrderAmount',
            'codOrdersCount',
            'codTotalAmount',
            'codCollectedAmount',
            'codPendingAmount',
            'stripeOrdersCount',
            'stripeTotalAmount',
            'totalFoods',
            'totalSubcategories',
            'totalUnits',
            'totalDeliveryMen',
            'totalDeliveryRuns',
            'activeDeliveryRuns',
            'completedDeliveryRuns',
            'totalcontactmessage',
            'totalcategory'
        ));
    }
}
