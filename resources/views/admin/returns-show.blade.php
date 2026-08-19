@extends('layouts.app')

@section('title', 'Tinjau Pengembalian')
@section('page_title', 'Tinjau Pengajuan Pengembalian')
@section('page_subtitle', ($pengajuan->return_number ? $pengajuan->return_number . ' • ' : '') . 'Pesanan ' . ($pengajuan->order?->order_number ?? '—'))

@section('content')
@php
    $kelasStatus = [
        'pending'  => 'bg-amber-50 text-amber-700 border-amber-200',
        'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'rejected' => 'bg-rose-50 text-rose-700 border-rose-200',
    ][$pengajuan->status] ?? 'bg-gray-50 text-gray-600 border-gray-200';

    // Harga barang saja — ongkos kirim tidak dikembalikan karena jasa
    // antarnya sudah terpakai.
    // Usulan mengikuti bawaan di AdminWebReturnController::finalize(): seluruh
    // yang dibayar pembeli, ongkos kirim termasuk. Kalau angka di sini berbeda
    // dari yang dipakai controller, admin akan mengira ongkirnya ikut padahal
    // tidak — persis jenis selisih yang baru ketahuan setelah dana cair.
    $nominalUsulan = (float) ($pengajuan->refund_amount ?? $pengajuan->order?->grand_total ?? 0);
@endphp

<div class="space-y-6">
    <a href="{{ route('admin.returns') }}" class="inline-flex items-center gap-2 text-xs font-bold text-gray-500 hover:text-gray-800">
        <i class="fa-solid fa-arrow-left text-[10px]"></i> Kembali ke daftar
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── Rincian Pengajuan ── --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
                <div class="flex items-start justify-between gap-4 border-b border-gray-50 pb-4">
                    <div>
                        <h3 class="font-black text-gray-800">Alasan Pengembalian</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Diajukan {{ $pengajuan->created_at->format('d M Y, H:i') }}</p>
                    </div>
                    <span class="px-3 py-1.5 rounded-xl text-[11px] font-bold border {{ $kelasStatus }}">
                        {{ $pengajuan->status_label }}
                    </span>
                </div>

                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">Alasan yang dipilih</p>
                    <p class="text-sm font-bold text-gray-800">{{ $pengajuan->reason_label }}</p>
                </div>

                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">Penjelasan pembeli</p>
                    @if(filled($pengajuan->reason))
                        <div class="bg-gray-50 rounded-xl p-4 text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $pengajuan->reason }}</div>
                    @else
                        {{-- Penjelasan bebas memang opsional selain untuk "Alasan lain",
                             jadi kosong di sini bukan berarti datanya hilang. --}}
                        <div class="bg-gray-50 rounded-xl p-4 text-xs text-gray-400 italic">
                            Tidak diisi — pembeli memilih alasan dari daftar tanpa menambahkan penjelasan.
                        </div>
                    @endif
                </div>

                {{-- ── Bukti dari pembeli ──
                     Ditempatkan tepat setelah alasannya, sebelum keputusan
                     diambil: inilah yang seharusnya dilihat admin lebih dulu
                     sebelum menyetujui atau menolak. --}}
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">Bukti yang dilampirkan</p>

                    @if($pengajuan->receipt_photo || $pengajuan->package_photo || $pengajuan->unboxing_video)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach([
                                ['jalur' => $pengajuan->receipt_photo, 'judul' => 'Foto Resi'],
                                ['jalur' => $pengajuan->package_photo, 'judul' => 'Foto Paket'],
                            ] as $foto)
                                <div>
                                    <p class="text-xs font-bold text-gray-600 mb-1.5">{{ $foto['judul'] }}</p>
                                    @if($foto['jalur'])
                                        {{-- Gambarnya dibungkus tautan supaya admin bisa
                                             membuka versi penuhnya; detail resi kerap tidak
                                             terbaca pada ukuran pratinjau. --}}
                                        <a href="{{ Storage::url($foto['jalur']) }}" target="_blank" rel="noopener"
                                           class="block rounded-xl overflow-hidden border border-gray-200 hover:border-gray-400 transition">
                                            <img src="{{ Storage::url($foto['jalur']) }}" alt="{{ $foto['judul'] }}"
                                                 class="w-full h-44 object-cover">
                                        </a>
                                    @else
                                        <div class="rounded-xl bg-gray-50 p-4 text-xs text-gray-400 italic">Tidak ada.</div>
                                    @endif
                                </div>
                            @endforeach

                            <div class="sm:col-span-2">
                                <p class="text-xs font-bold text-gray-600 mb-1.5">
                                    Video Unboxing
                                    @if($pengajuan->video_duration)
                                        <span class="ml-1 font-normal text-gray-400">
                                            ({{ intdiv($pengajuan->video_duration, 60) }} menit
                                            {{ str_pad($pengajuan->video_duration % 60, 2, '0', STR_PAD_LEFT) }} detik)
                                        </span>
                                    @endif
                                </p>
                                @if($pengajuan->unboxing_video)
                                    <video src="{{ Storage::url($pengajuan->unboxing_video) }}" controls preload="metadata"
                                           class="w-full rounded-xl border border-gray-200 bg-black"></video>
                                @else
                                    <div class="rounded-xl bg-gray-50 p-4 text-xs text-gray-400 italic">Tidak ada.</div>
                                @endif
                            </div>
                        </div>
                    @else
                        {{-- Pengajuan lama dibuat sebelum bukti diwajibkan, jadi
                             kosong di sini bukan berarti pembeli melewatinya. --}}
                        <div class="bg-gray-50 rounded-xl p-4 text-xs text-gray-400 italic">
                            Tidak ada bukti terlampir — pengajuan ini dibuat sebelum lampiran diwajibkan.
                        </div>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">Penyelesaian yang diminta</p>
                        <p class="text-sm font-bold text-gray-800">{{ $pengajuan->resolution_label }}</p>
                    </div>
                    @if($pengajuan->resolution === 'exchange')
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">Barang / ukuran pengganti</p>
                        <p class="text-sm font-bold text-gray-800">{{ $pengajuan->exchange_request ?: '—' }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ── Isi Pesanan ── --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-black text-gray-800 mb-4">Isi Pesanan</h3>
                <div class="space-y-3">
                    @foreach($pengajuan->order?->items ?? [] as $item)
                    <div class="flex items-center justify-between gap-4 text-xs border-b border-gray-50 pb-3 last:border-0">
                        <div class="min-w-0">
                            <p class="font-bold text-gray-800 truncate">{{ $item->product_name }}</p>
                            <p class="text-gray-400 mt-0.5">{{ $item->variant_info }} &middot; {{ $item->quantity }} pasang</p>
                        </div>
                        <p class="font-bold text-gray-700 shrink-0">Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</p>
                    </div>
                    @endforeach
                </div>

                <div class="mt-4 pt-4 border-t border-gray-100 space-y-1.5 text-xs">
                    <div class="flex justify-between text-gray-500">
                        <span>Harga barang</span>
                        <span class="font-bold text-gray-700">Rp {{ number_format($pengajuan->order?->total_price ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-gray-500">
                        <span>Ongkos kirim</span>
                        <span class="font-bold text-gray-700">Rp {{ number_format($pengajuan->order?->shipping_cost ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between pt-1.5 border-t border-gray-50">
                        <span class="font-bold text-gray-700">Total dibayar</span>
                        <span class="font-black text-gray-900">Rp {{ number_format($pengajuan->order?->grand_total ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Panel Keputusan ── --}}
        <div class="space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-black text-gray-800 mb-4">Pembeli</h3>
                <p class="text-sm font-bold text-gray-800">{{ $pengajuan->user?->name }}</p>
                <p class="text-xs text-gray-400">{{ $pengajuan->user?->email }}</p>
                <p class="text-xs text-gray-400">{{ $pengajuan->user?->phone }}</p>

                <div class="mt-4 pt-4 border-t border-gray-50 flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-500">Saldo R_Pay saat ini</span>
                    <span class="text-sm font-black text-gray-800">
                        Rp {{ number_format($pengajuan->user?->rpay_balance ?? 0, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            {{-- ══ Panel keputusan, mengikuti tahap yang sedang berjalan ══ --}}

            {{-- Tahap 1: meninjau pengajuan --}}
            @if($pengajuan->status === 'pending')
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6" x-data="{ keputusan: '' }">
                <h3 class="font-black text-gray-800 mb-1">Tahap 1 — Tinjau Pengajuan</h3>
                <p class="text-[11px] text-gray-400 mb-4 leading-relaxed">
                    Menyetujui belum mencairkan dana. Pembeli akan diminta mengirim barangnya
                    kembali dulu; dana menyusul setelah barang sampai dan lolos diperiksa.
                </p>

                <form method="POST" action="{{ route('admin.returns.decide', $pengajuan->id) }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="keputusan" :value="keputusan">

                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-1.5">
                            Catatan untuk pembeli
                            <span x-show="keputusan === 'rejected'" class="text-rose-500">(wajib bila ditolak)</span>
                        </label>
                        <textarea name="admin_notes" rows="4"
                                  placeholder="Contoh: Silakan kirim sepatunya beserta dus dan kartunya."
                                  class="w-full text-xs border border-gray-200 rounded-xl px-3 py-2.5 focus:ring-orange-500 focus:border-orange-500">{{ old('admin_notes') }}</textarea>
                        @error('admin_notes')
                            <p class="text-[10px] text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2 pt-1">
                        <button type="submit" @click="keputusan = 'approved'"
                                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold py-3 rounded-xl transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-circle-check"></i>
                            Setujui &amp; Minta Barang Dikirim
                        </button>
                        <button type="submit" @click="keputusan = 'rejected'"
                                class="w-full bg-white border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-bold py-3 rounded-xl transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-circle-xmark"></i>
                            Tolak Pengajuan
                        </button>
                    </div>
                </form>
            </div>

            {{-- Tahap 2: menunggu pembeli mengirim --}}
            @elseif($pengajuan->status === 'approved')
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-3">
                <h3 class="font-black text-gray-800">Tahap 2 — Menunggu Barang Dikirim</h3>
                <p class="text-xs text-gray-500 leading-relaxed">
                    Pembeli sudah diberi alamat pengembalian dan diminta mencatatkan nomor resinya.
                    Belum ada yang perlu kamu lakukan sampai resinya masuk.
                </p>
                <div class="bg-amber-50 border border-amber-100 rounded-xl p-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-amber-700">Disetujui pada</p>
                    <p class="text-sm font-black text-amber-800 mt-0.5">
                        {{ $pengajuan->approved_at?->translatedFormat('d F Y, H:i') }}
                    </p>
                    <p class="text-[10px] text-amber-600 font-semibold mt-1">
                        Tenggat kirim pembeli:
                        {{ $pengajuan->approved_at?->addDays(config('alasan-retur.batas_kirim_balik_hari', 7))->translatedFormat('d F Y') }}
                    </p>
                </div>
            </div>

            {{-- Tahap 3: barang dalam perjalanan --}}
            @elseif($pengajuan->status === 'shipped_back')
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                <h3 class="font-black text-gray-800">Tahap 3 — Barang Dalam Perjalanan</h3>

                <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-blue-700">Resi dari pembeli</p>
                    <p class="text-sm font-black text-blue-900 mt-0.5">{{ $pengajuan->return_courier }}</p>
                    <p class="text-sm font-mono font-bold text-blue-800">{{ $pengajuan->return_tracking_number }}</p>
                    <p class="text-[10px] text-blue-600 font-semibold mt-1">
                        Dikirim {{ $pengajuan->shipped_back_at?->translatedFormat('d F Y, H:i') }}
                    </p>
                </div>

                {{-- Posisi paket kembali.

                     Inilah bagian yang paling menentukan rasa aman: barang
                     yang sudah dikirim balik pembeli belum tentu sampai, dan
                     tanpa pelacakan tidak ada cara membedakan paket yang masih
                     di jalan dari paket yang benar-benar hilang. --}}
                @php
                    /*
                     * Tujuan paket kembali adalah TOKO, bukan alamat pembeli —
                     * arahnya terbalik dari pengiriman biasa. Koordinatnya
                     * diambil dari pengaturan toko.
                     */
                    $lintangToko = config('pengiriman.toko.lintang');
                    $bujurToko   = config('pengiriman.toko.bujur');
                @endphp

                <x-lacak-paket
                    :alamat="route('admin.pelacakan.pengembalian', $pengajuan->id)"
                    :resi="$pengajuan->return_tracking_number"
                    :kurir="$pengajuan->return_courier"
                    :lintang="$lintangToko"
                    :bujur="$bujurToko"
                    :tujuan="'Gudang ' . config('pengiriman.toko.kota', 'toko')"
                    judul="Posisi Paket Kembali"
                    nada="rose" />

                <form method="POST" action="{{ route('admin.returns.terima', $pengajuan->id) }}">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('Tandai barang sudah sampai di gudang?');"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-3 rounded-xl transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-box-open"></i>
                        Barang Sudah Sampai
                    </button>
                </form>
            </div>

            {{-- Tahap 4: pemeriksaan & penentuan akhir --}}
            @elseif($pengajuan->status === 'received')
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6" x-data="{ hasil: '' }">
                <h3 class="font-black text-gray-800 mb-1">Tahap 4 — Hasil Pemeriksaan</h3>
                <p class="text-[11px] text-gray-400 mb-4 leading-relaxed">
                    Barang sampai {{ $pengajuan->received_at?->translatedFormat('d F Y, H:i') }}.
                    Tolak bila barangnya cacat, rusak, atau tidak sesuai dengan yang diajukan.
                </p>

                <form method="POST" action="{{ route('admin.returns.finalize', $pengajuan->id) }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="hasil" :value="hasil">

                    @if($pengajuan->resolution === 'refund')
                    {{-- Kolom rupiah.
                         Sebelumnya memakai <input type="number" step="1000">, dan itu
                         menjebak: mengetik "150.000" seperti kebiasaan menulis rupiah
                         membuat browser membacanya sebagai 150 (titik dianggap koma
                         desimal), lalu menolak dengan "valid values are 0 and 1000".
                         Sekarang isiannya teks berpemisah ribuan, sementara yang
                         terkirim ke server tetap angka bulat lewat kolom tersembunyi. --}}
                    <div x-show="hasil !== 'rejected'"
                         x-data="{
                            nominal: {{ (int) $nominalUsulan }},
                            batas: {{ (int) ($pengajuan->order?->grand_total ?? 0) }},

                            get tampil() {
                                return this.nominal > 0
                                    ? new Intl.NumberFormat('id-ID').format(this.nominal)
                                    : '';
                            },

                            /* Titik, koma, spasi, dan huruf diabaikan — hanya angkanya diambil. */
                            ubah(teks) {
                                const angka = String(teks).replace(/[^0-9]/g, '');
                                this.nominal = angka === '' ? 0 : Math.min(parseInt(angka, 10), this.batas);
                            }
                         }">
                        <label class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-1.5">
                            Nominal dikembalikan ke R_Pay
                        </label>

                        <div class="flex items-center border border-gray-200 rounded-xl overflow-hidden focus-within:ring-1 focus-within:ring-orange-500 focus-within:border-orange-500">
                            <span class="px-3 py-2.5 text-sm font-bold text-gray-400 bg-gray-50 border-r border-gray-200">Rp</span>
                            <input type="text" inputmode="numeric" autocomplete="off"
                                   :value="tampil"
                                   @input="ubah($event.target.value); $event.target.value = tampil"
                                   placeholder="0"
                                   class="flex-1 text-sm font-bold px-3 py-2.5 border-0 focus:ring-0 focus:outline-none">
                        </div>

                        {{-- Yang benar-benar dikirim: angka bulat tanpa pemisah. --}}
                        <input type="hidden" name="nominal" :value="nominal">

                        <p class="text-[10px] text-gray-400 mt-1.5 leading-relaxed">
                            Bawaannya seluruh yang dibayar pembeli, ongkos kirim termasuk. Ubah bila perlu.
                            Maksimal Rp {{ number_format($pengajuan->order?->grand_total ?? 0, 0, ',', '.') }}
                            (total yang dibayar pembeli).
                        </p>
                        @error('nominal')
                            <p class="text-[10px] text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>
                    @endif

                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-1.5">
                            Hasil pemeriksaan barang <span class="text-rose-500">(wajib)</span>
                        </label>
                        <textarea name="inspection_notes" rows="4"
                                  placeholder="Contoh: Sepatu masih mulus, dus dan kartu lengkap, sesuai dengan yang diajukan."
                                  class="w-full text-xs border border-gray-200 rounded-xl px-3 py-2.5 focus:ring-orange-500 focus:border-orange-500">{{ old('inspection_notes') }}</textarea>
                        @error('inspection_notes')
                            <p class="text-[10px] text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2 pt-1">
                        <button type="submit" @click="hasil = 'completed'"
                                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold py-3 rounded-xl transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-circle-check"></i>
                            {{ $pengajuan->resolution === 'refund' ? 'Lolos — Cairkan Dana' : 'Lolos — Proses Penukaran' }}
                        </button>
                        <button type="submit" @click="hasil = 'rejected'"
                                class="w-full bg-white border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-bold py-3 rounded-xl transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-circle-xmark"></i>
                            Tolak — Barang Tidak Memenuhi Syarat
                        </button>
                    </div>

                    @if($pengajuan->resolution === 'refund')
                    <p class="text-[10px] text-gray-400 leading-relaxed pt-1">
                        Meloloskan akan langsung menambah saldo R_Pay pembeli sebesar nominal di atas.
                        Tindakan ini tidak bisa dibatalkan dari halaman ini.
                    </p>
                    @endif
                </form>
            </div>

            {{-- Sudah selesai atau ditolak --}}
            @else
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-3">
                <h3 class="font-black text-gray-800">Sudah Selesai</h3>
                <p class="text-xs text-gray-500">
                    {{ $pengajuan->status_label }} oleh
                    <strong>{{ $pengajuan->diputuskanOleh?->name ?? 'sistem' }}</strong>
                    pada {{ $pengajuan->resolved_at?->translatedFormat('d F Y, H:i') }}.
                </p>

                @if($pengajuan->refund_amount)
                <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Dana dikembalikan</p>
                    <p class="text-lg font-black text-emerald-800 mt-0.5">
                        Rp {{ number_format($pengajuan->refund_amount, 0, ',', '.') }}
                    </p>
                    <p class="text-[10px] text-emerald-600 font-semibold">Sudah masuk ke R_Pay pembeli</p>
                </div>
                @endif

                @if($pengajuan->return_tracking_number)
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">Resi pengiriman balik</p>
                    <p class="text-xs text-gray-600">
                        {{ $pengajuan->return_courier }} &middot;
                        <span class="font-mono font-bold">{{ $pengajuan->return_tracking_number }}</span>
                    </p>
                </div>
                @endif

                @if($pengajuan->inspection_notes)
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">Hasil pemeriksaan</p>
                    <div class="bg-gray-50 rounded-xl p-3 text-xs text-gray-600 whitespace-pre-line">{{ $pengajuan->inspection_notes }}</div>
                </div>
                @endif

                @if($pengajuan->admin_notes)
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">Catatan</p>
                    <div class="bg-gray-50 rounded-xl p-3 text-xs text-gray-600 whitespace-pre-line">{{ $pengajuan->admin_notes }}</div>
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
