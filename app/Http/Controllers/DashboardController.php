<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    //
    public function index()
    {
        $orders = Order::where('user_id', Auth::id())
            ->latest()
            ->take(3)
            ->get();

        $recommendedProducts = Product::inRandomOrder()
            ->take(4)
            ->get();

        return inertia('Dashboard', [
            'orders' => $orders,
            'recommendedProducts' => $recommendedProducts,
        ]);
    }

}
