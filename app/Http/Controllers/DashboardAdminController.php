<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardAdminController extends Controller
{
    //

    public function index()
    {
        // Stats de base
        $stats = [
            'products' => Product::count(),
            'orders' => Order::count(),
            'customers' => User::where('role', 'user')->count(),
            'revenue' => Order::where('status', 'delivered')->sum('total_price'),
        ];

        // Statistiques avancées
        $advancedStats = [
            // CA par mois sur l'année en cours
            'monthlyRevenue' => Order::selectRaw('
                    MONTH(created_at) as month,
                    SUM(total_price) as revenue
                ')
                ->where('status', 'delivered')
                ->whereYear('created_at', date('Y'))
                ->groupBy('month')
                ->orderBy('month')
                ->get(),

            // Produits les plus vendus
            'topProducts' => OrderItem::selectRaw('
                    products.name,
                    SUM(order_items.quantity) as total_sold,
                    SUM(order_items.quantity * order_items.unit_price) as revenue
                ')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->groupBy('products.name')
                ->orderByDesc('total_sold')
                ->limit(5)
                ->get(),

            // Evolution des commandes
            'ordersTrend' => Order::selectRaw('
                    DATE_FORMAT(created_at, "%Y-%m") as month,
                    COUNT(*) as count
                ')
                ->where('created_at', '>', now()->subMonths(12))
                ->groupBy('month')
                ->orderBy('month')
                ->get(),

            // Répartition par statut
            'ordersByStatus' => Order::selectRaw('
                    status,
                    COUNT(*) as count
                ')
                ->groupBy('status')
                ->get(),

            // Clients avec plus d'achats
            'topCustomers' => Order::selectRaw('
                    users.name,
                    COUNT(orders.id) as order_count,
                    SUM(orders.total_price) as total_spent
                ')
                ->join('users', 'orders.user_id', '=', 'users.id')
                ->whereNotNull('user_id')
                ->groupBy('users.name')
                ->orderByDesc('total_spent')
                ->limit(5)
                ->get(),
        ];

        $recentOrders = Order::with(['user', 'guest', 'address'])
            ->latest()
            ->take(5)
            ->get();

        $lowStockProducts = Product::where('stock', '<', 5)->get();

        return inertia('Admin/Dashboard', [
            'stats' => $stats,
            'advancedStats' => $advancedStats,
            'recentOrders' => $recentOrders,
            'lowStockProducts' => $lowStockProducts,
            'currentYear' => date('Y'),
        ]);
    }
}
