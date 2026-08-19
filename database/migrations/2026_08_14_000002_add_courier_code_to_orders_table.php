<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Kode kurir Biteship pada pesanan.
 *
 * Selama ini yang disimpan hanya nama tampilannya — "GoSend Instant",
 * "J&T Express" — sedangkan kode resmi dari Biteship (gojek:instant,
 * jnt:reg) dibuang begitu saja. Akibatnya saat pesanan diproses, kurirnya
 * harus ditebak ulang dari teks, dan tebakan itu meleset untuk hampir semua
 * pilihan: pembeli memilih J&T atau GoSend Instant, paketnya berangkat JNE
 * reguler.
 *
 * Kolom ini menyimpan kode aslinya apa adanya, sehingga kurir yang dipesan
 * pembeli benar-benar itu yang dibooking ke Biteship.
 */
return new class extends Migration
{
    /** Menerka kode kurir dari nama tampilan, untuk mengisi pesanan lama. */
    private function terka(?string $nama): ?string
    {
        if (blank($nama)) {
            return null;
        }

        $teks = strtolower($nama);

        $peta = [
            'gojek'     => ['gosend', 'gojek', 'go-send'],
            'grab'      => ['grabexpress', 'grab'],
            'lalamove'  => ['lalamove'],
            'jnt'       => ['j&t', 'jnt', 'j & t'],
            'jne'       => ['jne'],
            'sicepat'   => ['sicepat', 'si cepat'],
            'anteraja'  => ['anteraja', 'anter aja'],
            'pos'       => ['pos indonesia', 'pos ind'],
            'ninja'     => ['ninja'],
            'tiki'      => ['tiki'],
            'idexpress' => ['id express', 'idexpress'],
        ];

        foreach ($peta as $kode => $kataKunci) {
            foreach ($kataKunci as $kata) {
                if (str_contains($teks, $kata)) {
                    // Layanan instan dikenali dari namanya sendiri.
                    $jenis = str_contains($teks, 'instant') || str_contains($teks, 'instan')
                        ? 'instant'
                        : 'reg';

                    return $kode . ':' . $jenis;
                }
            }
        }

        return null;
    }

    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('courier_code', 64)->nullable()->after('courier');
        });

        /*
         * Pesanan lama diisi sebisanya dari namanya. Hasil terkaan ini hanya
         * untuk pesanan yang sudah terlanjur ada — pesanan baru memakai kode
         * asli dari Biteship, bukan terkaan.
         */
        DB::table('orders')->whereNotNull('courier')->orderBy('id')
            ->get(['id', 'courier'])
            ->each(function ($baris) {
                $kode = $this->terka($baris->courier);

                if ($kode) {
                    DB::table('orders')->where('id', $baris->id)->update(['courier_code' => $kode]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('courier_code');
        });
    }
};
