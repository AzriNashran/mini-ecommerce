<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Exports\ReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ReportController extends Controller
{
  public function index(Request $request)
  {
    $start = $request->input('start') ? Carbon::parse($request->input('start'))->startOfDay() : null;
    $end = $request->input('end') ? Carbon::parse($request->input('end'))->endOfDay() : null;

    //total orders
    $totalOrdersQuery = Order::query();
    if ($start && $end) {
      $totalOrdersQuery->whereBetween('order_date', [$start->toDateString(), $end->toDateString()]);
    }
    $totalOrders = $totalOrdersQuery->count();

    //total revenue
    $totalRevenueQuery = Order::query();
    if ($start && $end) {
      $totalRevenueQuery->whereBetween('order_date', [$start->toDateString(), $end->toDateString()]);
    }
    $totalRevenue = $totalRevenueQuery->sum('total_amount');

    //top 3 products
    $topProductsQuery = OrderItem::selectRaw('product_id, SUM(quantity) as total_qty')
    ->whereHas('order', function ($q) use ($start, $end) {
      if ($start && $end) {
        $q->whereBetween('order_date', [$start->toDateString(), $end->toDateString()]);
      }
    })
    ->with('product')
    ->groupBy('product_id')
    ->orderByDesc('total_qty')
    ->limit(3);
    $topProducts = $topProductsQuery->get();

    //average order value
    $avgOrderValue = $totalOrders ? round($totalRevenue / $totalOrders, 2) : 0;

    //order items
    $orderItemsQuery = OrderItem::with(['order.customer', 'product.category'])
    ->join('orders', 'order_items.order_id', '=', 'orders.id')
    ->select('order_items.*');
    if ($start && $end) {
      $orderItemsQuery->whereBetween('orders.order_date', [$start->toDateString(), $end->toDateString()]);
    }
    $orderItems = $orderItemsQuery->orderBy('orders.order_date', 'desc')->paginate(15);

    return view('report.index', compact('orderItems','totalOrders','totalRevenue','topProducts','avgOrderValue','start','end'));
  }

  public function export(Request $request)
  {
    $start = $request->input('start') ? Carbon::parse($request->input('start'))->toDateString() : null;
    $end = $request->input('end') ? Carbon::parse($request->input('end'))->toDateString() : null;
    
    if ($start && $end) {
      $fileName = "product-order-summary-{$start}_to_{$end}.xlsx";
    } else {
      $fileName = "product-order-summary-all.xlsx";
    }
    
    return Excel::download(new ReportExport($start, $end), $fileName);
  }
}
