@props([
    'alamat',                       // alamat AJAX pelacakan
    'resi'    => null,
    'kurir'   => null,
    'judul'   => 'Posisi Paket',
    'nada'    => 'slate',           // slate = paket keluar, rose = paket kembali
    'lintang' => null,              // koordinat tujuan, boleh kosong
    'bujur'   => null,
    'tujuan'  => null,              // alamat tujuan sebagai teks
])

@php
    $tahapan = \App\Services\PelacakanService::TAHAP;

    // Peta hanya digambar bila koordinatnya benar-benar ada. Menebak dari
    // nama kota hanya menghasilkan titik yang salah, dan titik yang salah
    // lebih menyesatkan daripada tidak ada peta sama sekali.
    $adaKoordinat = filled($lintang) && filled($bujur);
@endphp

{{-- Riwayat perjalanan paket. --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 space-y-4"
     x-data="{
        muat: false,
        sudah: false,
        data: null,

        async lacak() {
            if (this.muat) return;

            this.muat = true;
            try {
                const r = await fetch('{{ $alamat }}', { headers: { 'Accept': 'application/json' } });
                this.data = await r.json();
            } catch (e) {
                this.data = { ok: false, pesan: 'Tidak bisa menghubungi peladen.', riwayat: [], tahap: -1 };
            } finally {
                this.muat = false;
                this.sudah = true;
            }
        },

        waktuRapi(t) {
            if (! t) return '';
            const d = new Date(t);
            return isNaN(d) ? t : d.toLocaleString('id-ID', {
                day: '2-digit', month: 'short', year: 'numeric',
                hour: '2-digit', minute: '2-digit',
            });
        },
     }">

    <div class="flex items-center justify-between gap-3 border-b border-gray-100 pb-3">
        <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
            <i class="fa-solid fa-route text-{{ $nada }}-500"></i>
            {{ $judul }}
        </h3>

        @if($resi)
            <button type="button" @click="lacak()" :disabled="muat"
                    class="text-[11px] font-bold px-3 py-1.5 rounded-lg bg-{{ $nada }}-600 hover:bg-{{ $nada }}-700 text-white transition disabled:opacity-50">
                <span x-show="!muat && !sudah">Lacak Paket</span>
                <span x-show="muat" x-cloak>Memuat…</span>
                <span x-show="!muat && sudah" x-cloak>Muat Ulang</span>
            </button>
        @endif
    </div>

    <div class="grid grid-cols-2 gap-3 text-xs">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Kurir</p>
            <p class="font-bold text-gray-800 mt-0.5">{{ $kurir ?: '—' }}</p>
        </div>
        <div>
            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">No. Resi</p>
            <p class="font-mono font-bold text-gray-800 mt-0.5 select-all break-all">{{ $resi ?: '—' }}</p>
        </div>
    </div>

    @if(! $resi)
        <p class="text-[11px] text-gray-400 italic">Belum ada nomor resi, jadi belum bisa dilacak.</p>
    @endif

    {{-- ══════════ 1. --}}
    <div class="pt-1">
        <div class="flex items-start">
            @foreach($tahapan as $i => $t)
                <div class="flex-1 flex flex-col items-center relative">
                    {{-- Garis penghubung ke tahap berikutnya --}}
                    @if($i < count($tahapan) - 1)
                        <span class="absolute top-3.5 left-1/2 w-full h-0.5 -z-0"
                              :class="(data && data.tahap > {{ $i }}) ? 'bg-{{ $nada }}-500' : 'bg-gray-200'"></span>
                    @endif

                    <span class="relative z-10 w-7 h-7 rounded-full flex items-center justify-center text-[10px] border-2 transition"
                          :class="(data && data.tahap >= {{ $i }})
                              ? 'bg-{{ $nada }}-600 border-{{ $nada }}-600 text-white'
                              : 'bg-white border-gray-200 text-gray-300'">
                        <i class="fa-solid {{ $t['ikon'] }}"></i>
                    </span>

                    <span class="mt-1.5 text-[9px] font-bold text-center leading-tight"
                          :class="(data && data.tahap >= {{ $i }}) ? 'text-{{ $nada }}-700' : 'text-gray-400'">
                        {{ $t['label'] }}
                    </span>
                </div>
            @endforeach
        </div>

        {{-- Perjalanan yang gagal tidak digambar sebagai tahap, sebab memang
             keluar dari alurnya. Ditandai terpisah supaya tidak tersamar. --}}
        <template x-if="data && data.gagal">
            <div class="mt-3 rounded-xl bg-rose-50 border border-rose-200 px-3 py-2">
                <p class="text-[11px] font-bold text-rose-800">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                    <span x-text="data.gagal"></span>
                </p>
            </div>
        </template>
    </div>

    {{-- ══════════ Hasil pelacakan ══════════ --}}
    <template x-if="sudah && data && !data.ok">
        <div class="rounded-xl bg-amber-50 border border-amber-200 p-3">
            <p class="text-[11px] text-amber-800 leading-relaxed" x-text="data.pesan"></p>
        </div>
    </template>

    <template x-if="sudah && data && data.ok">
        <div class="space-y-3">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold uppercase tracking-wider"
                      x-text="data.status"></span>
                <span class="text-[10px] text-gray-400" x-text="data.riwayat.length + ' catatan'"></span>

                {{-- ══════════ 3. --}}
                <template x-if="data.tautan">
                    <a :href="data.tautan" target="_blank" rel="noopener"
                       class="ml-auto text-[10px] font-bold text-{{ $nada }}-600 hover:text-{{ $nada }}-800 underline">
                        Buka halaman resmi kurir
                        <i class="fa-solid fa-arrow-up-right-from-square text-[8px]"></i>
                    </a>
                </template>
            </div>

            {{-- Identitas kurir. --}}
            <template x-if="data.kurir_nama || data.kurir_plat">
                <div class="rounded-xl bg-gray-50 border border-gray-200 px-3 py-2 text-[11px] text-gray-700">
                    <span class="font-bold">Kurir:</span>
                    <span x-text="data.kurir_nama || '—'"></span>
                    <template x-if="data.kurir_plat">
                        <span> &middot; <span class="font-mono" x-text="data.kurir_plat"></span></span>
                    </template>
                    <template x-if="data.kurir_hp">
                        <span> &middot; <span x-text="data.kurir_hp"></span></span>
                    </template>
                </div>
            </template>

            {{-- Foto Bukti Diterima (Proof of Delivery / POD) --}}
            <template x-if="data.foto_bukti">
                <div class="p-3 bg-emerald-50/70 border border-emerald-200 rounded-xl space-y-2">
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-bold text-emerald-800 flex items-center gap-1.5">
                            <i class="fa-solid fa-camera"></i>
                            <span>Foto Bukti Diterima (Proof of Delivery)</span>
                        </p>
                        <template x-if="data.penerima_nama">
                            <span class="text-[10px] text-emerald-700 font-semibold" x-text="'Diterima oleh: ' + data.penerima_nama"></span>
                        </template>
                    </div>
                    <a :href="data.foto_bukti" target="_blank" rel="noopener" class="inline-block group relative overflow-hidden rounded-lg border border-emerald-300 w-28 h-28 bg-gray-100 shadow-sm">
                        <img :src="data.foto_bukti" alt="Bukti Serah Terima" class="w-full h-full object-cover group-hover:scale-105 transition" />
                        <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-[9px] font-bold text-center p-1">
                            Perbesar Foto
                        </div>
                    </a>
                </div>
            </template>

            {{-- Riwayat, terbaru di atas. --}}
            <ol class="relative border-l border-gray-200 ml-1.5 space-y-4 pt-1">
                <template x-for="(r, i) in data.riwayat" :key="i">
                    <li class="ml-4">
                        <span class="absolute -left-[5px] w-2.5 h-2.5 rounded-full"
                              :class="i === 0 ? 'bg-{{ $nada }}-600 ring-4 ring-{{ $nada }}-100' : 'bg-gray-300'"></span>
                        <p class="text-xs text-gray-800 leading-snug" x-text="r.keterangan || r.status"></p>
                        <p class="text-[10px] text-gray-400 mt-0.5" x-text="waktuRapi(r.waktu)"></p>
                    </li>
                </template>
            </ol>

            <template x-if="data.riwayat.length === 0">
                <p class="text-[11px] text-gray-400 italic">Kurir belum mencatat perjalanan paket ini.</p>
            </template>
        </div>
    </template>

    {{-- ══════════ 2. --}}
    @if($adaKoordinat)
        <div class="border-t border-gray-100 pt-3" x-data="{ petaBuka: false }">
            <button type="button" @click="petaBuka = ! petaBuka"
                    class="text-[11px] font-bold text-{{ $nada }}-600 hover:text-{{ $nada }}-800 flex items-center gap-1.5">
                <i class="fa-solid fa-location-dot"></i>
                <span x-text="petaBuka ? 'Tutup peta tujuan' : 'Lihat peta tujuan'"></span>
            </button>

            <div x-show="petaBuka" x-cloak class="mt-3 space-y-2">
                @if($tujuan)
                    <p class="text-[11px] text-gray-600 leading-relaxed">{{ $tujuan }}</p>
                @endif

                <div class="rounded-xl overflow-hidden border border-gray-200">
                    {{-- OpenStreetMap dipakai karena tidak menuntut kunci API
                         maupun biaya per tampilan. --}}
                    <iframe
                        x-bind:src="petaBuka
                            ? 'https://www.openstreetmap.org/export/embed.html?bbox={{ $bujur - 0.01 }},{{ $lintang - 0.008 }},{{ $bujur + 0.01 }},{{ $lintang + 0.008 }}&layer=mapnik&marker={{ $lintang }},{{ $bujur }}'
                            : ''"
                        class="w-full h-56 border-0" loading="lazy" title="Peta tujuan pengiriman"></iframe>
                </div>

                <a href="https://www.google.com/maps/search/?api=1&query={{ $lintang }},{{ $bujur }}"
                   target="_blank" rel="noopener"
                   class="inline-flex items-center gap-1.5 text-[11px] font-bold text-{{ $nada }}-600 hover:text-{{ $nada }}-800">
                    Buka di Google Maps
                    <i class="fa-solid fa-arrow-up-right-from-square text-[8px]"></i>
                </a>
            </div>
        </div>
    @endif
</div>
