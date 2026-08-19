@extends('layouts.app')

@section('title', 'Pengaturan Website')
@section('page_title', 'Pengaturan Website')
@section('page_subtitle', 'Identitas toko, kontak, pengiriman, pembayaran, dan operasional.')

@section('content')
<div class="space-y-6" x-data="{ tab: '{{ $active }}', dirty: false }" @change="dirty = true" @input="dirty = true">

    @if ($errors->any())
        <div class="flash-alert p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm shadow-sm">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        <input type="hidden" name="tab" :value="tab">

        <div class="flex flex-col lg:flex-row gap-6">

            {{-- ── Navigasi Tab ── --}}
            <div class="lg:w-64 shrink-0">
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-2 lg:sticky lg:top-24">
                    <div class="flex lg:flex-col gap-1 overflow-x-auto">
                        @foreach($order as $groupKey)
                            @php
                                [$label, $icon, $hint] = $groups[$groupKey] ?? [ucfirst($groupKey), 'fa-folder', ''];
                                $count = $settings[$groupKey]->count();
                            @endphp
                            <button type="button" @click="tab = '{{ $groupKey }}'"
                                class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold transition text-left shrink-0 w-full"
                                :class="tab === '{{ $groupKey }}'
                                    ? 'bg-orange-600 text-white shadow-md shadow-orange-900/20'
                                    : 'text-gray-600 hover:bg-gray-50'">
                                <i class="fa-solid {{ $icon }} w-4 text-center"></i>
                                <span class="flex-1 whitespace-nowrap">{{ $label }}</span>
                                <span class="text-[10px] px-1.5 py-0.5 rounded-full shrink-0"
                                      :class="tab === '{{ $groupKey }}' ? 'bg-white/20' : 'bg-gray-100 text-gray-500'">{{ $count }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ── Isi Tab ── --}}
            <div class="flex-1 min-w-0 space-y-6">
                @foreach($order as $groupKey)
                    @php
                        [$label, $icon, $hint] = $groups[$groupKey] ?? [ucfirst($groupKey), 'fa-folder', ''];
                    @endphp

                    <div x-show="tab === '{{ $groupKey }}'" x-cloak x-transition.opacity
                         class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

                        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                            <span class="w-9 h-9 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center shrink-0">
                                <i class="fa-solid {{ $icon }}"></i>
                            </span>
                            <div>
                                <h4 class="text-sm font-black text-gray-800">{{ $label }}</h4>
                                <p class="text-[11px] text-gray-400">{{ $hint }}</p>
                            </div>
                        </div>

                        <div class="divide-y divide-gray-50">
                            @foreach($settings[$groupKey] as $setting)
                                <div class="px-6 py-5 grid grid-cols-1 md:grid-cols-3 gap-4 items-start">

                                    {{-- Label & keterangan --}}
                                    <div class="md:col-span-1">
                                        <label class="block text-xs font-bold text-gray-700">{{ $setting->label }}</label>
                                        @if($setting->description)
                                            <p class="text-[10px] text-gray-400 mt-1 leading-relaxed">{{ $setting->description }}</p>
                                        @endif
                                        <code class="text-[9px] text-gray-300">{{ $setting->key }}</code>
                                    </div>

                                    {{-- Kolom isian sesuai tipe --}}
                                    <div class="md:col-span-2">

                                        {{-- Teks pendek --}}
                                        @if($setting->type === 'string')
                                            <input type="text" name="settings[{{ $setting->key }}]" value="{{ old('settings.' . $setting->key, $setting->value) }}"
                                                   class="w-full border border-gray-200 rounded-xl py-2.5 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500"
                                                   placeholder="Belum diisi">

                                        {{-- Teks panjang --}}
                                        @elseif($setting->type === 'text')
                                            <textarea name="settings[{{ $setting->key }}]" rows="3"
                                                      class="w-full border border-gray-200 rounded-xl py-2.5 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500"
                                                      placeholder="Belum diisi">{{ old('settings.' . $setting->key, $setting->value) }}</textarea>

                                        {{-- Angka --}}
                                        @elseif($setting->type === 'integer')
                                            <div class="relative max-w-xs">
                                                @if($setting->key === 'free_shipping_min')
                                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 font-bold">Rp</span>
                                                @endif
                                                <input type="number" min="0" name="settings[{{ $setting->key }}]"
                                                       value="{{ old('settings.' . $setting->key, $setting->value ?? 0) }}"
                                                       class="w-full border border-gray-200 rounded-xl py-2.5 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500
                                                              {{ $setting->key === 'free_shipping_min' ? 'pl-9' : '' }}">
                                            </div>

                                        {{-- Saklar on/off --}}
                                        @elseif($setting->type === 'boolean')
                                            @php $on = (bool) old('settings.' . $setting->key, $setting->value); @endphp
                                            <label class="inline-flex items-center gap-3 cursor-pointer"
                                                   x-data="{ on: {{ $on ? 'true' : 'false' }} }">
                                                <input type="checkbox" name="settings[{{ $setting->key }}]" value="1"
                                                       x-model="on" class="sr-only">
                                                <span class="w-12 h-6 rounded-full transition-colors duration-200 relative shrink-0"
                                                      :class="on ? 'bg-emerald-500' : 'bg-gray-300'">
                                                    <span class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-200"
                                                          :class="on ? 'translate-x-6' : 'translate-x-0'"></span>
                                                </span>
                                                <span class="text-xs font-bold"
                                                      :class="on ? 'text-emerald-600' : 'text-gray-400'"
                                                      x-text="on ? 'Aktif' : 'Nonaktif'"></span>
                                            </label>

                                            @if($setting->key === 'maintenance_mode')
                                                <p class="text-[10px] text-amber-600 font-semibold mt-2">
                                                    <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                                                    Saat aktif, pembeli tidak bisa mengakses toko. Panel admin tetap bisa dibuka.
                                                </p>
                                            @endif

                                        {{-- Daftar kurir (json) --}}
                                        @elseif($setting->type === 'json' && $setting->key === 'couriers')
                                            @php $selected = json_decode($setting->value ?? '[]', true) ?: []; @endphp
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($couriers as $code => $courierLabel)
                                                    <label class="cursor-pointer">
                                                        <input type="checkbox" name="settings[{{ $setting->key }}][]" value="{{ $code }}"
                                                               @checked(in_array($code, $selected, true)) class="peer sr-only">
                                                        <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3.5 py-2 rounded-xl border-2 transition
                                                                     border-gray-200 text-gray-400 bg-white
                                                                     peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700">
                                                            <i class="fa-solid fa-truck text-[10px]"></i>
                                                            {{ $courierLabel }}
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>
                                            <p class="text-[10px] text-gray-400 mt-2">Kurir yang tidak dicentang tidak akan muncul di halaman checkout.</p>

                                        {{-- JSON bebas --}}
                                        @elseif($setting->type === 'json')
                                            <textarea name="settings[{{ $setting->key }}]" rows="3"
                                                      class="w-full border border-gray-200 rounded-xl py-2.5 px-3 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">{{ $setting->value }}</textarea>

                                        {{-- Gambar --}}
                                        @elseif($setting->type === 'image')
                                            <div x-data="{ preview: @js($setting->value ? asset('storage/' . $setting->value) : null), remove: false }"
                                                 class="flex items-start gap-4">

                                                {{-- Kotak pratinjau --}}
                                                <div class="w-24 h-24 rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden shrink-0 relative">
                                                    <template x-if="preview && !remove">
                                                        <img :src="preview" class="w-full h-full object-contain p-1.5">
                                                    </template>
                                                    <template x-if="!preview || remove">
                                                        <i class="fa-regular fa-image text-2xl text-gray-300"></i>
                                                    </template>
                                                </div>

                                                <div class="flex-1 min-w-0">
                                                    <input type="file" name="files[{{ $setting->key }}]" accept="image/*"
                                                           id="file-{{ $setting->key }}" class="hidden"
                                                           @change="preview = URL.createObjectURL($event.target.files[0]); remove = false">

                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <label for="file-{{ $setting->key }}"
                                                               class="cursor-pointer inline-flex items-center gap-2 text-xs font-bold px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                                                            <i class="fa-solid fa-upload"></i>
                                                            <span x-text="preview && !remove ? 'Ganti Gambar' : 'Pilih Gambar'"></span>
                                                        </label>

                                                        <template x-if="preview && !remove">
                                                            <label class="cursor-pointer inline-flex items-center gap-2 text-xs font-bold px-4 py-2 rounded-xl border border-red-200 text-red-500 hover:bg-red-50 transition">
                                                                <input type="checkbox" name="remove[{{ $setting->key }}]" value="1" x-model="remove" class="sr-only">
                                                                <i class="fa-solid fa-trash"></i>
                                                                Hapus
                                                            </label>
                                                        </template>

                                                        <template x-if="remove">
                                                            <span class="inline-flex items-center gap-2 text-xs font-bold text-red-600">
                                                                <i class="fa-solid fa-circle-exclamation"></i>
                                                                Akan dihapus saat disimpan
                                                                <button type="button" @click="remove = false" class="underline">batal</button>
                                                                <input type="hidden" name="remove[{{ $setting->key }}]" value="1">
                                                            </span>
                                                        </template>
                                                    </div>

                                                    <p class="text-[10px] text-gray-400 mt-2">JPG, PNG, WEBP, atau SVG. Maksimal 4MB.</p>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ── Bar Simpan Melayang ── --}}
        <div class="sticky bottom-4 z-30">
            <div class="bg-slate-900/95 backdrop-blur text-white rounded-2xl px-5 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-xl border border-slate-700">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-gear text-orange-400"></i>
                    <span class="text-xs font-bold" x-show="!dirty">Semua pengaturan tersimpan.</span>
                    <span class="text-xs font-bold text-amber-300" x-show="dirty" x-cloak>
                        <i class="fa-solid fa-pen mr-1"></i>Ada perubahan yang belum disimpan.
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.settings') }}"
                       class="text-xs font-bold px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 transition">
                        Muat Ulang
                    </a>
                    <button type="submit"
                            class="text-xs font-bold px-6 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white shadow transition">
                        <i class="fa-solid fa-floppy-disk mr-1.5"></i>Simpan Pengaturan
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
