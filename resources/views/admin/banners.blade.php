@extends('layouts.app')

@section('title', 'Kelola Banner Hero Slider')
@section('page_title', 'Kelola Banner Hero Slider')

@section('content')
@php
    // Ambil banner hero yang diurutkan berdasarkan sort_order, max 3
    $heroSlots = $banners->where('position', 'hero')->sortBy('sort_order')->values()->take(3);

    // Fungsi helper: cek apakah url adalah video
    $isVideo = fn($url) => preg_match('/\.(mp4|webm|ogg|mov)(\?|$)/i', (string) $url);
@endphp

<div class="space-y-6" x-data="{
    showAddModal: false,
    showEditModal: false,
    showDeleteModal: false,
    addSlotOrder: 1,
    editBanner: {},
    deleteBanner: {},
    previewAdd: null,
    previewEdit: null,
    isVideoAdd: false,
    isVideoEdit: false,
    openAdd(slotOrder) {
        this.addSlotOrder = slotOrder;
        this.previewAdd = null;
        this.isVideoAdd = false;
        this.showAddModal = true;
    },
    openEdit(banner) {
        this.editBanner = { ...banner };
        this.previewEdit = banner.image_url;
        this.isVideoEdit = banner.image_url && banner.image_url.match(/\.(mp4|webm|mov|ogg)/i) !== null;
        this.showEditModal = true;
    },
    openDelete(banner) {
        this.deleteBanner = { ...banner };
        this.showDeleteModal = true;
    }
}">

    {{-- ── Header ── --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <p class="text-sm text-gray-500">
                Kelola 3 slide banner utama yang tampil di halaman depan toko Anda.
            </p>
            <div class="mt-2 flex flex-wrap items-center gap-2">
                <div class="inline-flex items-center gap-2 bg-blue-50 border border-blue-200 text-blue-700 text-xs font-semibold px-3 py-1.5 rounded-lg">
                    <i class="fa-solid fa-ruler-combined text-blue-500"></i>
                    <span>Ukuran ideal: <strong>1920 × 1080 px</strong> (rasio 16:9)</span>
                </div>
                <div class="inline-flex items-center gap-2 bg-orange-50 border border-orange-200 text-orange-700 text-xs font-semibold px-3 py-1.5 rounded-lg">
                    <i class="fa-solid fa-layer-group"></i>
                    <span>{{ $heroSlots->count() }} / 3 Slot Terisi</span>
                </div>
            </div>
        </div>
        <a href="{{ env('FRONTEND_URL', 'https://recordshoes.com') }}" target="_blank"
           class="inline-flex items-center gap-2 bg-slate-800 text-white text-xs font-bold px-5 py-2.5 rounded-xl shadow hover:bg-slate-700 transition">
            <i class="fa-solid fa-arrow-up-right-from-square"></i>
            Lihat Tampilan Toko
        </a>
    </div>

    @if ($errors->any())
        <div class="flash-alert p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-exclamation text-red-500 text-base"></i>
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" onclick="this.closest('.flash-alert').remove()" class="text-red-500 hover:text-red-700 p-1">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
    @endif

    {{-- ── 3 Slot Hero Slider ── --}}
    <div class="grid grid-cols-1 gap-5">
        @for ($slot = 1; $slot <= 3; $slot++)
            @php
                $banner = $heroSlots->get($slot - 1);
            @endphp

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col md:flex-row">

                {{-- Label Slot --}}
                <div class="flex items-center justify-center md:w-12 shrink-0 bg-orange-50 border-r border-orange-100 py-3 md:py-0">
                    <span class="font-extrabold text-orange-500 text-xs tracking-widest uppercase"
                          style="writing-mode: vertical-rl; text-orientation: mixed; transform: rotate(180deg);">
                        Slide {{ $slot }}
                    </span>
                </div>

                @if($banner)
                    {{-- Preview Banner --}}
                    <div class="shrink-0 bg-gray-900 overflow-hidden relative" style="width:240px; aspect-ratio: 16/9; min-width:160px;">
                        @if($isVideo($banner->image_url))
                            <video src="{{ $banner->image_url }}" autoplay loop muted playsinline
                                style="width:100%; height:100%; object-fit:cover; display:block;"></video>
                        @else
                            <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}"
                                style="width:100%; height:100%; object-fit:cover; display:block;"
                                onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=700&q=80';">
                        @endif
                        <div class="absolute top-2 left-2 flex gap-1 z-10">
                            @if($banner->is_active)
                                <span class="bg-green-500 text-white text-[9px] font-bold px-2 py-0.5 rounded-full">Aktif</span>
                            @else
                                <span class="bg-gray-400 text-white text-[9px] font-bold px-2 py-0.5 rounded-full">Nonaktif</span>
                            @endif
                            @if($isVideo($banner->image_url))
                                <span class="bg-purple-600/80 text-white text-[9px] font-bold px-2 py-0.5 rounded-full">
                                    <i class="fa-solid fa-video mr-0.5"></i>Video
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Info Banner --}}
                    <div class="flex-1 p-5 flex flex-col justify-between gap-3">
                        <div>
                            <h3 class="font-black text-gray-800 text-sm">{{ $banner->title }}</h3>
                            @if($banner->subtitle)
                                <p class="text-xs text-gray-500 mt-1 leading-relaxed">{{ Str::limit($banner->subtitle, 90) }}</p>
                            @endif
                            @if($banner->link)
                                <p class="text-[11px] text-gray-500 mt-2 flex items-center gap-1.5 font-medium">
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-orange-500"></i>
                                    <span>Tautan: <strong class="text-slate-800">{{ $banner->link }}</strong></span>
                                </p>
                            @else
                                <p class="text-[11px] text-gray-400 mt-2 italic">
                                    <i class="fa-solid fa-link-slash text-[10px] mr-1"></i>Hanya banner (tanpa tautan)
                                </p>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-2 pt-3 border-t border-gray-50">
                            <form action="{{ route('admin.banners.toggle', $banner) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit"
                                    class="text-xs font-bold px-3.5 py-1.5 rounded-lg border transition
                                        {{ $banner->is_active ? 'border-orange-200 text-orange-600 hover:bg-orange-50' : 'border-green-200 text-green-600 hover:bg-green-50' }}">
                                    <i class="fa-solid {{ $banner->is_active ? 'fa-eye-slash' : 'fa-eye' }} mr-1"></i>
                                    {{ $banner->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>

                            <button type="button"
                                @click="openEdit({{ json_encode([
                                    'id'        => $banner->id,
                                    'title'     => $banner->title,
                                    'subtitle'  => $banner->subtitle ?? '',
                                    'link'      => $banner->link ?? '',
                                    'sort_order'=> $banner->sort_order,
                                    'is_active' => $banner->is_active,
                                    'image_url' => $banner->image_url,
                                ]) }})"
                                class="text-xs font-bold px-3.5 py-1.5 rounded-lg border border-blue-200 text-blue-600 hover:bg-blue-50 transition">
                                <i class="fa-solid fa-pen-to-square mr-1"></i>Edit / Ganti Gambar
                            </button>

                            <button type="button"
                                @click="openDelete({{ json_encode(['id' => $banner->id, 'title' => $banner->title, 'slot' => $slot]) }})"
                                class="text-xs font-bold px-3.5 py-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 transition">
                                <i class="fa-solid fa-trash-can mr-1"></i>Hapus
                            </button>
                        </div>
                    </div>

                @else
                    {{-- Slot Kosong --}}
                    <div class="flex-1 flex items-center justify-center p-10 bg-gray-50/50">
                        <div class="text-center">
                            <div class="w-16 h-16 rounded-2xl bg-orange-50 border-2 border-dashed border-orange-200 flex items-center justify-center mx-auto mb-3">
                                <i class="fa-solid fa-plus text-2xl text-orange-300"></i>
                            </div>
                            <h3 class="text-sm font-bold text-gray-400">Slot {{ $slot }} Kosong</h3>
                            <p class="text-xs text-gray-400 mt-1 mb-4">Tambahkan banner untuk slide ke-{{ $slot }}</p>
                            <button type="button" @click="openAdd({{ $slot - 1 }})"
                                class="inline-flex items-center gap-2 bg-orange-600 text-white text-xs font-bold px-5 py-2.5 rounded-xl shadow hover:bg-orange-700 transition">
                                <i class="fa-solid fa-plus"></i>
                                Isi Slot {{ $slot }}
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        @endfor
    </div>

    @if($heroSlots->count() >= 3)
        <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl flex items-center gap-3 text-amber-700 text-xs font-semibold">
            <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
            <span>Ketiga slot hero slider sudah terisi. Untuk menambah banner baru, hapus salah satu banner yang ada terlebih dahulu.</span>
        </div>
    @endif


    {{-- ════════════════════════════════ --}}
    {{-- MODAL: Tambah Banner Baru        --}}
    {{-- ════════════════════════════════ --}}
    <div x-show="showAddModal"
        class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4"
        x-transition style="display:none;">
        <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full flex flex-col" style="max-height:90vh;" @click.away="showAddModal = false">
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between shrink-0 rounded-t-3xl">
                <div>
                    <h3 class="font-extrabold text-gray-900 text-base">Tambah Banner — Slide <span x-text="addSlotOrder + 1"></span></h3>
                    <p class="text-xs text-gray-400 mt-0.5">Slider yang tampil di halaman depan toko.</p>
                </div>
                <button type="button" @click="showAddModal = false" class="text-gray-400 hover:text-gray-600 p-1">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <form id="formAdd" action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 overflow-hidden">
                @csrf
                <input type="hidden" name="position" value="hero">
                <input type="hidden" name="sort_order" :value="addSlotOrder">

                <div class="p-6 space-y-4 overflow-y-auto flex-1">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">
                            Foto / Video Banner <span class="text-red-500">*</span>
                        </label>
                        <div class="border-2 border-dashed border-gray-200 rounded-2xl p-4 text-center hover:border-orange-400 transition cursor-pointer bg-gray-50"
                            onclick="document.getElementById('addImageInput').click()">
                            <template x-if="!previewAdd">
                                <div class="py-4">
                                    <i class="fa-solid fa-cloud-arrow-up text-3xl text-gray-300 mb-2 block"></i>
                                    <p class="text-xs text-gray-400 font-semibold">Klik untuk upload foto / video</p>
                                    <p class="text-[10px] text-gray-300 mt-1">JPG, PNG, WEBP, MP4, MOV, WEBM — maks. {{ config('banner.maks_gambar_mb', 10) }}MB gambar / {{ config('banner.maks_video_mb', 100) }}MB video</p>
                                    <p class="text-[10px] text-gray-300">Ukuran ideal: 1920 × 1080 px (16:9)</p>
                                </div>
                            </template>
                            <template x-if="previewAdd">
                                <div>
                                    <div class="relative w-full rounded-xl overflow-hidden bg-gray-900" style="aspect-ratio: 16/9;">
                                        <template x-if="isVideoAdd">
                                            <video :src="previewAdd" autoplay loop muted playsinline
                                                style="width:100%; height:100%; object-fit:cover; display:block;"></video>
                                        </template>
                                        <template x-if="!isVideoAdd">
                                            <img :src="previewAdd" style="width:100%; height:100%; object-fit:cover; display:block;">
                                        </template>
                                    </div>
                                    <p class="text-[10px] text-gray-400 mt-2 font-semibold">
                                        <i class="fa-solid fa-crop-simple mr-1"></i>Tampilan sesuai frame 16:9 — klik untuk ganti file
                                    </p>
                                </div>
                            </template>
                            <input id="addImageInput" type="file" name="image" accept="image/*,video/*" class="hidden" required
                                @change="previewAdd = URL.createObjectURL($event.target.files[0]); isVideoAdd = $event.target.files[0].type.startsWith('video/')">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Judul Banner <span class="text-red-500">*</span></label>
                        <input type="text" name="title" required
                            class="w-full border border-gray-200 rounded-xl py-2.5 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500"
                            placeholder="Contoh: NEW COLLECTION 2026">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Keterangan Banner <span class="text-gray-400 font-normal">(opsional)</span></label>
                        <textarea name="subtitle" rows="2"
                            class="w-full border border-gray-200 rounded-xl py-2.5 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500"
                            placeholder="Keterangan singkat..."></textarea>
                    </div>

                    <div x-data="{ modeAdd: 'select' }">
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-gray-600">Halaman Tujuan (Saat Banner Diklik)</label>
                            <button type="button" @click="modeAdd = (modeAdd === 'select' ? 'custom' : 'select')"
                                    class="text-[10px] font-bold text-orange-600 hover:underline">
                                <span x-text="modeAdd === 'select' ? '+ Tulis URL Kustom' : '&larr; Pilih dari Menu'"></span>
                            </button>
                        </div>
                        <div x-show="modeAdd === 'select'">
                            <select name="link"
                                class="w-full border border-gray-200 rounded-xl py-2.5 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 bg-white">
                                <option value="">— Tidak ada tautan (hanya tampilan) —</option>
                                <optgroup label="🏠 Halaman Utama & Info">
                                    <option value="/">🏠 Beranda Toko (/)</option>
                                    <option value="/products" selected>📦 Semua Produk / Katalog</option>
                                    <option value="/affiliate">🤝 Program Referral & Komisi</option>
                                    <option value="/about">ℹ️ Tentang Kami</option>
                                    <option value="/kontak">📞 Hubungi Kami</option>
                                    <option value="/faq">❓ FAQ & Bantuan</option>
                                    <option value="/pengiriman-retur">🔄 Info Pengiriman & Retur</option>
                                </optgroup>
                                @if(isset($categories) && $categories->isNotEmpty())
                                    <optgroup label="🏷️ Kategori Produk">
                                        @foreach($categories as $cat)
                                            <option value="/category/{{ $cat->slug }}">🏷️ {{ $cat->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endif
                                @if(isset($products) && $products->isNotEmpty())
                                    <optgroup label="👟 Produk Spesifik">
                                        @foreach($products as $prod)
                                            <option value="/products/{{ $prod->slug }}">👟 {{ $prod->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            </select>
                        </div>
                        <div x-show="modeAdd === 'custom'" x-cloak>
                            <input type="text" name="link_custom" placeholder="Contoh: /products?category=sneaker atau https://..."
                                   class="w-full border border-orange-300 bg-orange-50/30 rounded-xl py-2.5 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500"
                                   @input="$el.form.querySelector('select[name=link]').value = $event.target.value">
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1">Customer akan diarahkan ke halaman ini saat mengeklik banner.</p>
                    </div>

                    <div>
                        <label class="flex items-center gap-2 text-xs font-bold text-gray-600 cursor-pointer">
                            <input type="checkbox" name="is_active" checked class="w-4 h-4 accent-orange-600 rounded">
                            <span>Aktifkan banner ini segera</span>
                        </label>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3 shrink-0 bg-white rounded-b-3xl">
                    <button type="button" @click="showAddModal = false"
                        class="text-xs font-bold px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="text-xs font-bold px-6 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white shadow transition">
                        <i class="fa-solid fa-plus mr-1.5"></i>Simpan Banner
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ════════════════════════════════ --}}
    {{-- MODAL: Edit Banner               --}}
    {{-- ════════════════════════════════ --}}
    <div x-show="showEditModal"
        class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4"
        x-transition style="display:none;">
        <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full flex flex-col" style="max-height:90vh;" @click.away="showEditModal = false">
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between shrink-0 rounded-t-3xl">
                <div>
                    <h3 class="font-extrabold text-gray-900 text-base">Edit Banner</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Perbarui gambar, teks, atau tautan banner.</p>
                </div>
                <button type="button" @click="showEditModal = false" class="text-gray-400 hover:text-gray-600 p-1">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <form :action="'{{ url('admin/banners') }}/' + editBanner.id" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 overflow-hidden">
                @csrf
                @method('PUT')
                <input type="hidden" name="position" value="hero">
                <input type="hidden" name="sort_order" :value="editBanner.sort_order">

                <div class="p-6 space-y-4 overflow-y-auto flex-1">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">
                            Foto / Video Banner <span class="text-gray-400 font-normal">(kosongkan jika tidak diganti)</span>
                        </label>
                        <div class="border-2 border-dashed border-gray-200 rounded-2xl p-4 text-center hover:border-orange-400 transition cursor-pointer bg-gray-50"
                            onclick="document.getElementById('editImageInput').click()">
                            <template x-if="previewEdit">
                                <div>
                                    <div class="relative w-full rounded-xl overflow-hidden bg-gray-900" style="aspect-ratio: 16/9;">
                                        <template x-if="isVideoEdit">
                                            <video :src="previewEdit" autoplay loop muted playsinline
                                                style="width:100%; height:100%; object-fit:cover; display:block;"></video>
                                        </template>
                                        <template x-if="!isVideoEdit">
                                            <img :src="previewEdit" style="width:100%; height:100%; object-fit:cover; display:block;">
                                        </template>
                                    </div>
                                    <p class="text-[10px] text-gray-400 mt-2 font-semibold">
                                        <i class="fa-solid fa-crop-simple mr-1"></i>Tampilan sesuai frame 16:9 — klik untuk ganti file
                                    </p>
                                </div>
                            </template>
                            <input id="editImageInput" type="file" name="image" accept="image/*,video/*" class="hidden"
                                @change="previewEdit = URL.createObjectURL($event.target.files[0]); isVideoEdit = $event.target.files[0].type.startsWith('video/')">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Judul Banner <span class="text-red-500">*</span></label>
                        <input type="text" name="title" required :value="editBanner.title"
                            class="w-full border border-gray-200 rounded-xl py-2.5 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Keterangan Banner <span class="text-gray-400 font-normal">(opsional)</span></label>
                        <textarea name="subtitle" rows="2"
                            class="w-full border border-gray-200 rounded-xl py-2.5 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500"
                            x-text="editBanner.subtitle"></textarea>
                    </div>

                    <div x-data="{ modeEdit: 'select' }">
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-gray-600">Halaman Tujuan (Saat Banner Diklik)</label>
                            <button type="button" @click="modeEdit = (modeEdit === 'select' ? 'custom' : 'select')"
                                    class="text-[10px] font-bold text-orange-600 hover:underline">
                                <span x-text="modeEdit === 'select' ? '+ Tulis URL Kustom' : '&larr; Pilih dari Menu'"></span>
                            </button>
                        </div>
                        <div x-show="modeEdit === 'select'">
                            <select name="link" x-model="editBanner.link"
                                class="w-full border border-gray-200 rounded-xl py-2.5 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 bg-white">
                                <option value="">— Tidak ada tautan (hanya tampilan) —</option>
                                <optgroup label="🏠 Halaman Utama & Info">
                                    <option value="/">🏠 Beranda Toko (/)</option>
                                    <option value="/products">📦 Semua Produk / Katalog</option>
                                    <option value="/affiliate">🤝 Program Referral & Komisi</option>
                                    <option value="/about">ℹ️ Tentang Kami</option>
                                    <option value="/kontak">📞 Hubungi Kami</option>
                                    <option value="/faq">❓ FAQ & Bantuan</option>
                                    <option value="/pengiriman-retur">🔄 Info Pengiriman & Retur</option>
                                </optgroup>
                                @if(isset($categories) && $categories->isNotEmpty())
                                    <optgroup label="🏷️ Kategori Produk">
                                        @foreach($categories as $cat)
                                            <option value="/category/{{ $cat->slug }}">🏷️ {{ $cat->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endif
                                @if(isset($products) && $products->isNotEmpty())
                                    <optgroup label="👟 Produk Spesifik">
                                        @foreach($products as $prod)
                                            <option value="/products/{{ $prod->slug }}">👟 {{ $prod->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            </select>
                        </div>
                        <div x-show="modeEdit === 'custom'" x-cloak>
                            <input type="text" name="link" x-model="editBanner.link"
                                   placeholder="Contoh: /products?search=sneaker atau https://..."
                                   class="w-full border border-orange-300 bg-orange-50/30 rounded-xl py-2.5 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500">
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1">Customer akan diarahkan ke halaman ini saat mengeklik banner.</p>
                    </div>

                    <div>
                        <label class="flex items-center gap-2 text-xs font-bold text-gray-600 cursor-pointer">
                            <input type="checkbox" name="is_active" :checked="editBanner.is_active"
                                class="w-4 h-4 accent-orange-600 rounded">
                            <span>Aktifkan banner ini</span>
                        </label>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3 shrink-0 bg-white rounded-b-3xl">
                    <button type="button" @click="showEditModal = false"
                        class="text-xs font-bold px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="text-xs font-bold px-6 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white shadow transition">
                        <i class="fa-solid fa-floppy-disk mr-1.5"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ════════════════════════════════ --}}
    {{-- MODAL: Konfirmasi Hapus          --}}
    {{-- ════════════════════════════════ --}}
    <div x-show="showDeleteModal"
        class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4"
        x-transition style="display:none;">
        <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full" @click.away="showDeleteModal = false">
            <div class="p-8 text-center">
                <div class="w-16 h-16 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-5">
                    <i class="fa-solid fa-trash-can text-2xl text-red-500"></i>
                </div>
                <h2 class="font-black text-gray-900 text-lg mb-2">Hapus Banner?</h2>
                <p class="text-sm text-gray-500 mb-1">
                    Anda akan menghapus banner <strong x-text="'Slide ' + deleteBanner.slot"></strong>:
                </p>
                <p class="text-sm font-bold text-gray-800 mb-5" x-text="'&quot;' + deleteBanner.title + '&quot;'"></p>
                <p class="text-xs text-red-500 bg-red-50 border border-red-100 rounded-xl px-4 py-2.5 mb-6">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                    Gambar banner akan dihapus permanen dan tidak bisa dikembalikan.
                </p>
                <form :action="'{{ url('admin/banners') }}/' + deleteBanner.id" method="POST" class="flex gap-3">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="showDeleteModal = false"
                        class="flex-1 text-xs font-bold px-4 py-3 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 text-xs font-bold px-4 py-3 rounded-xl bg-red-600 hover:bg-red-700 text-white shadow transition">
                        <i class="fa-solid fa-trash-can mr-1.5"></i>Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
    if (typeof Alpine === 'undefined') {
        document.write('<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"><\/script>');
    }
</script>
@endsection
