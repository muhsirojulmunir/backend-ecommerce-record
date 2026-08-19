<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use Concerns\RecordsActivity;

    protected $activityLogName = 'pesanan';
    protected $activityModelLabel = 'pesanan';
    protected $activityLogAttributes = ['status', 'payment_status', 'tracking_number', 'courier', 'grand_total'];

    protected $fillable = [
        'user_id',
        'order_number',
        'total_price',
        'shipping_cost',
        'grand_total',
        'status',
        'shipping_address',
        'courier',
        'courier_code',
        'tracking_number',
        'payment_method',
        'payment_status',
        'notes',
        'cancellation_reason',
        'cancellation_note',
        'cancelled_at',
        'completed_at',
        'referral_code_used',
        'referrer_id',
        'referral_discount',
        'referral_commission',
        'invoice_number',
        'invoice_issued_at',
    ];

    protected function casts(): array
    {
        return [
            'shipping_address' => 'array',
            'total_price'      => 'decimal:2',
            'shipping_cost'    => 'decimal:2',
            'grand_total'      => 'decimal:2',
            'cancelled_at'     => 'datetime',
            'completed_at'     => 'datetime',
            'referral_discount' => 'decimal:2',
            'referral_commission' => 'decimal:2',
            'invoice_issued_at' => 'datetime',
        ];
    }

    /**
     * Terbitkan nomor invoice otomatis begitu pembayaran dinyatakan lunas,
     * termasuk saat admin mengonfirmasi pembayaran secara manual.
     */
    protected static function booted(): void
    {
        static::saving(function (self $order) {
            if ($order->payment_status === 'paid' && blank($order->invoice_number)) {
                $order->invoice_number    = static::buatNomorInvoice();
                $order->invoice_issued_at = now();
            }

            /*
             * Biaya transaksi dicatat begitu pesanan lunas.
             *
             * Ditaruh di kait model, bukan di tiap tempat yang menandai
             * pesanan lunas — sebab tempatnya ada beberapa: notifikasi
             * Midtrans, pemeriksaan status, pembayaran R_Pay, dan konfirmasi
             * manual admin. Satu kait menutup semuanya, dan jalur baru yang
             * ditambahkan nanti ikut tercakup dengan sendirinya.
             *
             * Dihitung saat baru menjadi lunas, saat kanal pembayarannya
             * berubah, atau bila pesanan lama belum pernah dihitung. Di luar
             * itu angkanya dibiarkan — yang sudah tercatat mewakili biaya
             * yang benar-benar terjadi saat itu.
             */
            if ($order->payment_status === 'paid'
                && ($order->isDirty('payment_status')
                    || $order->isDirty('payment_method')
                    || $order->net_revenue === null)) {
                app(\App\Services\TransactionFeeService::class)->terapkanKe($order);
            }
        });

        /*
         * Akibat kode referal dijalankan SETELAH baris pesanan tersimpan,
         * bukan di dalam saving(). Mengkreditkan saldo di tengah proses
         * penyimpanan berisiko: bila penyimpanannya kemudian gagal, uangnya
         * terlanjur berpindah.
         *
         * Dipasang pada model supaya berlaku untuk semua jalur — pembayaran
         * lewat Midtrans, R_Pay, maupun konfirmasi manual oleh admin.
         */
        static::saved(function (self $order) {
            $layanan = app(\App\Services\ReferralPayoutService::class);

            $baruLunas = $order->wasChanged('payment_status') && $order->payment_status === 'paid';
            $baruBatal = $order->wasChanged('status') && $order->status === 'cancelled';

            // Pesanan yang langsung tercipta dalam keadaan lunas (mis. dibayar
            // R_Pay) tidak menghasilkan perubahan kolom, jadi ikut diperiksa.
            if ($order->wasRecentlyCreated && $order->payment_status === 'paid') {
                $baruLunas = true;
            }

            if ($baruLunas) {
                $layanan->saatLunas($order);
            }

            if ($baruBatal) {
                $layanan->saatDibatalkan($order);
            }
        });
    }

    /**
     * Nomor invoice berurutan per bulan, contoh: INV/2026/08/0007
     */
    public static function buatNomorInvoice(): string
    {
        $awalan = 'INV/' . now()->format('Y/m') . '/';

        $terakhir = static::where('invoice_number', 'like', $awalan . '%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $urut = $terakhir ? ((int) substr($terakhir, -4)) + 1 : 1;

        return $awalan . str_pad((string) $urut, 4, '0', STR_PAD_LEFT);
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** Ulasan yang lahir dari pesanan ini. */
    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function returnRequest(): HasOne
    {
        return $this->hasOne(OrderReturn::class);
    }

    /**
     * Satu pesanan bisa punya lebih dari satu baris di order_returns —
     * misalnya pernah diajukan pembatalan, lalu setelah barang sampai
     * diajukan pengembalian. Karena itu relasi jamak ini yang dipakai untuk
     * menu Kelola Pengembalian, bukan returnRequest() yang tunggal.
     */
    public function returns(): HasMany
    {
        return $this->hasMany(OrderReturn::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopePendingPayment($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeShipped($query)
    {
        return $query->where('status', 'shipped');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeWithReturns($query)
    {
        return $query->whereHas('returnRequest');
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'    => 'Menunggu',
            'processing' => 'Diproses',
            'shipped'    => 'Dikirim',
            'completed'  => 'Selesai',
            'cancelled'  => 'Dibatalkan',
            default      => $this->status,
        };
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            'unpaid'               => 'Belum Bayar',
            'pending_verification' => 'Menunggu Konfirmasi Admin',
            'paid'                 => 'Sudah Dibayar',
            'failed'               => 'Gagal',
            'refunded'             => 'Dikembalikan',
            default                => $this->payment_status,
        };
    }

    public function getFormattedGrandTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->grand_total, 0, ',', '.');
    }
}
