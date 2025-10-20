<?php

namespace App\Infrastructure\Repositories;

use App\Core\Interfaces\IReportRepository;
use App\Core\Entities\OrderItem;
use App\Core\Entities\Order;
use Illuminate\Support\Facades\DB;

class ReportRepository implements IReportRepository
{
    public function getSalesSummary()
    {
        $totalRevenue = Order::where('status', 'completed')->sum('total_price');
        $totalOrders = Order::where('status', 'completed')->count();
        $totalItems = OrderItem::sum('quantity');

        $topProducts = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select('products.name', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->groupBy('products.name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        return [
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'total_items_sold' => $totalItems,
            'top_products' => $topProducts
        ];
    }
}
