@extends('layouts.app')

@section('title', 'Kelola Produk')
@section('page_title', 'Daftar Produk')

@section('content')
    @php
        // Dihitung di view agar tidak perlu mengubah controller yang dipakai
        // beberapa jalur lain; jumlahnya kecil dan hanya satu kueri.
        $jumlahUnggulan = \App\Models\Product::where('is_featured', true)
            ->where('status', 'active')->count();
        $idealUnggulan  = 8;
    @endphp

    <div class="space-y-6">

        {{-- ── Panduan Our Collection ──
             Bagian "Our Collection" di halaman utama mengambil produk yang
             ditandai bintang di bawah — bukan kategori. Tanpa keterangan ini,
             hubungan antara tombol bintang dan etalase depan tidak kelihatan. --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-star"></i>
            </div>

            <div class="min-w-0 flex-1">
                <h3 class="text-sm font-black text-gray-800">Our Collection di Halaman Utama</h3>
                <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                    Bagian ini menampilkan produk yang kamu tandai bintang pada tabel di bawah —
                    tidak ada hubungannya dengan kategori, jadi kamu tidak perlu mengisi semua kategori.
                    Barisnya rapi bila jumlahnya kelipatan 4.
                </p>
            </div>

            <div class="shrink-0 text-center sm:text-right">
                <p class="text-2xl font-black {{ $jumlahUnggulan === 0 ? 'text-rose-600' : 'text-gray-800' }}">
                    {{ $jumlahUnggulan }}<span class="text-sm text-gray-300 font-bold">/{{ $idealUnggulan }}</span>
                </p>
                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">produk tampil</p>

                @if($jumlahUnggulan === 0)
                    <p class="text-[10px] text-rose-600 font-semibold mt-1">Bagian ini disembunyikan</p>
                @elseif($jumlahUnggulan % 4 !== 0)
                    <p class="text-[10px] text-amber-600 font-semibold mt-1">
                        Tambah {{ 4 - ($jumlahUnggulan % 4) }} lagi agar barisnya penuh
                    </p>
                @else
                    <p class="text-[10px] text-emerald-600 font-semibold mt-1">Barisnya sudah rapi</p>
                @endif
            </div>
        </div>

        <!-- Actions and Search -->
        <div
            class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
            <form method="GET" action="{{ route('admin.products') }}" class="w-full sm:max-w-xs relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama produk atau SKU..."
                    class="block w-full pl-9 pr-3 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm placeholder-gray-400 transition">
            </form>

            <div class="flex items-center gap-2 shrink-0">
                {{-- Impor + unduh template berdampingan --}}
                <div class="flex items-stretch rounded-xl overflow-hidden border-2 border-emerald-500">
                    <a href="{{ route('admin.products.import') }}"
                        class="bg-white text-emerald-600 hover:bg-emerald-50 font-bold py-2.5 px-5 text-sm transition flex items-center gap-2">
                        <i class="fa-solid fa-file-excel"></i>
                        <span>Impor Excel</span>
                    </a>
                    <a href="{{ route('admin.products.import.template') }}"
                        title="Unduh template Excel"
                        class="bg-emerald-500 text-white hover:bg-emerald-600 font-bold py-2.5 px-4 text-sm transition flex items-center gap-2 border-l-2 border-emerald-500">
                        <i class="fa-solid fa-download"></i>
                        <span class="hidden lg:inline">Template</span>
                    </a>
                </div>

                <a href="{{ route('admin.products.create') }}"
                    class="bg-gradient-to-r from-orange-500 to-rose-500 hover:from-orange-600 hover:to-rose-600 text-white font-bold py-2.5 px-5 rounded-xl shadow-md text-sm transition flex items-center space-x-2">
                    <i class="fa-solid fa-plus"></i>
                    <span>Tambah Produk</span>
                </a>
            </div>
        </div>

        <!-- Products Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-500">
                    <thead class="text-xs text-gray-400 uppercase bg-gray-50/50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4">Produk</th>
                            <th class="px-6 py-4">Kategori</th>
                            <th class="px-6 py-4">Harga Jual</th>
                            <th class="px-6 py-4">Stok</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-center">Our Collection</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                        @forelse($products as $product)
                            {{-- Satu <tbody> per produk. HTML mengizinkan satu tabel
                                 punya banyak tbody, dan hanya dengan begini baris
                                 varian di bawah bisa membaca keadaan "terbentang"
                                 milik baris produknya — dua <tr> bersaudara tidak
                                 bisa berbagi x-data.

                                 Keadaannya per produk, bukan satu untuk seluruh
                                 tabel, supaya beberapa produk bisa dibuka sekaligus
                                 saat membandingkan stok. --}}
                    <tbody class="border-b border-gray-100" x-data="{ buka: false }">
                            <tr class="hover:bg-slate-50/50 transition" :class="buka && 'bg-orange-50/40'">
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-4">
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                            class="w-12 h-12 rounded-lg object-cover bg-gray-100 border border-gray-100 shrink-0">
                                        <div class="min-w-0">
                                            <h5 class="font-bold text-gray-800 line-clamp-1">{{ $product->name }}</h5>
                                            <p class="text-xs text-gray-400 mt-0.5">Berat:
                                                {{ $product->shipping->weight_gram ?? '-' }} gr
                                            </p>

                                            @if($product->variants->isNotEmpty())
                                                <button type="button" @click="buka = ! buka"
                                                    class="mt-1.5 inline-flex items-center gap-1.5 text-[11px] font-bold text-orange-600 hover:text-orange-700 transition">
                                                    <i class="fa-solid fa-chevron-down transition-transform duration-200"
                                                       :class="buka && 'rotate-180'"></i>
                                                    <span x-text="buka
                                                        ? 'Tutup varian'
                                                        : 'Lihat {{ $product->variants->count() }} varian'"></span>
                                                    @php $habis = $product->variants->where('stock', '<=', 0)->count(); @endphp
                                                    @if($habis > 0)
                                                        <span class="px-1.5 py-0.5 rounded bg-red-50 text-red-600 border border-red-200 text-[10px]">
                                                            {{ $habis }} habis
                                                        </span>
                                                    @endif
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-slate-100 text-slate-600 whitespace-nowrap">
                                        {{ $product->category->name ?? 'Tanpa Kategori' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($product->discount)
                                        <div class="flex flex-col">
                                            <span class="font-bold text-gray-800">Rp
                                                {{ number_format($product->discounted_price, 0, ',', '.') }}</span>
                                            <span class="text-[10px] line-through text-gray-400 mt-0.5">Rp
                                                {{ number_format($product->price, 0, ',', '.') }}</span>
                                        </div>
                                    @else
                                        <span class="font-bold text-gray-800">Rp
                                            {{ number_format($product->price, 0, ',', '.') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 font-semibold text-gray-700">
                                    <span data-stok-produk="{{ $product->id }}">{{ number_format($product->stock) }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($product->status === 'active')
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-200">
                                            <span class="w-1.5 h-1.5 mr-1.5 bg-green-500 rounded-full"></span>
                                            Aktif
                                        </span>
                                    @elseif($product->status === 'inactive')
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-50 text-gray-600 border border-gray-200">
                                            <span class="w-1.5 h-1.5 mr-1.5 bg-gray-400 rounded-full"></span>
                                            Nonaktif
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                                            Habis
                                        </span>
                                    @endif
                                </td>

                                {{-- Sakelar Our Collection.
                                     Ditaruh langsung di daftar supaya etalase bisa
                                     disusun sambil membandingkan produk, tanpa harus
                                     membuka form ubah satu per satu. --}}
                                <td class="px-6 py-4 text-center">
                                    <form action="{{ route('admin.products.toggle-featured', $product->id) }}"
                                          method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                title="{{ $product->is_featured ? 'Keluarkan dari Our Collection' : 'Masukkan ke Our Collection' }}"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border transition
                                                    {{ $product->is_featured
                                                        ? 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100'
                                                        : 'bg-gray-50 text-gray-400 border-gray-200 hover:bg-gray-100 hover:text-gray-600' }}">
                                            <i class="fa-{{ $product->is_featured ? 'solid' : 'regular' }} fa-star"></i>
                                            <span>{{ $product->is_featured ? 'Tampil' : 'Tidak' }}</span>
                                        </button>
                                    </form>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end items-center space-x-2">
                                        <a href="{{ route('admin.products.edit', $product->id) }}"
                                            class="text-gray-400 hover:text-orange-500 p-1 transition" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST"
                                            class="inline"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-gray-400 hover:text-red-500 p-1 transition"
                                                title="Hapus">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            {{-- Rincian varian, muncul saat baris produk dibentangkan.
                                 Tujuannya satu: mengosongkan stok yang habis tanpa
                                 membuka borang ubah produk. Karena itu yang bisa
                                 diubah di sini HANYA stok. --}}
                            <tr x-show="buka" x-cloak class="bg-slate-50/70">
                                <td colspan="7" class="px-6 pb-5 pt-1">
                                    <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
                                        <table class="w-full text-left text-xs">
                                            <thead class="bg-gray-50 text-gray-400 uppercase text-[10px]">
                                                <tr>
                                                    <th class="px-4 py-2.5">Varian</th>
                                                    <th class="px-4 py-2.5">SKU</th>
                                                    <th class="px-4 py-2.5">Harga</th>
                                                    <th class="px-4 py-2.5 w-64">Stok</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                @foreach($product->variants as $varian)
                                                    <tr x-data="barisVarian({{ $varian->id }}, {{ (int) $varian->stock }})"
                                                        :class="stok <= 0 && 'bg-red-50/40'">
                                                        <td class="px-4 py-3">
                                                            <div class="flex items-center gap-2">
                                                                @if($varian->color_hex)
                                                                    <span class="w-3.5 h-3.5 rounded-full border border-gray-300 shrink-0"
                                                                          style="background: {{ $varian->color_hex }}"></span>
                                                                @endif
                                                                <span class="font-bold text-gray-700">
                                                                    {{ trim(($varian->size ? 'Size ' . $varian->size : '') . ' ' . ($varian->color ? '- ' . $varian->color : '')) ?: 'Varian' }}
                                                                </span>
                                                            </div>
                                                        </td>

                                                        <td class="px-4 py-3 font-mono text-[11px] text-gray-500">
                                                            {{ $varian->sku ?: '-' }}
                                                        </td>

                                                        <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                                            @if($varian->price_adjustment)
                                                                Rp {{ number_format($product->price + $varian->price_adjustment, 0, ',', '.') }}
                                                            @else
                                                                Rp {{ number_format($product->price, 0, ',', '.') }}
                                                            @endif
                                                        </td>

                                                        <td class="px-4 py-3">
                                                            <div class="flex items-center gap-2">
                                                                <input type="number" min="0" max="999999"
                                                                       x-model.number="stok"
                                                                       @keydown.enter.prevent="simpan()"
                                                                       :disabled="menyimpan"
                                                                       class="w-20 rounded-lg border-gray-200 text-xs py-1.5 focus:border-orange-500 focus:ring-orange-500 disabled:bg-gray-100">

                                                                {{-- Jalan pintas yang paling sering dipakai. --}}
                                                                <button type="button" @click="kosongkan()"
                                                                        :disabled="menyimpan || stok === 0"
                                                                        class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 transition disabled:opacity-40 disabled:cursor-not-allowed"
                                                                        title="Jadikan stok 0">
                                                                    Kosongkan
                                                                </button>

                                                                <button type="button" @click="simpan()"
                                                                        :disabled="menyimpan || stok === awal"
                                                                        class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold bg-orange-600 text-white hover:bg-orange-700 transition disabled:opacity-40 disabled:cursor-not-allowed">
                                                                    <span x-show="!menyimpan">Simpan</span>
                                                                    <span x-show="menyimpan" x-cloak>...</span>
                                                                </button>

                                                                <span x-show="pesan" x-cloak x-text="pesan"
                                                                      class="text-[11px] font-bold"
                                                                      :class="galat ? 'text-red-600' : 'text-emerald-600'"></span>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                    </tbody>

                        @empty
                    <tbody>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <i class="fa-solid fa-inbox text-4xl text-gray-200"></i>
                                        <p>Belum ada produk yang ditambahkan.</p>
                                    </div>
                                </td>
                            </tr>
                    </tbody>
                        @endforelse
                </table>
            </div>

            @if($products->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>

{{-- Alpine dimuat dengan "defer" di layout, jadi skrip ini berjalan lebih
     dulu dan sempat mendaftarkan komponennya lewat alpine:init. Kalau
     fungsinya didefinisikan begitu saja sebagai fungsi global, urutannya
     tidak terjamin dan barisnya bisa mati tanpa galat. --}}
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('barisVarian', (id, stokAwal) => ({
            id,
            stok: stokAwal,
            awal: stokAwal,
            menyimpan: false,
            pesan: '',
            galat: false,
            pewaktuPesan: null,

            /*
             * Pesan selalu lewat sini supaya pewaktu penghapus dari pesan
             * SEBELUMNYA dibatalkan lebih dulu. Tanpa itu, pesan baru bisa
             * terhapus oleh pewaktu lama yang kebetulan jatuh tempo sesaat
             * setelahnya — dan admin mengira tombolnya tidak berfungsi.
             */
            tampilkanPesan(teks, adaGalat) {
                clearTimeout(this.pewaktuPesan);

                this.pesan = teks;
                this.galat = adaGalat;

                if (teks) {
                    this.pewaktuPesan = setTimeout(() => { this.pesan = ''; }, 2500);
                }
            },

            kosongkan() {
                this.stok = 0;
                this.simpan();
            },

            async simpan() {
                if (this.menyimpan) return;

                /*
                 * Nilai kosong atau minus ditolak sebelum dikirim. Kolom angka
                 * bisa berisi string kosong kalau isinya dihapus, dan mengirim
                 * itu ke peladen hanya menghasilkan galat yang membingungkan.
                 */
                const nilai = Number(this.stok);

                if (this.stok === '' || this.stok === null || !Number.isInteger(nilai) || nilai < 0) {
                    this.tampilkanPesan('Isi angka 0 atau lebih.', true);
                    return;
                }

                this.menyimpan = true;
                this.tampilkanPesan('', false);

                try {
                    const r = await fetch('{{ url('admin/products/varian') }}/' + this.id + '/stok', {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            {{-- Token diambil langsung dari Blade. Layout admin
                                 tidak memasang <meta name=csrf-token>, jadi
                                 membacanya dari DOM menghasilkan null dan setiap
                                 penyimpanan gagal. --}}
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({ stock: nilai }),
                    });

                    const data = await r.json();

                    if (!r.ok) {
                        throw new Error(data.message || 'Gagal menyimpan.');
                    }

                    this.stok = data.stock;
                    this.awal = data.stock;
                    this.tampilkanPesan(data.pesan, false);

                    /*
                     * Angka stok di baris produk induk ikut disegarkan supaya
                     * tidak bertentangan dengan rincian di bawahnya. Diubah
                     * lewat DOM langsung, bukan memuat ulang halaman — memuat
                     * ulang akan menutup semua baris yang sedang dibentangkan
                     * dan justru memperlambat pekerjaan yang mau dipercepat.
                     */
                    const sel = document.querySelector('[data-stok-produk="' + data.produk_id + '"]');
                    if (sel) sel.textContent = new Intl.NumberFormat('id-ID').format(data.stok_produk);
                } catch (e) {
                    this.tampilkanPesan(e.message, true);
                } finally {
                    this.menyimpan = false;
                }
            },
        }));
    });
</script>

@endsection