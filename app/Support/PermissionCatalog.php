<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

/**
 * Menerjemahkan nama permission mentah (mis.
 */
class PermissionCatalog
{
    /** Metadata permission bawaan: nama => [grup, label, ikon, keterangan]. */
    private const KNOWN = [
        'view dashboard'     => ['Dashboard', 'Lihat Dashboard',      'fa-chart-pie',          'Melihat ringkasan penjualan dan aktivitas toko.'],
        'manage orders'      => ['Produk',    'Kelola Pesanan',       'fa-receipt',            'Memproses pesanan, konfirmasi bayar, dan cetak resi.'],
        'manage products'    => ['Produk',    'Kelola Produk',        'fa-box-open',           'Menambah, mengubah, dan menghapus produk.'],
        'manage banners'     => ['Produk',    'Kelola Banner',        'fa-images',             'Mengatur banner slider di halaman utama toko.'],
        'manage discounts'   => ['Produk',    'Kelola Diskon',        'fa-tags',               'Mengatur potongan harga produk.'],
        'manage customers'   => ['Keuangan',  'Kelola Customer',      'fa-users',              'Melihat data pembeli dan memblokir akun bermasalah.'],
        'view reports'       => ['Keuangan',  'Kelola Laporan',       'fa-chart-bar',          'Melihat laporan omzet, produk terlaris, dan performa toko.'],
        'manage returns'     => ['Keuangan',  'Kelola Pengembalian',  'fa-rotate-left',        'Meninjau pengajuan pengembalian barang, menyetujui atau menolak.'],
        'manage reviews'     => ['Produk',    'Kelola Ulasan',        'fa-star',               'Meninjau ulasan pembeli, menyembunyikan yang kasar atau spam.'],
        'manage rpay'        => ['Keuangan',  'Kelola R_Pay',         'fa-wallet',             'Melihat saldo dan mutasi R_Pay seluruh akun pembeli.'],
        'process withdrawals' => ['Keuangan', 'Proses Pencairan',     'fa-money-bill-transfer', 'Memproses pencairan saldo R_Pay ke rekening bank pembeli.'],
        'manage roles'       => ['Settings',  'Kelola Role',          'fa-shield-halved',      'Membuat role dan menentukan hak aksesnya.'],
        'manage permissions' => ['Settings',  'Kelola Permission',    'fa-key',                'Membuat dan mengatur daftar hak akses sistem.'],
        'manage settings'    => ['Settings',  'Pengaturan Website',   'fa-gear',               'Mengubah identitas toko, kontak, pengiriman, dan pembayaran.'],
        'view activity logs' => ['Settings',  'Log Aktivitas',        'fa-clock-rotate-left',  'Menelusuri riwayat perubahan data oleh admin.'],
    ];

    /** Urutan tampilan grup, mengikuti urutan menu di sidebar. */
    private const GROUP_ORDER = ['Dashboard', 'Produk', 'Keuangan', 'Settings', 'Lainnya'];

    /**
 * Semua permission dari database, dikelompokkan dan sudah diberi label.
 *
 * @return Collection<string, Collection<int, array>>
 */
    public static function grouped(): Collection
    {
        return Permission::orderBy('id')->get()
            ->map(fn (Permission $permission) => self::describe($permission))
            ->groupBy('group')
            ->sortBy(fn ($items, $group) => array_search($group, self::GROUP_ORDER) === false
                ? count(self::GROUP_ORDER)
                : array_search($group, self::GROUP_ORDER));
    }

    /**
     * Metadata satu permission. Permission buatan sendiri (belum terdaftar di
     * KNOWN) tetap dapat label yang layak lewat tebakan dari namanya.
     */
    public static function describe(Permission $permission): array
    {
        [$group, $label, $icon, $description] = self::KNOWN[$permission->name] ?? [
            'Lainnya',
            ucfirst(str_replace(['manage ', 'view '], ['Kelola ', 'Lihat '], $permission->name)),
            'fa-circle-dot',
            'Permission tambahan yang dibuat manual.',
        ];

        return [
            'id'          => $permission->id,
            'name'        => $permission->name,
            'group'       => $group,
            'label'       => $label,
            'icon'        => $icon,
            'description' => $description,
            'is_custom'   => ! isset(self::KNOWN[$permission->name]),
            'roles_count' => $permission->roles_count ?? null,
        ];
    }

    /** Label singkat untuk satu nama permission, tanpa perlu model. */
    public static function label(string $name): string
    {
        return self::KNOWN[$name][1] ?? ucfirst(str_replace(['manage ', 'view '], ['Kelola ', 'Lihat '], $name));
    }

    /** Ikon Font Awesome untuk satu nama permission. */
    public static function icon(string $name): string
    {
        return self::KNOWN[$name][2] ?? 'fa-circle-dot';
    }

    /** Daftar nama grup yang dipakai, untuk dropdown saat membuat permission baru. */
    public static function groups(): array
    {
        return self::GROUP_ORDER;
    }
}
