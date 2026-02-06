<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Food;
use App\Models\Subcategory;
use App\Models\Unit;
use App\Models\Registration;
use App\Models\DeliveryMan;
use App\Models\DeliveryRun;

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

        /* ================= DELIVERY ================= */
        $totalDeliveryMen = DeliveryMan::count();
        $totalDeliveryRuns = DeliveryRun::count();
        $activeDeliveryRuns = DeliveryRun::where('status', 'on_the_way')->count();
        $completedDeliveryRuns = DeliveryRun::where('status', 'completed')->count();

        return view('backend.pages.dashboard', compact(
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
            'completedDeliveryRuns'
        ));
    }
}
