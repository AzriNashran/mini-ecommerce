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
    // date range default: last 30 days
    $end = $request->input('end') ? Carbon::parse($request->input('end'))->endOfDay() : Carbon::now()->endOfDay();
    $start = $request->input('start') ? Carbon::parse($request->input('start'))->startOfDay() : Carbon::now()->subDays(30)->startOfDay();

    // Summary: 1) total orders (separate query)
    $totalOrders = Order::whereBetween('order_date', [$start->toDateString(), $end->toDateString()])->count();

    // 2) total revenue
    $totalRevenue = Order::whereBetween('order_date', [$start->toDateString(), $end->toDateString()])->sum('total_amount');

    // 3) top 3 best-selling products (separate query)
    $topProducts = OrderItem::selectRaw('product_id, SUM(quantity) as total_qty')
    ->whereHas('order', function ($q) use ($start, $end) {
      $q->whereBetween('order_date', [$start->toDateString(), $end->toDateString()]);
    })
    ->with('product')
    ->groupBy('product_id')
    ->orderByDesc('total_qty')
    ->limit(3)
    ->get();

    // 4) average order value
    $avgOrderValue = $totalOrders ? round($totalRevenue / $totalOrders, 2) : 0;

    // Detailed table: paginate order items with eager relationships (prevent N+1)
    $orderItems = OrderItem::with(['order.customer', 'product.category'])
    ->join('orders', 'order_items.order_id', '=', 'orders.id')
    ->whereBetween('orders.order_date', [$start->toDateString(), $end->toDateString()])
    ->select('order_items.*')
    ->orderBy('orders.order_date', 'desc')
    ->paginate(15);

    return view('report.index', compact('orderItems','totalOrders','totalRevenue','topProducts','avgOrderValue','start','end'));
  }

  public function export(Request $request)
  {
    $start = $request->input('start') ? Carbon::parse($request->input('start'))->toDateString() : Carbon::now()->subDays(30)->toDateString();
    $end = $request->input('end') ? Carbon::parse($request->input('end'))->toDateString() : Carbon::now()->toDateString();
    $fileName = "product-order-summary-{$start}_to_{$end}.xlsx";
    return Excel::download(new ReportExport($start, $end), $fileName);
  }
}
