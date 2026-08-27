@extends('layouts.app')

@section('title', 'Kelola Banner')
@section('page_title', 'Kelola Banner')

@section('content')
@php
    // Kuota banner hero: maksimal 3 (sesuai batasan di AdminWebBannerController)
    $heroCount = $banners->where('position', 'hero')->count();
    $heroLimit = 3;
    $heroFull  = $heroCount >= $heroLimit;
@endphp
<div class="space-y-6" x-data="{
    showAddModal: false,
    showEditModal: false,
    showPreviewModal: false,
    editBanner: {},
    previewAdd: null,
    previewEdit: null,
    isVideoAdd: false,
    isVideoEdit: false,
    openEdit(banner) {
        this.editBanner = { ...banner };
        this.previewEdit = banner.image_url;
        this.isVideoEdit = banner.image_url.match(/\.(mp4|webm|mov|ogg)/i) !== null;
        this.showEditModal = true;
    }
}">

    {{-- ── Header ── --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <p class="text-sm text-gray-500">Kelola banner/slider yang tampil di halaman utama (mendukung foto & video landscape).</p>
            {{-- Panduan Ukuran Banner --}}
            <div class="mt-2 flex flex-wrap items-center gap-2">
                <div class="inline-flex items-center gap-2 bg-blue-50 border border-blue-200 text-blue-700 text-xs font-semibold px-3 py-1.5 rounded-lg">
                    <i class="fa-solid fa-ruler-combined text-blue-500"></i>
                    <span>Ukuran banner: <strong>1920 × 1080 piksel</strong> (rasio 16:9, HD seperti video YouTube) — berlaku untuk foto maupun video. Ukuran lain tetap bisa diunggah namun sisi-sisinya akan terpotong.</span>
                </div>
                {{-- Kuota banner hero --}}
                <div class="inline-flex items-center gap-2 text-xs font-bold px-3 py-1.5 rounded-lg border
                    {{ $heroFull ? 'bg-amber-50 border-amber-200 text-amber-700' : 'bg-emerald-50 border-emerald-200 text-emerald-700' }}">
                    <i class="fa-solid {{ $heroFull ? 'fa-triangle-exclamation' : 'fa-layer-group' }}"></i>
                    <span>Banner Hero: {{ $heroCount }} / {{ $heroLimit }}{{ $heroFull ? ' — kuota penuh' : '' }}</span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <button @click="showPreviewModal = true"
                class="inline-flex items-center gap-2 bg-slate-800 text-white text-xs font-bold px-5 py-2.5 rounded-xl shadow hover:bg-slate-700 transition">
                <i class="fa-solid fa-eye"></i>
                Lihat Preview Toko
            </button>
            <button @click="showAddModal = true; previewAdd = null; isVideoAdd = false;"
                class="inline-flex items-center gap-2 bg-orange-600 text-white text-xs font-bold px-5 py-2.5 rounded-xl shadow hover:bg-orange-700 transition">
                <i class="fa-solid fa-plus"></i>
                Tambah Banner Baru
            </button>
        </div>
    </div>

    @if ($errors->any())
        <div class="flash-alert p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-exclamation text-red-500 text-base"></i>
                <div>
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" onclick="this.closest('.flash-alert').remove()" class="text-red-500 hover:text-red-700 transition p-1">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
    @endif

    {{-- ── Daftar Banner ── --}}
    @if($banners->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-16 text-center">
            <i class="fa-regular fa-image text-4xl text-gray-300 mb-4 block"></i>
            <h3 class="text-sm font-bold text-gray-500">Belum ada banner</h3>
            <p class="text-xs text-gray-400 mt-1">Klik tombol <strong>"Tambah Banner Baru"</strong> untuk mulai menambahkan banner.</p>
        </div>
    @else
        <div class="grid grid-cols-1 gap-5">
            @foreach($banners as $banner)
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col md:flex-row">
                    {{-- Preview Banner (Foto/Video) — dibingkai rasio 16:9 persis seperti di frontend --}}
                    <div class="md:w-72 shrink-0 bg-gray-900 overflow-hidden relative" style="aspect-ratio: 16/9;">
                        @if(Str::endsWith($banner->image, ['.mp4', '.webm', '.ogg', '.mov']))
                            <video src="{{ $banner->image_url }}" autoplay loop muted playsinline
                                style="width:100%; height:100%; object-fit:cover; display:block;"></video>
                        @else
                            <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}"
                                style="width:100%; height:100%; object-fit:cover; display:block;"
                                onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=700&q=80';">
                        @endif
                        <div class="absolute top-2 left-2 flex gap-1 z-10">
                            @if($banner->is_active)
                                <span class="bg-green-500 text-white text-[9px] font-bold px-2 py-0.5 rounded-full uppercase">Aktif</span>
                            @else
                                <span class="bg-gray-400 text-white text-[9px] font-bold px-2 py-0.5 rounded-full uppercase">Nonaktif</span>
                            @endif
                            <span class="bg-slate-800/90 text-white text-[9px] font-bold px-2 py-0.5 rounded-full uppercase">Hero Slider</span>
                            @if(Str::endsWith($banner->image, ['.mp4', '.webm', '.ogg', '.mov']))
                                <span class="bg-purple-600/80 text-white text-[9px] font-bold px-2 py-0.5 rounded-full uppercase">
                                    <i class="fa-solid fa-video mr-0.5"></i>Video
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Info Banner --}}
                    <div class="flex-1 p-5 flex flex-col justify-between gap-4">
                        <div>
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <h3 class="font-black text-gray-800 text-sm">{{ $banner->title }}</h3>
                                    @if($banner->subtitle)
                                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">{{ Str::limit($banner->subtitle, 100) }}</p>
                                    @endif
                                </div>
                                <span class="shrink-0 text-xs text-gray-400 font-semibold bg-gray-50 px-2 py-0.5 rounded-lg">Urutan: {{ $banner->sort_order }}</span>
                            </div>
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

                        {{-- Tombol Aksi --}}
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
                                @click="openEdit({{ json_encode(['id' => $banner->id, 'title' => $banner->title, 'subtitle' => $banner->subtitle ?? '', 'link' => $banner->link ?? '', 'position' => $banner->position, 'sort_order' => $banner->sort_order, 'is_active' => $banner->is_active, 'image_url' => $banner->image_url]) }})"
                                class="text-xs font-bold px-3.5 py-1.5 rounded-lg border border-blue-200 text-blue-600 hover:bg-blue-50 transition">
                                <i class="fa-solid fa-pen-to-square mr-1"></i>Edit
                            </button>

                            <form action="{{ route('admin.banners.destroy', $banner) }}" method="POST"
                                onsubmit="return confirm('Yakin ingin menghapus banner ini?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="text-xs font-bold px-3.5 py-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 transition">
                                    <i class="fa-solid fa-trash-can mr-1"></i>Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif


    {{-- ════════════════════════════════ --}}
    {{-- MODAL: Tambah Banner Baru        --}}
    {{-- ════════════════════════════════ --}}
    <div x-show="showAddModal"
        class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4"
        x-transition style="display:none;">
        <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full flex flex-col" style="max-height:90vh;" @click.away="showAddModal = false">
            {{-- Header Modal --}}
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between shrink-0 bg-white rounded-t-3xl">
                <div>
                    <h3 class="font-extrabold text-gray-900 text-base">Tambah Banner Baru</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Slider utama (Hero) yang akan tampil di halaman depan toko.</p>
                </div>
                <button type="button" @click="showAddModal = false" class="text-gray-400 hover:text-gray-600 p-1">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            {{-- Body Modal (scrollable) --}}
            <form id="formAdd" action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 overflow-hidden">
                @csrf
                <input type="hidden" name="position" value="hero">

                <div class="p-6 space-y-4 overflow-y-auto flex-1">
                    {{-- Upload Media --}}
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
                                    <p class="text-[10px] text-gray-300 mt-1">Format: JPG, PNG, WEBP, MP4, MOV, WEBM — maks. {{ config('banner.maks_gambar_mb') }}MB untuk gambar, {{ config('banner.maks_video_mb') }}MB untuk video</p>
                                    <p class="text-[10px] text-gray-300">Ukuran ideal: 1920 × 1080 px (rasio 16:9)</p>
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
                                            <img :src="previewAdd"
                                                style="width:100%; height:100%; object-fit:cover; display:block;">
                                        </template>
                                    </div>
                                    <p class="text-[10px] text-gray-400 mt-2 font-semibold">
                                        <i class="fa-solid fa-crop-simple mr-1"></i>Tampilan sesuai frame 1920 × 1080 px — klik untuk ganti file
                                    </p>
                                </div>
                            </template>

                            <input id="addImageInput" type="file" name="image" accept="image/*,video/*" class="hidden" required
                                @change="previewAdd = URL.createObjectURL($event.target.files[0]); isVideoAdd = $event.target.files[0].type.startsWith('video/')">
                        </div>
                    </div>

                    {{-- Judul --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Judul Banner <span class="text-red-500">*</span></label>
                        <input type="text" name="title" required
                            class="w-full border border-gray-200 rounded-xl py-2.5 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500"
                            placeholder="Contoh: NEW COLLECTION 2026">
                    </div>

                    {{-- Subjudul --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Teks / Keterangan Banner</label>
                        <textarea name="subtitle" rows="2"
                            class="w-full border border-gray-200 rounded-xl py-2.5 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500"
                            placeholder="Keterangan singkat tentang banner ini..."></textarea>
                    </div>

                    {{-- Link Tujuan (Navigasi Frontend Dinamis) --}}
                    <div x-data="{ modeAdd: 'select' }">
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-gray-600">Tautan Navigasi (Saat Banner Diklik)</label>
                            <button type="button" @click="modeAdd = (modeAdd === 'select' ? 'custom' : 'select')"
                                    class="text-[10px] font-bold text-orange-600 hover:underline">
                                <span x-text="modeAdd === 'select' ? '+ Tulis URL Kustom' : 'Pilih dari Menu Halaman'"></span>
                            </button>
                        </div>

                        <div x-show="modeAdd === 'select'">
                            <select name="link"
                                class="w-full border border-gray-200 rounded-xl py-2.5 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 bg-white">
                                <option value="">— Tidak Mengarah ke Mana Pun (Hanya Banner) —</option>
                                <optgroup label="🏠 Halaman Utama & Info">
                                    <option value="/">🏠 Beranda Depan Toko (/)</option>
                                    <option value="/products" selected>📦 Semua Produk (Katalog)</option>
                                    <option value="/affiliate">🤝 Program Referral & Saldo Komisi</option>
                                    <option value="/about">ℹ️ Tentang Kami</option>
                                    <option value="/kontak">📞 Hubungi Kami</option>
                                    <option value="/faq">❓ FAQ & Bantuan</option>
                                    <option value="/pengiriman-retur">🔄 Info Pengiriman & Retur</option>
                                </optgroup>
                                @if(isset($categories) && $categories->isNotEmpty())
                                    <optgroup label="🏷️ Kategori Produk Toko">
                                        @foreach($categories as $cat)
                                            <option value="/category/{{ $cat->slug }}">🏷️ Kategori: {{ $cat->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endif
                                @if(isset($products) && $products->isNotEmpty())
                                    <optgroup label="👟 Produk Spesifik">
                                        @foreach($products as $prod)
                                            <option value="/products/{{ $prod->slug }}">👟 Produk: {{ $prod->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            </select>
                        </div>

                        <div x-show="modeAdd === 'custom'" x-cloak>
                            <input type="text" name="link_custom" placeholder="Contoh: /products?search=sneaker atau https://..."
                                   class="w-full border border-orange-300 bg-orange-50/30 rounded-xl py-2.5 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500"
                                   @input="$el.form.querySelector('select[name=link]').value = $event.target.value">
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1">Pengunjung toko akan langsung diarahkan ke halaman tersebut saat mengeklik banner.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 items-center">
                        {{-- Urutan --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Urutan Tampil (0, 1, 2...)</label>
                            <input type="number" name="sort_order" value="0" min="0"
                                class="w-full border border-gray-200 rounded-xl py-2.5 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500">
                        </div>

                        {{-- Status Aktif --}}
                        <div class="pt-4">
                            <label class="flex items-center gap-2 text-xs font-bold text-gray-600 cursor-pointer">
                                <input type="checkbox" name="is_active" id="addIsActive" checked
                                    class="w-4 h-4 accent-orange-600 rounded">
                                <span>Aktifkan banner ini</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Footer (sticky) --}}
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
            {{-- Header Modal --}}
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between shrink-0 bg-white rounded-t-3xl">
                <div>
                    <h3 class="font-extrabold text-gray-900 text-base">Edit Banner</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Perbarui gambar/video atau tujuan navigasi banner.</p>
                </div>
                <button type="button" @click="showEditModal = false" class="text-gray-400 hover:text-gray-600 p-1">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            {{-- Body Modal (scrollable) --}}
            <form :action="'{{ url('admin/banners') }}/' + editBanner.id" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 overflow-hidden">
                @csrf
                @method('PUT')
                <input type="hidden" name="position" value="hero">

                <div class="p-6 space-y-4 overflow-y-auto flex-1">
                    {{-- Upload Media --}}
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
                                            <img :src="previewEdit"
                                                style="width:100%; height:100%; object-fit:cover; display:block;">
                                        </template>
                                    </div>
                                    <p class="text-[10px] text-gray-400 mt-2 font-semibold">
                                        <i class="fa-solid fa-crop-simple mr-1"></i>Tampilan sesuai frame 1920 × 1080 px — klik untuk ganti file
                                    </p>
                                </div>
                            </template>

                            <input id="editImageInput" type="file" name="image" accept="image/*,video/*" class="hidden"
                                @change="previewEdit = URL.createObjectURL($event.target.files[0]); isVideoEdit = $event.target.files[0].type.startsWith('video/')">
                        </div>
                    </div>

                    {{-- Judul --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Judul Banner <span class="text-red-500">*</span></label>
                        <input type="text" name="title" required :value="editBanner.title"
                            class="w-full border border-gray-200 rounded-xl py-2.5 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500">
                    </div>

                    {{-- Subjudul --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Teks / Keterangan Banner</label>
                        <textarea name="subtitle" rows="2"
                            class="w-full border border-gray-200 rounded-xl py-2.5 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500"
                            x-text="editBanner.subtitle"></textarea>
                    </div>

                    {{-- Link Tujuan (Navigasi Frontend Dinamis) --}}
                    <div x-data="{ modeEdit: 'select' }">
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-gray-600">Tautan Navigasi (Saat Banner Diklik)</label>
                            <button type="button" @click="modeEdit = (modeEdit === 'select' ? 'custom' : 'select')"
                                    class="text-[10px] font-bold text-orange-600 hover:underline">
                                <span x-text="modeEdit === 'select' ? '+ Tulis URL Kustom' : 'Pilih dari Menu Halaman'"></span>
                            </button>
                        </div>

                        <div x-show="modeEdit === 'select'">
                            <select name="link" x-model="editBanner.link"
                                class="w-full border border-gray-200 rounded-xl py-2.5 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 bg-white">
                                <option value="">— Tidak Mengarah ke Mana Pun (Hanya Banner) —</option>
                                <optgroup label="🏠 Halaman Utama & Info">
                                    <option value="/">🏠 Beranda Depan Toko (/)</option>
                                    <option value="/products">📦 Semua Produk (Katalog)</option>
                                    <option value="/affiliate">🤝 Program Referral & Saldo Komisi</option>
                                    <option value="/about">ℹ️ Tentang Kami</option>
                                    <option value="/kontak">📞 Hubungi Kami</option>
                                    <option value="/faq">❓ FAQ & Bantuan</option>
                                    <option value="/pengiriman-retur">🔄 Info Pengiriman & Retur</option>
                                </optgroup>
                                @if(isset($categories) && $categories->isNotEmpty())
                                    <optgroup label="🏷️ Kategori Produk Toko">
                                        @foreach($categories as $cat)
                                            <option value="/category/{{ $cat->slug }}">🏷️ Kategori: {{ $cat->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endif
                                @if(isset($products) && $products->isNotEmpty())
                                    <optgroup label="👟 Produk Spesifik">
                                        @foreach($products as $prod)
                                            <option value="/products/{{ $prod->slug }}">👟 Produk: {{ $prod->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            </select>
                        </div>

                        <div x-show="modeEdit === 'custom'" x-cloak>
                            <input type="text" name="link" x-model="editBanner.link" placeholder="Contoh: /products?search=sneaker atau https://..."
                                   class="w-full border border-orange-300 bg-orange-50/30 rounded-xl py-2.5 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500">
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1">Pengunjung toko akan langsung diarahkan ke halaman tersebut saat mengeklik banner.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 items-center">
                        {{-- Urutan --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Urutan Tampil (0, 1, 2...)</label>
                            <input type="number" name="sort_order" :value="editBanner.sort_order" min="0"
                                class="w-full border border-gray-200 rounded-xl py-2.5 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500">
                        </div>

                        {{-- Status Aktif --}}
                        <div class="pt-4">
                            <label class="flex items-center gap-2 text-xs font-bold text-gray-600 cursor-pointer">
                                <input type="checkbox" name="is_active" id="editIsActive"
                                    :checked="editBanner.is_active"
                                    class="w-4 h-4 accent-orange-600 rounded">
                                <span>Aktifkan banner ini</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Footer (sticky) --}}
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
    {{-- MODAL: Preview Live Banner --}}
    {{-- ════════════════════════════════ --}}
    <div x-show="showPreviewModal"
        class="fixed inset-0 z-50 bg-black/75 flex items-center justify-center p-4"
        x-transition style="display:none;">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-4xl flex flex-col" style="max-height:90vh;" @click.away="showPreviewModal = false">
            
            {{-- Header --}}
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center shrink-0">
                <div class="flex items-center gap-2">
                    <span class="w-3.5 h-3.5 bg-green-500 rounded-full animate-ping"></span>
                    <h2 class="font-black text-gray-800 text-sm uppercase tracking-wide">Live Preview Tampilan Toko</h2>
                </div>
                <button @click="showPreviewModal = false" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            {{-- Body (Live Preview Simulator) --}}
            <div class="px-6 py-6 overflow-y-auto space-y-6 bg-slate-50 rounded-b-3xl">
                
                {{-- Dummy Browser Header --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="bg-gray-100 px-4 py-2 flex items-center gap-2 border-b border-gray-200">
                        <div class="flex gap-1.5">
                            <span class="w-3 h-3 bg-red-400 rounded-full"></span>
                            <span class="w-3 h-3 bg-yellow-400 rounded-full"></span>
                            <span class="w-3 h-3 bg-green-400 rounded-full"></span>
                        </div>
                        <div class="bg-white text-[10px] text-gray-400 px-3 py-0.5 rounded-md flex-1 text-center max-w-md mx-auto truncate select-none">
                            ecommerce-record.test
                        </div>
                    </div>

                    {{-- Dummy Nav Bar --}}
                    <div class="bg-white border-b border-gray-100 px-6 py-3 flex items-center justify-between">
                        <span class="font-black text-orange-600 tracking-wider text-sm">RECORD</span>
                        <div class="flex items-center gap-4 text-[10px] text-gray-500 font-bold uppercase">
                            <span>Beranda</span>
                            <span>About</span>
                            <span>Catalog</span>
                            <span>Kontak</span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-400 text-xs">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <i class="fa-solid fa-cart-shopping"></i>
                        </div>
                    </div>

                    {{-- Live Hero Banner Slider Simulator --}}
                    @php
                        // Sama seperti frontend: hanya hero yang aktif, maksimal 3 slide
                        $activeHeroBanners = $banners->where('position', 'hero')->where('is_active', true)->take(3);
                    @endphp
                    @if($activeHeroBanners->isEmpty())
                        <div class="bg-slate-900 flex items-center justify-center py-20 text-center text-white">
                            <div>
                                <i class="fa-regular fa-image text-4xl text-gray-600 mb-3 block"></i>
                                <h3 class="font-bold text-sm">Belum Ada Banner Hero Aktif</h3>
                                <p class="text-xs text-gray-400 mt-1">Aktifkan atau tambahkan banner hero terlebih dahulu untuk melihat preview.</p>
                            </div>
                        </div>
                    @else
                        <div class="relative w-full"
                             x-data="{ activeIndex: 0, total: {{ $activeHeroBanners->count() }} }"
                             x-init="if(total > 1) { setInterval(() => activeIndex = (activeIndex + 1) % total, 5000) }">

                            {{-- Container: overflow:hidden, rasio 16:9 (1920 × 1080 px) sama dengan frontend --}}
                            <div class="w-full overflow-hidden bg-gray-950" style="aspect-ratio: 16/9;">

                                {{-- Track: width 100%, flex, geser dengan translateX --}}
                                <div class="flex w-full h-full"
                                     style="transition: transform 1200ms cubic-bezier(0.16, 1, 0.3, 1);"
                                     :style="'transform: translateX(-' + (activeIndex * 100) + '%)'">

                                    @foreach($activeHeroBanners->values() as $idx => $b)
                                        {{-- Setiap slide: 100% lebar container, tidak menyusut --}}
                                        <div style="flex: 0 0 100%; width: 100%; height: 100%;">
                                            @if(Str::endsWith($b->image, ['.mp4', '.webm', '.ogg', '.mov']))
                                                <video src="{{ $b->image_url }}" autoplay loop muted playsinline
                                                       style="width:100%; height:100%; object-fit:cover; display:block;"></video>
                                            @else
                                                <img src="{{ $b->image_url }}" alt="{{ $b->title }}"
                                                     style="width:100%; height:100%; object-fit:cover; display:block;">
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Navigation dots --}}
                            @if($activeHeroBanners->count() > 1)
                                <div class="absolute bottom-3 left-0 right-0 flex justify-center space-x-1.5 z-10">
                                    @foreach($activeHeroBanners->values() as $idx => $b)
                                        <button @click="activeIndex = {{ $idx }}"
                                                :class="activeIndex === {{ $idx }} ? 'bg-white w-4' : 'bg-white/40 w-1.5'"
                                                class="h-1.5 rounded-full transition-all duration-300"></button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                    
                    {{-- Dummy Bottom Bar --}}
                    <div class="p-6 bg-white text-center">
                        <div class="h-4 w-32 bg-gray-100 mx-auto rounded-full mb-3"></div>
                        <div class="grid grid-cols-4 gap-4 max-w-sm mx-auto">
                            <div class="h-12 bg-gray-50 border border-gray-100 rounded-lg"></div>
                            <div class="h-12 bg-gray-50 border border-gray-100 rounded-lg"></div>
                            <div class="h-12 bg-gray-50 border border-gray-100 rounded-lg"></div>
                            <div class="h-12 bg-gray-50 border border-gray-100 rounded-lg"></div>
                        </div>
                    </div>
                </div>
                
                {{-- Info Note --}}
                <p class="text-[10px] text-gray-400 text-center font-bold">
                    <i class="fa-solid fa-circle-info mr-1"></i> Preview ini disimulasikan sesuai dengan tampilan yang akan dilihat oleh customer pada desktop dan smartphone.
                </p>
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
