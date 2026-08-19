<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminWebReportController extends Controller
{
    /** Preset rentang tanggal yang tersedia di filter. */
    private const PRESETS = ['today', '7d', '30d', 'this_month', 'last_month', 'this_year', 'custom'];

    /**
     * Laporan penjualan: ringkasan, grafik harian, produk & customer terbaik.
     */
    public function index(Request $request)
    {
        [$from, $to, $preset] = $this->resolveRange($request);

        // Semua angka omzet dihitung dari pesanan yang sudah LUNAS saja
        $paid = fn () => Order::whereBetween('created_at', [$from, $to])->where('payment_status', 'paid');
        $all  = fn () => Order::whereBetween('created_at', [$from, $to]);

        $revenue     = (float) $paid()->sum('grand_total');
        $paidCount   = $paid()->count();
        $orderCount  = $all()->count();

        $summary = [
            'revenue'       => $revenue,
            'orders'        => $orderCount,
            'paid_orders'   => $paidCount,
            'avg_order'     => $paidCount > 0 ? $revenue / $paidCount : 0.0,
            'items_sold'    => (int) $this->itemsQuery($from, $to, true)->sum('order_items.quantity'),
            'shipping'      => (float) $paid()->sum('shipping_cost'),
            'new_customers' => User::customers()->whereBetween('created_at', [$from, $to])->count(),
        ];

        $previous = $this->previousPeriodSummary($from, $to);

        return view('admin.reports', [
            'from'          => $from,
            'to'            => $to,
            'preset'        => $preset,
            'summary'       => $summary,
            'growth'        => [
                'revenue' => $this->growth($summary['revenue'], $previous['revenue']),
                'orders'  => $this->growth($summary['orders'], $previous['orders']),
            ],
            'previous'      => $previous,
            'chart'         => $this->dailySeries($from, $to),
            'statusCounts'  => $this->statusBreakdown($from, $to),
            'paymentMix'    => $this->paymentMix($from, $to),
            'topProducts'   => $this->topProducts($from, $to),
            'topCategories' => $this->topCategories($from, $to),
            'topCustomers'  => $this->topCustomers($from, $to),
        ]);
    }

    /**
     * Unduh laporan penjualan harian pada rentang aktif sebagai CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        [$from, $to] = $this->resolveRange($request);

        $rows     = $this->dailySeries($from, $to);
        $filename = 'laporan-' . $from->format('Ymd') . '-' . $to->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM supaya Excel membaca UTF-8 dengan benar
            fputcsv($out, ['Tanggal', 'Jumlah Pesanan', 'Pesanan Lunas', 'Omzet (Rp)'], escape: '');

            foreach ($rows as $row) {
                fputcsv($out, [$row['date'], $row['orders'], $row['paid_orders'], (int) $row['revenue']], escape: '');
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ─── Helper privat ────────────────────────────────────────────────────────

    /**
     * Terjemahkan preset / input tanggal menjadi rentang Carbon yang valid.
     *
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    private function resolveRange(Request $request): array
    {
        $preset = $request->get('preset', '30d');

        if (! in_array($preset, self::PRESETS, true)) {
            $preset = '30d';
        }

        if ($preset === 'custom') {
            $from = $this->parseDate($request->get('from'))?->startOfDay() ?? now()->subDays(29)->startOfDay();
            $to   = $this->parseDate($request->get('to'))?->endOfDay() ?? now()->endOfDay();

            // Toleransi kalau user membalik urutan tanggal
            if ($from->gt($to)) {
                [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
            }

            return [$from, $to, $preset];
        }

        [$from, $to] = match ($preset) {
            'today'      => [now()->startOfDay(), now()->endOfDay()],
            '7d'         => [now()->subDays(6)->startOfDay(), now()->endOfDay()],
            'this_month' => [now()->startOfMonth(), now()->endOfMonth()],
            'last_month' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
            'this_year'  => [now()->startOfYear(), now()->endOfYear()],
            default      => [now()->subDays(29)->startOfDay(), now()->endOfDay()],
        };

        return [$from, $to, $preset];
    }

    private function parseDate(?string $value): ?Carbon
    {
        if (! filled($value)) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Deret harian untuk grafik: omzet & jumlah pesanan per tanggal.
     * Tanggal tanpa transaksi tetap diisi 0 agar grafik tidak bolong.
     */
    private function dailySeries(Carbon $from, Carbon $to): array
    {
        $raw = Order::whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as d')
            ->selectRaw('COUNT(*) as orders')
            ->selectRaw("SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) as paid_orders")
            ->selectRaw("SUM(CASE WHEN payment_status = 'paid' THEN grand_total ELSE 0 END) as revenue")
            ->groupBy('d')
            ->get()
            ->keyBy('d');

        // Rentang panjang dipadatkan supaya grafik tetap terbaca
        $days = $from->diffInDays($to) + 1;
        $step = $days > 120 ? 7 : 1;

        $series = [];
        foreach (CarbonPeriod::create($from->copy()->startOfDay(), "{$step} days", $to) as $day) {
            $bucketEnd = $day->copy()->addDays($step - 1);

            $orders = 0;
            $paidOrders = 0;
            $revenue = 0.0;

            foreach (CarbonPeriod::create($day, $bucketEnd) as $d) {
                $row = $raw->get($d->format('Y-m-d'));
                if ($row) {
                    $orders     += (int) $row->orders;
                    $paidOrders += (int) $row->paid_orders;
                    $revenue    += (float) $row->revenue;
                }
            }

            $series[] = [
                'date'        => $day->format('Y-m-d'),
                'label'       => $step > 1
                    ? $day->translatedFormat('d M') . '–' . $bucketEnd->translatedFormat('d M')
                    : $day->translatedFormat('d M'),
                'orders'      => $orders,
                'paid_orders' => $paidOrders,
                'revenue'     => $revenue,
            ];
        }

        return $series;
    }

    /**
     * Ringkasan periode sebelumnya dengan panjang rentang yang sama, untuk perbandingan.
     */
    private function previousPeriodSummary(Carbon $from, Carbon $to): array
    {
        $length   = $from->diffInSeconds($to);
        $prevTo   = $from->copy()->subSecond();
        $prevFrom = $prevTo->copy()->subSeconds($length);

        return [
            'revenue' => (float) Order::whereBetween('created_at', [$prevFrom, $prevTo])
                ->where('payment_status', 'paid')
                ->sum('grand_total'),
            'orders'  => Order::whereBetween('created_at', [$prevFrom, $prevTo])->count(),
            'from'    => $prevFrom,
            'to'      => $prevTo,
        ];
    }

    /** Persentase pertumbuhan; null kalau periode sebelumnya kosong (tidak bisa dibandingkan). */
    private function growth(float|int $current, float|int $previous): ?float
    {
        if ($previous <= 0) {
            return null;
        }

        return (($current - $previous) / $previous) * 100;
    }

    private function statusBreakdown(Carbon $from, Carbon $to): array
    {
        $counts = Order::whereBetween('created_at', [$from, $to])
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return collect(['pending', 'processing', 'shipped', 'completed', 'cancelled'])
            ->mapWithKeys(fn ($s) => [$s => (int) ($counts[$s] ?? 0)])
            ->all();
    }

    private function paymentMix(Carbon $from, Carbon $to)
    {
        return Order::whereBetween('created_at', [$from, $to])
            ->where('payment_status', 'paid')
            ->selectRaw('payment_method, COUNT(*) as total, SUM(grand_total) as revenue')
            ->groupBy('payment_method')
            ->orderByDesc('revenue')
            ->get();
    }

    /**
     * Query dasar order_items pada rentang tanggal.
     * $paidOnly = hanya menghitung pesanan yang sudah lunas.
     */
    private function itemsQuery(Carbon $from, Carbon $to, bool $paidOnly = true)
    {
        $query = Order::query()
            ->join('order_items', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$from, $to]);

        if ($paidOnly) {
            $query->where('orders.payment_status', 'paid');
        }

        return $query;
    }

    private function topProducts(Carbon $from, Carbon $to)
    {
        return $this->itemsQuery($from, $to)
            ->selectRaw('order_items.product_name')
            ->selectRaw('SUM(order_items.quantity) as qty')
            ->selectRaw('SUM(order_items.price * order_items.quantity) as revenue')
            ->groupBy('order_items.product_name')
            ->orderByDesc('qty')
            ->limit(10)
            ->get();
    }

    private function topCategories(Carbon $from, Carbon $to)
    {
        return $this->itemsQuery($from, $to)
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->selectRaw('categories.name')
            ->selectRaw('SUM(order_items.quantity) as qty')
            ->selectRaw('SUM(order_items.price * order_items.quantity) as revenue')
            ->groupBy('categories.name')
            ->orderByDesc('revenue')
            ->limit(6)
            ->get();
    }

    private function topCustomers(Carbon $from, Carbon $to)
    {
        return Order::query()
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->whereBetween('orders.created_at', [$from, $to])
            ->where('orders.payment_status', 'paid')
            ->selectRaw('users.id, users.name, users.email')
            ->selectRaw('COUNT(orders.id) as orders_count')
            ->selectRaw('SUM(orders.grand_total) as revenue')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('revenue')
            ->limit(8)
            ->get();
    }
}
