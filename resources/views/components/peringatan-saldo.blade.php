@php
    /*
     * Peringatan saldo Biteship.
     *
     * Ditempatkan di layout admin supaya terlihat di halaman mana pun, bukan
     * hanya di halaman saldo — saldo habis menghentikan seluruh pengiriman,
     * jadi kabarnya tidak boleh menunggu admin kebetulan membuka halaman yang
     * tepat.
     *
     * Keadaan "aman" dan "diam" sengaja tidak menampilkan apa-apa; spanduk yang
     * selalu ada akan berhenti dibaca orang.
     */
    $ringkasan = app(\App\Services\SaldoBiteshipService::class)->ringkasan();
    $nada = $ringkasan['nada'];
@endphp

@if(in_array($nada, ['bahaya', 'awas'], true))
    @php
        $gaya = $nada === 'bahaya'
            ? ['bg' => 'bg-rose-50',   'garis' => 'border-rose-200',   'teks' => 'text-rose-800',
               'ikon' => 'text-rose-500',   'tombol' => 'bg-rose-600 hover:bg-rose-700',
               'lambang' => 'fa-circle-exclamation']
            : ['bg' => 'bg-amber-50',  'garis' => 'border-amber-200',  'teks' => 'text-amber-800',
               'ikon' => 'text-amber-500',  'tombol' => 'bg-amber-600 hover:bg-amber-700',
               'lambang' => 'fa-triangle-exclamation'];
    @endphp

    <div class="{{ $gaya['bg'] }} border {{ $gaya['garis'] }} rounded-2xl px-5 py-4 mb-5 flex items-start gap-3">
        <i class="fa-solid {{ $gaya['lambang'] }} {{ $gaya['ikon'] }} mt-0.5"></i>

        <div class="flex-1 min-w-0">
            <p class="text-sm font-bold {{ $gaya['teks'] }}">{{ $ringkasan['judul'] }}</p>
            <p class="text-xs {{ $gaya['teks'] }} opacity-90 mt-1 leading-relaxed">
                {{ $ringkasan['pesan'] }}
            </p>

            @if($ringkasan['dicatat'])
                <p class="text-[10px] {{ $gaya['teks'] }} opacity-60 mt-1.5">
                    Perkiraan dihitung dari saldo yang dicatat
                    {{ $ringkasan['dicatat']->diffForHumans() }}. Biaya kecil per panggilan API
                    belum terhitung, jadi saldo sebenarnya bisa sedikit lebih rendah.
                </p>
            @endif
        </div>

        <a href="{{ route('admin.saldo-biteship') }}"
           class="{{ $gaya['tombol'] }} text-white text-[11px] font-bold px-3.5 py-2 rounded-xl transition shrink-0">
            Catat Saldo
        </a>
    </div>
@endif
