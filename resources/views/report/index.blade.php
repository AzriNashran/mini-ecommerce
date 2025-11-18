<x-app-layout>
    <x-slot name="header">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Product Order Summary Report
      </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="container py-4">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                  <form class="d-inline" method="get" action="{{ route('report.index') }}">
                    <input type="date" name="start" value="{{ $start->toDateString() }}" />
                    <input type="date" name="end" value="{{ $end->toDateString() }}" />
                    <button class="btn btn-sm btn-primary" type="submit">Search</button>
                  </form>
                  <a href="{{ route('report.export', ['start' => $start->toDateString(), 'end' => $end->toDateString()]) }}" class="btn btn-success btn-sm ms-2">Download Excel</a>
                </div>
              </div>

              <!-- Summary cards -->
              <div class="row mb-4 g-3">
                <div class="col-md-3">
                  <div class="card p-3">
                    <div class="text-muted small">Total Orders</div>
                    <div class="h4">{{ $totalOrders }}</div>
                    <div class="text-muted" style="font-size: 12px;">orders from <strong>{{ $start->format('d-m-Y') }}</strong> to <strong>{{ $end->format('d-m-Y') }}</strong></div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="card p-3">
                    <div class="text-muted small">Total Revenue</div>
                    <div class="h4">RM {{ number_format($totalRevenue,2) }}</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="card p-3">
                    <div class="text-muted small">Avg Order Value</div>
                    <div class="h4">RM {{ number_format($avgOrderValue,2) }}</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="card p-3">
                    <div class="text-muted small">Top 3 Products</div>
                    <div class="small">
                      <ol class="mb-0">
                        @forelse($topProducts as $tp)
                          <li>{{ $tp->product->name ?? 'N/A' }} — {{ $tp->total_qty }}</li>
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
                    <table class="table table-sm table-striped mb-0">
                      <thead>
                        <tr>
                          <th>Order Date</th>
                          <th>Customer</th>
                          <th>State</th>
                          <th>Category</th>
                          <th>Product</th>
                          <th class="text-end">Quantity</th>
                          <th class="text-end">Unit Price</th>
                          <th class="text-end">Subtotal</th>
                        </tr>
                      </thead>
                      <tbody>
                        @forelse($orderItems as $item)
                            <tr>
                              <td>{{ date('d-m-Y', strtotime($item->order->order_date)) }}</td>
                              <td>{{ $item->order->customer->name }}</td>
                              <td>{{ $item->order->customer->state }}</td>
                              <td>{{ $item->product->category->name ?? '' }}</td>
                              <td>{{ $item->product->name ?? '' }}</td>
                              <td class="text-end">{{ $item->quantity }}</td>
                              <td class="text-end">RM {{ number_format($item->unit_price, 2, '.', ',') }}</td>
                              <td class="text-end">RM {{ number_format($item->quantity * $item->unit_price, 2, '.', ',') }}</td>
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

              <div class="text-muted small mt-3">Showing orders from <strong>{{ $start->toDateString() }}</strong> to <strong>{{ $end->toDateString() }}</strong></div>
            </div>
        </div>
    </div>
</x-app-layout>