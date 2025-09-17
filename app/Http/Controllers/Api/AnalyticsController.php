<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $oneMinuteAgo = $now->copy()->subMinute();
        $totalRevenue = Order::sum('total');
        $topProducts = Order::select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get();
        $recentRevenue = Order::where('created_at', '>=', $oneMinuteAgo)
            ->sum('total');

        $previousRevenue = Order::where('created_at', '<', $oneMinuteAgo)
            ->where('created_at', '>=', $oneMinuteAgo->copy()->subMinute())
            ->sum('total');

        $revenueChange = $previousRevenue > 0 
            ? (($recentRevenue - $previousRevenue) / $previousRevenue) * 100 
            : ($recentRevenue > 0 ? 100 : 0);

        $recentOrdersCount = Order::where('created_at', '>=', $oneMinuteAgo)
            ->count();

        return response()->json([
            'total_revenue' => (float) $totalRevenue,
            'top_products' => $topProducts,
            'revenue_changes_last_minute' => [
                'current_minute_revenue' => (float) $recentRevenue,
                'previous_minute_revenue' => (float) $previousRevenue,
                'percentage_change' => round($revenueChange, 2)
            ],
            'orders_count_last_minute' => $recentOrdersCount,
            'timestamp' => $now->toISOString()
        ]);
    }
}