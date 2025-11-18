<x-app-layout>
    <x-slot name="header">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Product Order Summary
      </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="container py-4">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                  <form class="d-inline" method="get" action="{{ route('report.index') }}">
                    <input type="date" name="start" value="{{ isset($start) && $start ? $start->toDateString() : '' }}" />
                    <input type="date" name="end" value="{{ isset($end) && $end ? $end->toDateString() : '' }}" />
                    <button class="btn btn-sm btn-primary" type="submit">Search</button>
                  </form>
                  @if(isset($start) && isset($end) && $start && $end)
                    <a href="{{ route('report.index') }}" class="btn btn-sm btn-outline-secondary ms-2">Reset</a>                    
                  @endif
                  <a href="{{ route('report.export', ['start' => isset($start) && $start ? $start->toDateString() : '', 'end' => isset($end) && $end ? $end->toDateString() : '']) }}" class="btn btn-success btn-sm ms-2">Download Excel</a>
                </div>
              </div>

              <!-- Summary cards -->
              <div class="row mb-4 g-3">
                <div class="col-md-3">
                  <div class="card p-3 h-100 text-dark" style="background-color: #9DD4D9;">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                      <div class="opacity-75">Total Orders</div>
                      <i class="fas fa-shopping-cart fa-2x" style="color: #2E7D82;"></i>
                    </div>
                    <div class="h4">{{ $totalOrders }}</div>
                    <div class="small opacity-75" style="font-size: 12px;">
                      @if(isset($start) && isset($end) && $start && $end)
                        orders from <strong>{{ $start->format('d-m-Y') }}</strong> to <strong>{{ $end->format('d-m-Y') }}</strong>
                      @else
                        no date filter
                      @endif
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="card p-3 h-100 text-dark" style="background-color: #C8EBE8;">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                      <div class="opacity-75">Total Revenue</div>
                      <i class="fas fa-dollar-sign fa-2x" style="color: #3D8B85;"></i>
                    </div>
                    <div class="h4">RM {{ number_format($totalRevenue,2) }}</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="card p-3 h-100 text-dark" style="background-color: #D1EDDF;">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                      <div class="opacity-75">Average Order Value</div>
                      <i class="fas fa-chart-line fa-2x" style="color: #4A9D7A;"></i>
                    </div>
                    <div class="h4">RM {{ number_format($avgOrderValue,2) }}</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="card p-3 h-100 text-dark" style="background-color: #ECF5D0;">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                      <div class="opacity-75">Top 3 Products</div>
                      <i class="fas fa-trophy fa-2x" style="color: #7BA83A;"></i>
                    </div>
                    <div class="small">
                      <ol class="mb-0">
                        @forelse($topProducts as $tp)
                          <li><b>{{ $tp->product->name ?? 'N/A' }}</b> — <b>{{ $tp->total_qty }}</b> items sold</li>
                        @empty
                          <li>—</li>
                        @endforelse
                      </ol>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Detailed table -->
              <div class="card">
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-sm mb-0">
                      <thead>
                        <tr>
                          <th>Order Date</th>
                          <th>Customer</th>
                          <th>State</th>
                          <th>Category</th>
                          <th>Product</th>
                          <th class="text-center">Qty</th>
                          <th class="text-end">Unit Price (RM)</th>
                          <th class="text-end">Subtotal (RM)</th>
                        </tr>
                      </thead>
                      <tbody>
                        @php
                          $groupedItems = $orderItems->groupBy('order_id');
                        @endphp
                        @forelse($groupedItems as $orderId => $items)
                          @php
                            $orderTotal = $items->sum(function($item) {
                              return $item->quantity * $item->unit_price;
                            });
                            $orderNumber = str_pad($orderId, 6, '0', STR_PAD_LEFT);
                            $firstItem = $items->first();
                          @endphp
                          @foreach($items as $index => $item)
                            <tr>
                              @if($index === 0)
                                <td>{{ date('Y-m-d', strtotime($firstItem->order->order_date)) }}</td>
                                <td>{{ $firstItem->order->customer->name }}</td>
                                <td>{{ $firstItem->order->customer->state }}</td>
                              @else
                                <td></td>
                                <td></td>
                                <td></td>
                              @endif
                              <td>{{ $item->product->category->name ?? '' }}</td>
                              <td>{{ $item->product->name ?? '' }}</td>
                              <td class="text-center">{{ $item->quantity }}</td>
                              <td class="text-end">{{ number_format($item->unit_price, 2, '.', ',') }}</td>
                              <td class="text-end">{{ number_format($item->quantity * $item->unit_price, 2, '.', ',') }}</td>
                            </tr>
                          @endforeach
                          <tr class="table-secondary">
                            <td colspan="7" class="text-end fw-bold">Total for Order #{{ $orderNumber }}:</td>
                            <td class="text-end fw-bold">RM {{ number_format($orderTotal, 2, '.', ',') }}</td>
                          </tr>
                        @empty
                            <tr>
                              <td colspan="8" class="text-center">No records found</td>
                            </tr>
                        @endforelse
                      </tbody>
                    </table>
                  </div>
                  <!-- Pagination -->
                  <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small">
                      Showing {{ $orderItems->firstItem() ?? 0 }} to {{ $orderItems->lastItem() ?? 0 }} of {{ $orderItems->total() }} results
                    </div>
                    <div>
                      {{ $orderItems->links('pagination::bootstrap-5') }}
                    </div>
                  </div>
                </div>
              </div>

              <div class="text-muted small mt-3">
                @if(isset($start) && isset($end) && $start && $end)
                  Showing orders from <strong>{{ $start->toDateString() }}</strong> to <strong>{{ $end->toDateString() }}</strong>
                @else
                  Showing all orders (no date filter)
                @endif
              </div>
            </div>
        </div>
    </div>
</x-app-layout>