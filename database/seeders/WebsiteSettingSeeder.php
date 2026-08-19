<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WebsiteSetting;

class WebsiteSettingSeeder extends Seeder
{
    /**
     * Daftar pengaturan yang tampil di menu "Pengaturan Website".
     *
     * Metadata (label/grup/tipe/deskripsi) selalu disegarkan saat seeder jalan,
     * tetapi kolom `value` hanya diisi saat baris pertama kali dibuat — supaya
     * nilai yang sudah diatur admin tidak tertimpa ketika seeder dijalankan ulang.
     */
    public function run(): void
    {
        $settings = [
            // ── Informasi Toko ────────────────────────────────────────────────
            ['general', 'site_name',         'string',  'Nama Toko',              'Nama toko yang tampil di judul halaman dan email.',            'RECORD Official Store'],
            ['general', 'site_tagline',      'string',  'Tagline Toko',           'Slogan singkat di bawah nama toko.',                           'Langkah Nyaman Setiap Hari'],
            ['general', 'site_description',  'text',    'Deskripsi Toko',         'Penjelasan singkat tentang toko, dipakai untuk SEO.',          null],
            ['general', 'site_logo',         'image',   'Logo Toko',              'Format PNG transparan, disarankan 512 × 512 piksel.',          null],
            ['general', 'site_favicon',      'image',   'Favicon',                'Ikon kecil di tab browser, 64 × 64 piksel.',                   null],
            ['general', 'store_address',     'text',    'Alamat Toko',            'Alamat lengkap toko untuk halaman kontak & label pengiriman.', 'Jl. E-Commerce No. 123, Jakarta'],

            // ── Kontak & Sosial Media ─────────────────────────────────────────
            ['contact', 'contact_email',     'string',  'Email Customer Service', 'Email yang bisa dihubungi pembeli.',                           null],
            ['contact', 'contact_phone',     'string',  'Nomor Telepon',          'Nomor telepon toko.',                                          null],
            ['contact', 'contact_whatsapp',  'string',  'Nomor WhatsApp',         'Diawali 62, contoh: 6281234567890.',                           null],
            ['contact', 'social_instagram',  'string',  'Instagram',              'Username tanpa tanda @.',                                      null],
            ['contact', 'social_facebook',   'string',  'Facebook',               'Nama halaman atau URL Facebook.',                              null],
            ['contact', 'social_tiktok',     'string',  'TikTok',                 'Username tanpa tanda @.',                                      null],

            // ── Pengiriman ────────────────────────────────────────────────────
            ['shipping', 'store_city_id',      'integer', 'ID Kota Asal',          'ID kota asal pengiriman sesuai data kurir.',                          '152'],
            ['shipping', 'store_postal_code',  'string',  'Kode Pos Toko',         'Kode pos alamat asal pengiriman.',                                    '60117'],
            ['shipping', 'couriers',           'json',    'Kurir Aktif',           'Kurir yang bisa dipilih pembeli saat checkout.',                       null],
            ['shipping', 'free_shipping_min',  'integer', 'Minimal Gratis Ongkir', 'Belanja minimal (Rp) agar ongkir gratis. Isi 0 untuk menonaktifkan.',  '0'],

            // ── Pembayaran ────────────────────────────────────────────────────
            ['payment', 'bank_name',     'string',  'Nama Bank',          'Bank tujuan transfer manual.',                            null],
            ['payment', 'bank_account',  'string',  'Nomor Rekening',     'Nomor rekening tujuan transfer.',                         null],
            ['payment', 'bank_holder',   'string',  'Atas Nama',          'Nama pemilik rekening.',                                  null],
            ['payment', 'enable_cod',    'boolean', 'Aktifkan COD',       'Izinkan pembeli membayar di tempat (Cash On Delivery).',  '1'],
            ['payment', 'payment_note',  'text',    'Catatan Pembayaran', 'Instruksi tambahan yang tampil di halaman pembayaran.',   null],

            // ── Operasional ───────────────────────────────────────────────────
            ['operational', 'maintenance_mode',    'boolean', 'Mode Perbaikan',       'Jika aktif, halaman toko ditutup sementara untuk pembeli.',                    '0'],
            ['operational', 'maintenance_message', 'text',    'Pesan Mode Perbaikan', 'Pesan yang tampil ke pembeli saat mode perbaikan aktif.',                      'Toko sedang dalam perbaikan. Silakan kembali beberapa saat lagi.'],
            ['operational', 'order_auto_cancel',   'integer', 'Batas Bayar (Jam)',    'Pesanan dibatalkan otomatis jika belum dibayar melewati jam ini. 0 = nonaktif.', '24'],
            ['operational', 'low_stock_threshold', 'integer', 'Ambang Stok Menipis',  'Produk ditandai stok menipis jika jumlahnya di bawah angka ini.',              '5'],
        ];

        foreach ($settings as [$group, $key, $type, $label, $description, $default]) {
            $setting = WebsiteSetting::firstOrNew(['key' => $key]);

            // Nilai hanya diisi sekali, saat baris belum ada
            if (! $setting->exists) {
                $setting->value = $key === 'couriers'
                    ? json_encode(['jne', 'jnt', 'sicepat', 'anteraja', 'pos'])
                    : $default;
            }

            $setting->fill([
                'group'       => $group,
                'type'        => $type,
                'label'       => $label,
                'description' => $description,
            ])->save();
        }
    }
}
