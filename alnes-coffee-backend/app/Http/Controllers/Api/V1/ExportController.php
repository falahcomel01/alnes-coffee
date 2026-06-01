<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ExportController extends Controller
{
    public function exportCsv(Request $request)
    {
        $request->validate([
            'date_from' => ['required', 'date'],
            'date_to'   => ['required', 'date'],
            'type'      => ['required', 'in:orders,products,summary'],
        ]);

        $from = Carbon::parse($request->date_from)->startOfDay();
        $to   = Carbon::parse($request->date_to)->endOfDay();

        return match($request->type) {
            'orders'   => $this->exportOrders($from, $to),
            'products' => $this->exportProducts($from, $to),
            'summary'  => $this->exportSummary($from, $to),
        };
    }

private function exportOrders(Carbon $from, Carbon $to)
{
    $orders = Order::with(['table', 'items.product'])
        ->whereBetween('ordered_at', [$from, $to])
        ->where('payment_status', PaymentStatus::Paid)
        ->orderBy('ordered_at')
        ->get();

    $filename = 'laporan-order-' . $from->format('d-m-Y') . '-sd-' . $to->format('d-m-Y') . '.csv';

    $headers = [
        'Content-Type'        => 'text/csv',
        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
    ];

    $callback = function () use ($orders) {
        $file = fopen('php://output', 'w');
        fputs($file, "\xEF\xBB\xBF");

        fputcsv($file, [
            'Invoice', 'Tanggal', 'Jam', 'Meja', 'Customer',
            'No HP', 'Metode Bayar', 'Subtotal', 'Diskon',
            'Service Fee', 'Pajak', 'Total', 'Status Order',
        ]);

        foreach ($orders as $order) {
            // Handle enum atau string
            $paymentMethod = is_object($order->payment_method)
                ? $order->payment_method->value
                : $order->payment_method;

            $orderStatus = is_object($order->order_status)
                ? $order->order_status->value
                : $order->order_status;

            fputcsv($file, [
                $order->invoice_number,
                $order->ordered_at->format('d/m/Y'),
                $order->ordered_at->format('H:i'),
                $order->table?->table_number ?? '-',
                $order->customer_name,
                $order->customer_phone,
                strtoupper($paymentMethod),
                $order->subtotal,
                $order->discount,
                $order->service_fee,
                $order->tax,
                $order->grand_total,
                $orderStatus,
            ]);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

    private function exportProducts(Carbon $from, Carbon $to)
    {
        $products = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('orders.ordered_at', [$from, $to])
            ->where('orders.payment_status', PaymentStatus::Paid)
            ->whereNull('orders.deleted_at')
            ->select(
                'products.name as product_name',
                'categories.name as category_name',
                DB::raw('SUM(order_items.qty) as total_qty'),
                DB::raw('SUM(order_items.subtotal) as total_revenue'),
                DB::raw('AVG(order_items.price) as avg_price')
            )
            ->groupBy('products.id', 'products.name', 'categories.id', 'categories.name')
            ->orderByDesc('total_qty')
            ->get();

        $filename = 'laporan-produk-' . $from->format('d-m-Y') . '-sd-' . $to->format('d-m-Y') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($products) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, [
                'Nama Produk', 'Kategori', 'Total Terjual',
                'Total Revenue', 'Harga Rata-rata',
            ]);

            foreach ($products as $product) {
                fputcsv($file, [
                    $product->product_name,
                    $product->category_name,
                    $product->total_qty,
                    $product->total_revenue,
                    round($product->avg_price),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportSummary(Carbon $from, Carbon $to)
    {
        $totalRevenue = Order::whereBetween('ordered_at', [$from, $to])
            ->where('payment_status', PaymentStatus::Paid)
            ->sum('grand_total');

        $totalOrders = Order::whereBetween('ordered_at', [$from, $to])
            ->where('payment_status', PaymentStatus::Paid)
            ->count();

        $dailyData = Order::selectRaw('DATE(ordered_at) as date, COUNT(*) as total_orders, SUM(grand_total) as total_revenue')
            ->whereBetween('ordered_at', [$from, $to])
            ->where('payment_status', PaymentStatus::Paid)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $filename = 'laporan-summary-' . $from->format('d-m-Y') . '-sd-' . $to->format('d-m-Y') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($dailyData, $totalRevenue, $totalOrders, $from, $to) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            // Info periode
            fputcsv($file, ['LAPORAN SUMMARY']);
            fputcsv($file, ['Periode', $from->format('d/m/Y') . ' - ' . $to->format('d/m/Y')]);
            fputcsv($file, ['Total Revenue', 'Rp ' . number_format($totalRevenue, 0, ',', '.')]);
            fputcsv($file, ['Total Order', $totalOrders]);
            fputcsv($file, ['Rata-rata Order', $totalOrders > 0 ? 'Rp ' . number_format($totalRevenue / $totalOrders, 0, ',', '.') : 'Rp 0']);
            fputcsv($file, []);

            // Detail harian
            fputcsv($file, ['Tanggal', 'Total Order', 'Total Revenue']);

            foreach ($dailyData as $day) {
                fputcsv($file, [
                    Carbon::parse($day->date)->format('d/m/Y'),
                    $day->total_orders,
                    $day->total_revenue,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}