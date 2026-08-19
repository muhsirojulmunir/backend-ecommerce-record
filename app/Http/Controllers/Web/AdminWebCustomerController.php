<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminWebCustomerController extends Controller
{
    /**
     * Daftar customer lengkap dengan statistik belanja, filter, pencarian & urutan.
     */
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');
        $sort   = $request->get('sort', 'latest');

        $customers = $this->baseQuery()
            ->tap(fn ($q) => $this->applyFilter($q, $filter))
            ->tap(fn ($q) => $this->applySearch($q, $request->get('search')))
            ->tap(fn ($q) => $this->applySort($q, $sort))
            ->paginate(15)
            ->withQueryString();

        return view('admin.customers', [
            'customers' => $customers,
            'counts'    => $this->tabCounts(),
            'stats'     => $this->headlineStats(),
            'filter'    => $filter,
            'sort'      => $sort,
        ]);
    }

    /**
     * Detail satu customer: profil, alamat, ringkasan belanja & riwayat pesanan.
     */
    public function show(int $id)
    {
        $customer = User::customers()
            ->with(['addresses' => fn ($q) => $q->orderByDesc('is_default')])
            ->findOrFail($id);

        $orders = Order::with('items')
            ->where('user_id', $customer->id)
            ->latest()
            ->paginate(10);

        // Ringkasan hanya menghitung pesanan yang benar-benar sudah dibayar
        $paid = Order::where('user_id', $customer->id)->where('payment_status', 'paid');

        $summary = [
            'total_orders'   => Order::where('user_id', $customer->id)->count(),
            'total_spent'    => (float) (clone $paid)->sum('grand_total'),
            'paid_orders'    => (clone $paid)->count(),
            'avg_order'      => (float) (clone $paid)->avg('grand_total'),
            'last_order_at'  => Order::where('user_id', $customer->id)->max('created_at'),
            'status_counts'  => Order::where('user_id', $customer->id)
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
        ];

        // Produk yang paling sering dibeli customer ini
        $favoriteProducts = Order::where('orders.user_id', $customer->id)
            ->join('order_items', 'order_items.order_id', '=', 'orders.id')
            ->selectRaw('order_items.product_name, SUM(order_items.quantity) as qty, SUM(order_items.price * order_items.quantity) as revenue')
            ->groupBy('order_items.product_name')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();

        return view('admin.customers-show', compact('customer', 'orders', 'summary', 'favoriteProducts'));
    }

    /**
     * Blokir / buka blokir akun customer.
     */
    public function toggleBlock(int $id)
    {
        $customer = User::customers()->findOrFail($id);
        $customer->update(['is_blocked' => ! $customer->is_blocked]);

        // Cabut token API yang masih aktif supaya blokir langsung berlaku, bukan menunggu login berikutnya
        if ($customer->is_blocked) {
            $customer->tokens()->delete();
        }

        $status = $customer->is_blocked ? 'diblokir' : 'diaktifkan kembali';

        return redirect()->back()->with('success', "Akun {$customer->name} berhasil {$status}.");
    }

    /**
     * Unduh daftar customer (mengikuti filter & pencarian aktif) sebagai CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $customers = $this->baseQuery()
            ->tap(fn ($q) => $this->applyFilter($q, $request->get('filter', 'all')))
            ->tap(fn ($q) => $this->applySearch($q, $request->get('search')))
            ->tap(fn ($q) => $this->applySort($q, $request->get('sort', 'latest')))
            ->get();

        $filename = 'customers-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($customers) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM supaya Excel membaca UTF-8 dengan benar
            fputcsv($out, ['Nama', 'Email', 'Telepon', 'Status Akun', 'Jumlah Pesanan', 'Total Belanja (Rp)', 'Pesanan Terakhir', 'Tanggal Daftar'], escape: '');

            foreach ($customers as $c) {
                fputcsv($out, [
                    $c->name,
                    $c->email,
                    $c->phone ?: '-',
                    $c->is_blocked ? 'Diblokir' : 'Aktif',
                    $c->orders_count,
                    (int) $c->total_spent,
                    $c->last_order_at ? date('d/m/Y H:i', strtotime($c->last_order_at)) : '-',
                    $c->created_at?->format('d/m/Y H:i'),
                ], escape: '');
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ─── Helper privat ────────────────────────────────────────────────────────

    /**
     * Query dasar customer + agregat belanja (dipakai index maupun export).
     */
    private function baseQuery()
    {
        return User::customers()
            ->withCount('orders')
            ->withSum(
                ['orders as total_spent' => fn ($q) => $q->where('payment_status', 'paid')],
                'grand_total'
            )
            ->withMax('orders as last_order_at', 'created_at');
    }

    private function applyFilter($query, string $filter): void
    {
        match ($filter) {
            'active'   => $query->has('orders'),
            'inactive' => $query->doesntHave('orders'),
            'new'      => $query->where('created_at', '>=', now()->subDays(30)),
            'blocked'  => $query->where('is_blocked', true),
            default    => null,
        };
    }

    private function applySearch($query, ?string $search): void
    {
        if (! filled($search)) {
            return;
        }

        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    private function applySort($query, string $sort): void
    {
        match ($sort) {
            'spend_desc'  => $query->orderByDesc('total_spent'),
            'orders_desc' => $query->orderByDesc('orders_count'),
            'name'        => $query->orderBy('name'),
            'oldest'      => $query->oldest(),
            default       => $query->latest(),
        };
    }

    private function tabCounts(): array
    {
        return [
            'all'      => User::customers()->count(),
            'active'   => User::customers()->has('orders')->count(),
            'inactive' => User::customers()->doesntHave('orders')->count(),
            'new'      => User::customers()->where('created_at', '>=', now()->subDays(30))->count(),
            'blocked'  => User::customers()->where('is_blocked', true)->count(),
        ];
    }

    private function headlineStats(): array
    {
        $customerIds = User::customers()->select('id');

        $paidOrders = Order::whereIn('user_id', $customerIds)->where('payment_status', 'paid');

        $totalRevenue = (float) (clone $paidOrders)->sum('grand_total');
        $buyerCount   = User::customers()->has('orders')->count();

        return [
            'total'         => User::customers()->count(),
            'new_this_month' => User::customers()->where('created_at', '>=', now()->startOfMonth())->count(),
            'buyers'        => $buyerCount,
            'total_revenue' => $totalRevenue,
            // Rata-rata nilai belanja per customer yang pernah bertransaksi
            'avg_per_buyer' => $buyerCount > 0 ? $totalRevenue / $buyerCount : 0.0,
        ];
    }
}
