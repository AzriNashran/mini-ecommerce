<?php

namespace App\Exports;

use App\Models\Order;
use App\Models\OrderItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Carbon\Carbon;

class ReportExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithColumnFormatting, WithCustomStartCell, WithTitle, WithEvents
{
    protected $start;
    protected $end;
    protected $summaryData;
    protected $orderNumbersMap = [];

    public function __construct($start = null, $end = null)
    {
        $this->start = $start ? Carbon::parse($start)->startOfDay() : null;
        $this->end = $end ? Carbon::parse($end)->endOfDay() : null;
        $this->prepareSummaryData();
    }

    protected function prepareSummaryData()
    {
        //total orders
        $totalOrdersQuery = Order::query();
        if ($this->start && $this->end) {
            $totalOrdersQuery->whereBetween('order_date', [$this->start->toDateString(), $this->end->toDateString()]);
        }
        $totalOrders = $totalOrdersQuery->count();

        //total revenue
        $totalRevenueQuery = Order::query();
        if ($this->start && $this->end) {
            $totalRevenueQuery->whereBetween('order_date', [$this->start->toDateString(), $this->end->toDateString()]);
        }
        $totalRevenue = $totalRevenueQuery->sum('total_amount');

        //top 3 products
        $topProductsQuery = OrderItem::selectRaw('product_id, SUM(quantity) as total_qty')
            ->whereHas('order', function ($q) {
                if ($this->start && $this->end) {
                    $q->whereBetween('order_date', [$this->start->toDateString(), $this->end->toDateString()]);
                }
            })
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(3);
        $topProducts = $topProductsQuery->get();
        $topProductsList = $topProducts->map(function ($tp) {
            return $tp->product->name ?? 'N/A';
        })->implode(', ');

        //average order value
        $avgOrderValue = $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0;

        $this->summaryData = [
            'totalOrders' => $totalOrders,
            'totalRevenue' => $totalRevenue,
            'topProducts' => $topProductsList ?: '—',
            'avgOrderValue' => $avgOrderValue,
        ];
    }

    public function collection()
    {
        //order items
        $orderItemsQuery = OrderItem::with(['order.customer', 'product.category'])
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->select('order_items.*');
        
        if ($this->start && $this->end) {
            $orderItemsQuery->whereBetween('orders.order_date', [$this->start->toDateString(), $this->end->toDateString()]);
        }
        
        $orderItems = $orderItemsQuery->orderBy('orders.order_date', 'desc')->get();
        
        $groupedItems = $orderItems->groupBy('order_id');
        
        //export data
        $exportData = collect();
        $rowIndex = 0;
        
        foreach ($groupedItems as $orderId => $items) {
            //order total
            $orderTotal = $items->sum(function($item) {
                return $item->quantity * $item->unit_price;
            });
            $orderNumber = str_pad($orderId, 6, '0', STR_PAD_LEFT);
            $firstItem = $items->first();
            
            foreach ($items as $index => $item) {
                $exportData->push([
                    'order_date' => $index === 0 ? date('Y-m-d', strtotime($firstItem->order->order_date)) : '',
                    'customer' => $index === 0 ? $firstItem->order->customer->name : '',
                    'state' => $index === 0 ? $firstItem->order->customer->state : '',
                    'category' => $item->product->category->name ?? '',
                    'product' => $item->product->name ?? '',
                    'qty' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->quantity * $item->unit_price,
                ]);
                $rowIndex++;
            }
            
            $exportData->push([
                'order_date' => '',
                'customer' => '',
                'state' => '',
                'category' => '',
                'product' => '',
                'qty' => '',
                'unit_price' => '',
                'subtotal' => $orderTotal,
            ]);
            
            $this->orderNumbersMap[$rowIndex] = $orderNumber;
            $rowIndex++;
        }
        
        return $exportData;
    }

    public function headings(): array
    {
        return [
            'Order Date',
            'Customer',
            'State',
            'Category',
            'Product',
            'Qty',
            'Unit Price (RM)',
            'Subtotal (RM)',
        ];
    }

    public function startCell(): string
    {
        return 'A10';
    }

    public function title(): string
    {
        $title = 'Product Order Summary Report';
        
        if ($this->start && $this->end) {
            //date range
            $title .= ' (' . $this->start->format('Y-m-d') . ' to ' . $this->end->format('Y-m-d') . ')';
        }
        
        return $title;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 20,
            'C' => 15,
            'D' => 15,
            'E' => 25,
            'F' => 10,
            'G' => 18,
            'H' => 18,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'G' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'H' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        //header
        $sheet->setCellValue('A1', $this->title());
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        
        $sheet->setCellValue('A2', '');
        
        $sheet->setCellValue('A3', 'Metric');
        $sheet->setCellValue('B3', 'Value');
        $sheet->getStyle('A3:B3')->getFont()->setBold(true);
        $sheet->getStyle('A3:B3')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE0E0E0');
        $sheet->getStyle('A3:B3')->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A3:B3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('A3:B3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        
        //summary rows
        $summaryRows = [
            ['Total Orders', $this->summaryData['totalOrders']],
            ['Total Revenue', 'RM ' . number_format($this->summaryData['totalRevenue'], 2)],
            ['Top 3 Products', $this->summaryData['topProducts']],
            ['Average Order Value', 'RM ' . number_format($this->summaryData['avgOrderValue'], 2)],
        ];
        
        $row = 4;
        foreach ($summaryRows as $summaryRow) {
            $sheet->setCellValue('A' . $row, $summaryRow[0]);
            $sheet->setCellValue('B' . $row, $summaryRow[1]);
            $sheet->getStyle('A' . $row . ':B' . $row)->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row . ':B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('A' . $row . ':B' . $row)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $row++;
        }
        
        $sheet->setCellValue('A8', '');
        $sheet->setCellValue('A9', '');
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                $headerRange = 'A10:H10';
                $sheet->getStyle($headerRange)->getFont()->setBold(true);
                $sheet->getStyle($headerRange)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE0E0E0');
                $sheet->getStyle($headerRange)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle($headerRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                
                $highestRow = $sheet->getHighestRow();
                
                if ($highestRow > 10) {
                    $dataRange = 'A11:H' . $highestRow;
                    $sheet->getStyle($dataRange)->getBorders()->getOutline()
                        ->setBorderStyle(Border::BORDER_THIN);
                    $sheet->getStyle($dataRange)->getBorders()->getInside()
                        ->setBorderStyle(Border::BORDER_THIN);
                    
                    $sheet->getStyle('A11:A' . $highestRow)->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    $sheet->getStyle('B11:B' . $highestRow)->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    $sheet->getStyle('C11:C' . $highestRow)->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    $sheet->getStyle('D11:D' . $highestRow)->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    $sheet->getStyle('E11:E' . $highestRow)->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    $sheet->getStyle('F11:F' . $highestRow)->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('G11:G' . $highestRow)->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle('H11:H' . $highestRow)->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    
                    for ($row = 11; $row <= $highestRow; $row++) {
                        $subtotalCell = $sheet->getCell('H' . $row)->getValue();
                        $productCell = $sheet->getCell('E' . $row)->getValue();
                        
                        if (empty($productCell) && !empty($subtotalCell) && is_numeric($subtotalCell)) {
                            $orderNumber = $this->getOrderNumberForRow($row);
                            
                            $sheet->mergeCells('A' . $row . ':G' . $row);
                            $sheet->setCellValue('A' . $row, 'Total for Order #' . $orderNumber . ':');
                            
                            $sheet->getStyle('A' . $row . ':H' . $row)->getFont()->setBold(true);
                            $sheet->getStyle('A' . $row . ':H' . $row)->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setARGB('FFF0F0F0');
                            $sheet->getStyle('A' . $row)->getAlignment()
                                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                        }
                    }
                }
            },
        ];
    }
    
    protected function getOrderNumberForRow($row)
    {
        //data row index
        $dataRowIndex = $row - 11;
        
        if (isset($this->orderNumbersMap[$dataRowIndex])) {
            return $this->orderNumbersMap[$dataRowIndex];
        }
        
        return '';
    }
}

