<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;

class DashboardController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Display the admin dashboard
     */
    public function index()
    {
        $stats = [
            'users' => [
                'total' => User::count(),
                'active' => User::active()->count(),
                'recent' => User::where('created_at', '>=', now()->subDays(30))->count(),
            ],
            'products' => [
                'total' => Product::count(),
                'active' => Product::active()->count(),
                'out_of_stock' => Product::where('stock_quantity', '<=', 0)->count(),
            ],
            'categories' => [
                'total' => Category::count(),
                'active' => Category::active()->count(),
                'root' => Category::root()->count(),
            ],
        ];

        $recentUsers = User::with('roles')
            ->latest()
            ->limit(5)
            ->get();

        $recentProducts = Product::with('category')
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard.index', compact('stats', 'recentUsers', 'recentProducts'));
    }
}