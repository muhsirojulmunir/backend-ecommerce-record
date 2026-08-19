@extends('layouts.app')

@section('title', isset($product) ? 'Ubah Produk' : 'Tambah Produk')
@section('page_title', isset($product) ? 'Ubah Produk' : 'Tambah Produk Baru')
@section('page_subtitle', 'Masukkan detail informasi produk Anda di bawah ini.')

@section('content')
<form action="{{ isset($product) ? route('admin.products.update', $product->id) : route('admin.products.store') }}" 
      method="POST" 
      enctype="multipart/form-data" 
      class="max-w-5xl mx-auto space-y-8 pb-16">
    @csrf
    @if(isset($product))
        @method('PUT')
    @endif

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-sm flex items-start gap-3">
            <i class="fa-solid fa-triangle-exclamation text-red-500 text-lg shrink-0 mt-0.5"></i>
            <div>
                <span class="font-bold">Ada kesalahan pengisian data:</span>
                <ul class="list-disc list-inside mt-2 text-xs space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- 1. Informasi Produk -->
    <div class="bg-white rounded-2xl p-6 md:p-8 shadow-sm border border-gray-100 space-y-6">
        <div class="border-b border-gray-100 pb-4">
            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <i class="fa-solid fa-circle-info text-orange-500"></i>
                Informasi Produk
            </h3>
        </div>

        <!-- Foto Produk 1:1 (Max 5) -->
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Foto Katalog Produk (Maks. 5 Foto, Rasio 1:1)</label>
            <p class="text-xs text-gray-400 mb-3">
                Klik slot foto untuk memilih — bisa pilih beberapa foto sekaligus.
                Foto pertama menjadi Cover. Foto khusus tiap warna diatur di bagian <strong>Variasi Produk</strong> di bawah.
            </p>

            {{-- Pemberitahuan singkat setelah memilih banyak foto sekaligus --}}
            <p id="info-massal" class="hidden text-[11px] font-semibold mb-3"></p>

            {{-- Peringatan bila total ukuran berkas terlalu besar --}}
            <div id="peringatan-ukuran"
                 class="hidden mb-4 p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-[11px] font-semibold">
                <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                <span id="peringatan-ukuran-teks"></span>
            </div>

            @php
                // Hanya foto galeri umum yang mengisi lima slot ini. Foto khusus
                // warna punya bagian sendiri di bawah, jadi jangan ikut terhitung.
                $galeriUmum = isset($product)
                    ? $product->images->whereNull('color')->values()
                    : collect();
            @endphp

            <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                @for ($i = 0; $i < 5; $i++)
                    @php
                        $existingImage = $galeriUmum[$i] ?? null;
                        $imgSrc        = $existingImage ? asset('storage/' . $existingImage->image_path) : '';
                        $hasExisting   = !empty($imgSrc);
                    @endphp
                    {{-- Slot bisa digeser untuk mengubah urutan foto --}}
                    <div class="slot-foto relative aspect-square rounded-xl border-2 {{ $hasExisting ? 'border-orange-300' : 'border-dashed border-gray-200' }} hover:border-orange-400 transition bg-slate-50 overflow-hidden flex flex-col items-center justify-center cursor-pointer group"
                         id="slot-{{ $i }}"
                         data-index="{{ $i }}"
                         draggable="{{ $hasExisting ? 'true' : 'false' }}"
                         ondragstart="mulaiGeser(event, {{ $i }})"
                         ondragover="lewatiSlot(event, {{ $i }})"
                         ondragleave="keluarSlot(event, {{ $i }})"
                         ondrop="jatuhkanDiSlot(event, {{ $i }})"
                         ondragend="selesaiGeser()"
                         onclick="document.getElementById('file-input-{{ $i }}').click()">

                        <!-- Existing / Preview Image -->
                        <img id="preview-img-{{ $i }}"
                             src="{{ $imgSrc }}"
                             class="absolute inset-0 w-full h-full object-cover {{ $hasExisting ? '' : 'hidden' }}"
                             alt="Slot {{ $i + 1 }}"
                             draggable="false">

                        <!-- Empty Placeholder -->
                        <div id="preview-placeholder-{{ $i }}" class="text-center p-3 {{ $hasExisting ? 'hidden' : '' }}">
                            <i class="fa-regular fa-image text-2xl text-gray-400 group-hover:text-orange-500 transition mb-1"></i>
                            <p class="text-[10px] font-semibold text-gray-400 group-hover:text-gray-600 transition">
                                {{ $i === 0 ? 'Cover Utama' : 'Foto ' . ($i + 1) }}
                            </p>
                        </div>

                        <!-- Cover Badge: hanya pada slot pertama -->
                        <span id="badge-cover-{{ $i }}"
                              class="absolute top-1 left-1 bg-orange-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-md shadow {{ $i === 0 ? '' : 'hidden' }}">COVER</span>

                        <!-- Pegangan geser, muncul saat kursor di atas slot berisi -->
                        <span id="pegangan-{{ $i }}"
                              class="absolute bottom-1 left-1 bg-black/55 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-md opacity-0 group-hover:opacity-100 transition pointer-events-none {{ $hasExisting ? '' : 'hidden' }}">
                            <i class="fa-solid fa-up-down-left-right"></i> Geser
                        </span>

                        <!-- Change overlay on hover (visible only when image exists) -->
                        <div class="{{ $hasExisting ? '' : 'hidden' }} absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center" id="overlay-{{ $i }}">
                            <span class="text-white text-xs font-bold flex items-center gap-1"><i class="fa-solid fa-camera"></i> Ganti Foto</span>
                        </div>

                        <!-- Delete button (existing only) -->
                        @if($hasExisting)
                        <button type="button"
                                onclick="event.stopPropagation(); removeSlotImage({{ $i }})"
                                class="absolute top-1 right-1 w-6 h-6 bg-red-500 text-white rounded-full text-[10px] shadow hover:bg-red-600 transition flex items-center justify-center"
                                id="delete-btn-{{ $i }}"
                                title="Hapus foto ini">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                        @else
                        <button type="button"
                                onclick="event.stopPropagation(); removeSlotImage({{ $i }})"
                                class="absolute top-1 right-1 w-6 h-6 bg-red-500 text-white rounded-full text-[10px] shadow hover:bg-red-600 transition items-center justify-center hidden"
                                id="delete-btn-{{ $i }}"
                                title="Hapus foto ini">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                        @endif

                        <!-- Per-slot file input.
                             multiple: sekali klik boleh memilih beberapa foto —
                             yang pertama masuk ke slot ini, sisanya mengisi slot
                             kosong berikutnya. -->
                        <input type="file"
                               name="new_images[{{ $i }}]"
                               id="file-input-{{ $i }}"
                               class="hidden"
                               multiple
                               accept="image/jpeg,image/png,image/jpg,image/webp"
                               onchange="onSlotFileSelected(this, {{ $i }})">

                        <!-- Hidden: existing path (keep this image if no new file chosen) -->
                        <input type="hidden"
                               name="existing_images[{{ $i }}]"
                               id="existing-path-{{ $i }}"
                               value="{{ $existingImage?->image_path ?? '' }}">
                    </div>
                @endfor
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Nama Produk -->
            <div>
                <label for="name" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Nama Produk</label>
                <input type="text" name="name" id="name" value="{{ old('name', $product->name ?? '') }}" required
                       class="block w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm transition"
                       placeholder="Contoh: Sepatu Sneakers Running Casual">
            </div>

            <!-- Bahan -->
            <div>
                <label for="material" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Bahan</label>
                <input type="text" name="material" id="material" value="{{ old('material', $product->details['material'] ?? '') }}"
                       class="block w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm transition"
                       placeholder="Contoh: Kanvas, Kulit Sintetis, Karet">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Kategori -->
            <div>
                <label for="category_id" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Kategori</label>
                <select name="category_id" id="category_id" required
                        class="block w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm bg-white transition">
                    <option value="">Pilih Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ (old('category_id', $product->category_id ?? '') == $category->id) ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Stok Total -->
            <div>
                <label for="stock" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Total Stok</label>
                <input type="number" name="stock" id="stock" value="{{ old('stock', $product->stock ?? 0) }}" readonly
                       class="block w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 text-gray-500 text-sm font-semibold focus:outline-none cursor-not-allowed"
                       placeholder="0">
            </div>
        </div>

        <!-- Hidden Base Price -->
        <input type="hidden" name="price" id="price" value="{{ old('price', $product->price ?? '0') }}">

        <div>
            <!-- Status -->
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Status Produk</label>
            <div class="flex items-center space-x-6">
                <label class="flex items-center text-sm font-medium text-gray-700 cursor-pointer">
                    <input type="radio" name="status" value="active" {{ old('status', $product->status ?? 'active') === 'active' ? 'checked' : '' }}
                           class="w-4 h-4 text-orange-600 focus:ring-orange-500 border-gray-300 mr-2">
                    Aktif (Ditampilkan di Toko)
                </label>
                <label class="flex items-center text-sm font-medium text-gray-700 cursor-pointer">
                    <input type="radio" name="status" value="inactive" {{ old('status', $product->status ?? 'active') === 'inactive' ? 'checked' : '' }}
                           class="w-4 h-4 text-orange-600 focus:ring-orange-500 border-gray-300 mr-2">
                    Nonaktifkan
                </label>
            </div>

            {{-- Our Collection. --}}
            <div class="mt-6 pt-6 border-t border-gray-100">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                    Etalase Halaman Utama
                </label>

                <input type="hidden" name="is_featured" value="0">
                <label class="flex items-start gap-3 p-4 rounded-xl border border-gray-200 hover:border-amber-300 hover:bg-amber-50/40 cursor-pointer transition">
                    <input type="checkbox" name="is_featured" value="1"
                           {{ old('is_featured', $product->is_featured ?? false) ? 'checked' : '' }}
                           class="w-4 h-4 mt-0.5 text-amber-500 focus:ring-amber-400 border-gray-300 rounded">
                    <span>
                        <span class="block text-sm font-bold text-gray-800">
                            <i class="fa-solid fa-star text-amber-500 mr-1"></i>
                            Tampilkan di "Our Collection"
                        </span>
                        <span class="block text-xs text-gray-500 mt-1 leading-relaxed">
                            Produk akan muncul di etalase halaman utama toko.
                            Bisa juga diatur cepat lewat tombol bintang di daftar produk.
                        </span>
                    </span>
                </label>
            </div>
        </div>
    </div>

    <!-- 2. Deskripsi -->
    <div class="bg-white rounded-2xl p-6 md:p-8 shadow-sm border border-gray-100 space-y-6">
        <div class="border-b border-gray-100 pb-4">
            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <i class="fa-solid fa-align-left text-orange-500"></i>
                Deskripsi Produk
            </h3>
        </div>

        <div>
            <label for="description" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Penjelasan Produk</label>
            <textarea name="description" id="description" rows="6" required
                      class="block w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm transition"
                      placeholder="Tuliskan spesifikasi lengkap, kelebihan, dan detail produk lainnya... (Min. 20 karakter)">{{ old('description', $product->description ?? '') }}</textarea>
        </div>
    </div>

    <!-- 3. Informasi Penjualan (Variasi & Harga) -->
    <div class="bg-white rounded-2xl p-6 md:p-8 shadow-sm border border-gray-100 space-y-6">
        <div class="border-b border-gray-100 pb-4">
            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <i class="fa-solid fa-tags text-orange-500"></i>
                Informasi Penjualan (Varian Produk)
            </h3>
        </div>

        <!-- PILIHAN VARIAN (Seperti Gambar 2) -->
        <div class="space-y-6">
            <!-- Variasi 1 Box -->
            <div class="bg-slate-50 p-6 rounded-2xl border border-slate-200 relative">
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-200">
                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-nodes text-orange-500"></i> Variasi 1 (Warna)
                    </span>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Nama Varian (misal: Warna)</label>
                        <input type="text" id="v1-name" value="Warna" class="block w-full max-w-md px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 text-xs transition" placeholder="Warna">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Opsi Warna Produk</label>
                        <div id="v1-options-container" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <!-- JS will populate option inputs here -->
                        </div>
                        <button type="button" onclick="addV1Option('')" class="mt-3 text-[11px] font-bold text-orange-600 hover:text-orange-700 flex items-center gap-1.5 transition">
                            <i class="fa-solid fa-plus-circle"></i> Tambah Opsi Warna
                        </button>
                    </div>
                </div>
            </div>

            <!-- Variasi 2 Box -->
            <div class="bg-slate-50 p-6 rounded-2xl border border-slate-200 relative">
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-200">
                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-nodes text-orange-500"></i> Variasi 2 (Ukuran)
                    </span>
                    <button type="button" onclick="clearVariasi2()" class="text-xs font-bold text-red-500 hover:text-red-700 transition flex items-center gap-1">
                        <i class="fa-solid fa-trash-can"></i> Hapus Variasi 2
                    </button>
                </div>
                <div class="space-y-4">
                    <div class="flex flex-wrap items-center gap-6">
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Nama Varian (misal: Ukuran)</label>
                            <input type="text" id="v2-name" value="Ukuran" class="block w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 text-xs transition" placeholder="Ukuran">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Pilihan Cepat Ukuran</label>
                            <div class="flex items-center space-x-4 text-xs font-semibold py-2">
                                <label class="flex items-center cursor-pointer select-none">
                                    <input type="radio" name="v2-preset" value="US" onclick="applyPreset('US')" class="w-4 h-4 text-orange-600 focus:ring-orange-500 border-gray-300 mr-1.5"> US
                                </label>
                                <label class="flex items-center cursor-pointer select-none">
                                    <input type="radio" name="v2-preset" value="EU" onclick="applyPreset('EU')" class="w-4 h-4 text-orange-600 focus:ring-orange-500 border-gray-300 mr-1.5" checked> EU (Lokal Sepatu)
                                </label>
                                <label class="flex items-center cursor-pointer select-none">
                                    <input type="radio" name="v2-preset" value="UK" onclick="applyPreset('UK')" class="w-4 h-4 text-orange-600 focus:ring-orange-500 border-gray-300 mr-1.5"> UK
                                </label>
                                <label class="flex items-center cursor-pointer select-none">
                                    <input type="radio" name="v2-preset" value="Custom" onclick="applyPreset('Custom')" class="w-4 h-4 text-orange-600 focus:ring-orange-500 border-gray-300 mr-1.5"> Atur Sendiri
                                </label>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Opsi Ukuran Produk</label>
                        <div id="v2-options-container" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <!-- JS will populate option inputs here -->
                        </div>
                        <button type="button" onclick="addV2Option('')" class="mt-3 text-[11px] font-bold text-orange-600 hover:text-orange-700 flex items-center gap-1.5 transition">
                            <i class="fa-solid fa-plus-circle"></i> Tambah Opsi Ukuran
                        </button>
                    </div>
                </div>
            </div>
        </div>


        <!-- UBAH MASSAL (BULK EDIT) PANEL -->
        <div class="bg-orange-50/60 p-5 rounded-2xl border border-orange-100/80 space-y-3">
            <span class="block text-xs font-extrabold text-orange-800 uppercase tracking-wider flex items-center gap-1.5">
                <i class="fa-solid fa-pen-to-square"></i> Ubah Massal Kolom Varian
            </span>
            <div class="flex flex-wrap gap-4 items-end text-xs">
                <div>
                    <label class="block text-[10px] font-bold text-gray-600 uppercase mb-1">Harga Akhir (Rp)</label>
                    <input type="text" id="bulk-price" oninput="this.value = formatRupiah(this.value)" class="px-3 py-2 border border-gray-200 rounded-xl text-xs w-32 focus:ring-2 focus:ring-orange-500 focus:outline-none transition" placeholder="Contoh: 249.000">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-600 uppercase mb-1">Stok</label>
                    <input type="number" id="bulk-stock" class="px-3 py-2 border border-gray-200 rounded-xl text-xs w-20 focus:ring-2 focus:ring-orange-500 focus:outline-none transition" placeholder="10">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-600 uppercase mb-1">Prefix Kode SKU</label>
                    <input type="text" id="bulk-sku" class="px-3 py-2 border border-gray-200 rounded-xl text-xs w-44 focus:ring-2 focus:ring-orange-500 focus:outline-none transition" placeholder="RECORD-CONF">
                </div>
                <button type="button" onclick="applyBulkChanges()" class="bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold py-2 px-4 rounded-lg transition-all duration-150 shadow-sm flex items-center gap-1">
                    <i class="fa-solid fa-check"></i> Terapkan ke Semua
                </button>
            </div>
        </div>

        <!-- Variants Table -->
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Daftar Kombinasi Varian</label>
            <div class="overflow-x-auto border border-gray-100 rounded-xl">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3">Warna</th>
                            <th class="px-4 py-3">Ukuran</th>
                            <th class="px-4 py-3">Harga Akhir (Rp)</th>
                            <th class="px-4 py-3">Stok</th>
                            <th class="px-4 py-3">Kode Variasi (SKU)</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="variants-table-body" class="divide-y divide-gray-100 bg-white">
                        <!-- Will be dynamically populated via Javascript -->
                    </tbody>
                </table>
            </div>
            
            <button type="button" onclick="addEmptyVariantRow()" class="mt-3 text-xs font-bold text-orange-600 hover:text-orange-700 flex items-center gap-1.5 transition">
                <i class="fa-solid fa-plus-circle"></i> Tambah Baris Varian Baru Manual
            </button>
        </div>

        <!-- Foto per warna -->
        <div class="border-t border-gray-100 pt-6">
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Foto Tiap Warna</label>
            <p class="text-[11px] text-gray-400 mb-4 leading-relaxed">
                Unggah satu foto untuk tiap warna. Saat pembeli memilih warna di halaman produk,
                foto yang tampil ikut berganti sesuai pilihannya. Warna yang tidak diberi foto
                akan memakai foto utama produk.
            </p>

            <div id="color-photos" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                <!-- Diisi otomatis lewat JavaScript sesuai warna yang ada -->
            </div>

            <p id="color-photos-kosong" class="text-xs text-gray-400 italic hidden">
                Tambahkan warna pada bagian variasi di atas, lalu kotak unggah foto akan muncul di sini.
            </p>
        </div>
    </div>

    <!-- 4. Pengiriman -->
    <div class="bg-white rounded-2xl p-6 md:p-8 shadow-sm border border-gray-100 space-y-6">
        <div class="border-b border-gray-100 pb-4">
            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <i class="fa-solid fa-truck text-orange-500"></i>
                Informasi Pengiriman
            </h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Weight -->
            <div>
                <label for="weight_gram" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Berat Paket (gr)</label>
                <input type="number" name="weight_gram" id="weight_gram" value="{{ old('weight_gram', $product->shipping->weight_gram ?? 500) }}" required
                       class="block w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm transition"
                       placeholder="500">
            </div>

            <!-- Length -->
            <div>
                <label for="package_length" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Panjang (cm)</label>
                <input type="number" name="package_length" id="package_length" value="{{ old('package_length', $product->shipping->package_length ?? 0) }}" required
                       class="block w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm transition"
                       placeholder="0">
            </div>

            <!-- Width -->
            <div>
                <label for="package_width" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Lebar (cm)</label>
                <input type="number" name="package_width" id="package_width" value="{{ old('package_width', $product->shipping->package_width ?? 0) }}" required
                       class="block w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm transition"
                       placeholder="0">
            </div>

            <!-- Height -->
            <div>
                <label for="package_height" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Tinggi (cm)</label>
                <input type="number" name="package_height" id="package_height" value="{{ old('package_height', $product->shipping->package_height ?? 0) }}" required
                       class="block w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm transition"
                       placeholder="0">
            </div>
        </div>

        <div>
            <!-- Courier Providers -->
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Pilihan Jasa Kirim (Ekspedisi)</label>
            <div class="flex flex-wrap gap-4">
                @php
                    $savedCouriers = old('courier_providers', $product->shipping->courier_providers ?? ['jne', 'tiki', 'pos']);
                @endphp
                <label class="inline-flex items-center bg-slate-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-semibold text-gray-700 cursor-pointer hover:bg-slate-100 transition select-none">
                    <input type="checkbox" name="courier_providers[]" value="jne" {{ in_array('jne', $savedCouriers) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-orange-600 focus:ring-orange-500 mr-2.5">
                    JNE Express
                </label>
                <label class="inline-flex items-center bg-slate-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-semibold text-gray-700 cursor-pointer hover:bg-slate-100 transition select-none">
                    <input type="checkbox" name="courier_providers[]" value="tiki" {{ in_array('tiki', $savedCouriers) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-orange-600 focus:ring-orange-500 mr-2.5">
                    TIKI
                </label>
                <label class="inline-flex items-center bg-slate-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-semibold text-gray-700 cursor-pointer hover:bg-slate-100 transition select-none">
                    <input type="checkbox" name="courier_providers[]" value="pos" {{ in_array('pos', $savedCouriers) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-orange-600 focus:ring-orange-500 mr-2.5">
                    POS Indonesia
                </label>
            </div>
        </div>
    </div>

    <!-- Form Actions -->
    <div class="flex items-center justify-end space-x-4">
        <a href="{{ route('admin.products') }}" class="px-6 py-3 border border-gray-200 rounded-xl text-sm font-bold text-gray-500 hover:text-gray-700 hover:bg-gray-50 transition">
            Batal
        </a>
        <button type="submit" class="px-8 py-3 bg-gradient-to-r from-orange-500 to-rose-500 hover:from-orange-600 hover:to-rose-600 text-white font-bold rounded-xl shadow-lg shadow-orange-500/20 active:scale-[0.98] text-sm transition">
            Simpan Produk
        </button>
    </div>
</form>

<script>
    // Initialize or parse existing variants
    // Initialize or parse existing variants
    let variants = {!! json_encode(old('variants', isset($product) ? $product->variants : [])) !!};
    let basePriceInput = document.getElementById('price');

    // On page load, calculate absolute price for each variant based on base price
    let initialBasePrice = parseFloat(basePriceInput.value) || 0;
    variants.forEach(v => {
        let adj = parseFloat(v.price_adjustment) || 0;
        v.price = initialBasePrice + adj;
    });

    document.addEventListener("DOMContentLoaded", function() {
        initVariantBuilder();
        if (variants.length > 0) {
            sortVariants();
            recalculateBasePriceAndAdjustments();
            renderVariantsTable();
        } else {
            generateVariantsFromBuilder();
        }
    });

    // ──────────────────────────────────────────────────────────────────────────
    // SLOT-BASED IMAGE FUNCTIONS
    // ──────────────────────────────────────────────────────────────────────────

    /** Jumlah slot foto katalog yang tersedia. */
    const JUMLAH_SLOT = 5;

    // ═════════════════════════════════════════════════════════════ Pengelola slot foto katalog Isi slo...

    let daftarSlot = [];

    function slotBaru() {
        return { berkas: null, path: '', pratinjau: '' };
    }

    /** Bangun larik awal dari kondisi yang dirender server. */
    function siapkanSlot() {
        daftarSlot = [];

        for (let i = 0; i < JUMLAH_SLOT; i++) {
            const path = document.getElementById('existing-path-' + i);
            const img  = document.getElementById('preview-img-' + i);

            daftarSlot.push({
                berkas: null,
                path: path && path.value ? path.value : '',
                pratinjau: (img && !img.classList.contains('hidden') && img.getAttribute('src')) || '',
            });
        }
    }

    function slotTerisi(s) {
        return !!(s && (s.berkas || s.path));
    }

    function slotKosong(i) {
        return !slotTerisi(daftarSlot[i]);
    }

    /** Rapatkan foto ke depan agar tidak ada celah kosong di tengah. */
    function rapatkanSlot() {
        const terisi = daftarSlot.filter(slotTerisi);

        while (terisi.length < JUMLAH_SLOT) {
            terisi.push(slotBaru());
        }

        daftarSlot = terisi.slice(0, JUMLAH_SLOT);
    }

    /** Tuliskan kembali seluruh larik ke tampilan dan input formulir. */
    function gambarUlangSlot() {
        for (let i = 0; i < JUMLAH_SLOT; i++) {
            const s = daftarSlot[i] || slotBaru();
            const ada = slotTerisi(s);

            const kotak       = document.getElementById('slot-' + i);
            const img         = document.getElementById('preview-img-' + i);
            const placeholder = document.getElementById('preview-placeholder-' + i);
            const overlay     = document.getElementById('overlay-' + i);
            const tombolHapus = document.getElementById('delete-btn-' + i);
            const inputFile   = document.getElementById('file-input-' + i);
            const inputPath   = document.getElementById('existing-path-' + i);
            const badge       = document.getElementById('badge-cover-' + i);
            const pegangan    = document.getElementById('pegangan-' + i);

            if (!kotak) continue;

            // Nilai yang dikirim ke server
            inputPath.value = s.path || '';

            const dt = new DataTransfer();
            if (s.berkas) dt.items.add(s.berkas);
            inputFile.files = dt.files;

            // Tampilan. src dikosongkan lewat removeAttribute, bukan diisi
            // string kosong — src="" membuat browser memuat ulang alamat halaman.
            if (ada && s.pratinjau) {
                img.src = s.pratinjau;
            } else {
                img.removeAttribute('src');
            }
            img.classList.toggle('hidden', !ada);
            placeholder.classList.toggle('hidden', ada);
            overlay.classList.toggle('hidden', !ada);
            tombolHapus.classList.toggle('hidden', !ada);
            tombolHapus.classList.toggle('flex', ada);

            kotak.classList.toggle('border-orange-300', ada);
            kotak.classList.toggle('border-dashed', !ada);
            kotak.classList.toggle('border-gray-200', !ada);

            // Hanya slot berisi yang bisa digeser
            kotak.setAttribute('draggable', ada ? 'true' : 'false');
            pegangan.classList.toggle('hidden', !ada);

            // Lencana COVER selalu menempel di slot pertama
            badge.classList.toggle('hidden', i !== 0);
        }

        periksaTotalUkuran();
    }

    /**
 * Dipanggil saat admin memilih foto pada sebuah slot.
 */
    function onSlotFileSelected(input, index) {
        const berkas = Array.from(input.files || []);
        if (berkas.length === 0) return;

        isiSlot(index, berkas[0]);

        let tertampung = 1;
        let slot = 0;

        for (let i = 1; i < berkas.length; i++) {
            while (slot < JUMLAH_SLOT && (slot === index || !slotKosong(slot))) slot++;
            if (slot >= JUMLAH_SLOT) break;

            isiSlot(slot, berkas[i]);
            tertampung++;
            slot++;
        }

        rapatkanSlot();
        gambarUlangSlot();
        beriTahuHasil(berkas.length, tertampung);
    }

    /** Simpan satu berkas ke larik, lengkap dengan pratinjaunya. */
    function isiSlot(index, berkas) {
        if (index < 0 || index >= JUMLAH_SLOT) return;

        // Lepas pratinjau sementara sebelumnya agar tidak menumpuk di memori
        const lama = daftarSlot[index];
        if (lama && lama.pratinjau && lama.pratinjau.startsWith('blob:')) {
            URL.revokeObjectURL(lama.pratinjau);
        }

        daftarSlot[index] = {
            berkas: berkas,
            path: '',
            pratinjau: URL.createObjectURL(berkas),
        };
    }

    /**
     * Beri kabar singkat hanya bila ada foto yang tidak tertampung.
     */
    function beriTahuHasil(dipilih, tertampung) {
        const info = document.getElementById('info-massal');
        if (!info) return;

        const sisa = dipilih - tertampung;

        if (sisa > 0) {
            info.textContent = `${tertampung} foto dimasukkan, ${sisa} tidak muat karena slot penuh (maksimal ${JUMLAH_SLOT}).`;
            info.className = 'text-[11px] font-semibold mb-3 text-amber-600';
        } else if (dipilih > 1) {
            info.textContent = `${tertampung} foto dimasukkan.`;
            info.className = 'text-[11px] font-semibold mb-3 text-emerald-600';
        } else {
            info.className = 'hidden text-[11px] font-semibold mb-3';
        }
    }

    /** Hapus foto, lalu rapatkan sisanya supaya tidak ada celah. */
    function removeSlotImage(index) {
        const s = daftarSlot[index];

        if (s && s.pratinjau && s.pratinjau.startsWith('blob:')) {
            URL.revokeObjectURL(s.pratinjau);
        }

        daftarSlot[index] = slotBaru();

        rapatkanSlot();
        gambarUlangSlot();
    }

    // ───────────────────────────────────────────────────────────── Menggeser urutan foto Foto yang dil...

    let slotAsal = null;

    function mulaiGeser(e, index) {
        if (slotKosong(index)) {
            e.preventDefault();
            return;
        }

        slotAsal = index;
        e.dataTransfer.effectAllowed = 'move';
        // Sebagian browser mensyaratkan data terisi agar geseran berjalan
        e.dataTransfer.setData('text/plain', String(index));

        document.getElementById('slot-' + index).classList.add('opacity-40');
    }

    function lewatiSlot(e, index) {
        if (slotAsal === null || slotAsal === index) return;

        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        document.getElementById('slot-' + index).classList.add('ring-2', 'ring-orange-400');
    }

    function keluarSlot(e, index) {
        document.getElementById('slot-' + index).classList.remove('ring-2', 'ring-orange-400');
    }

    function jatuhkanDiSlot(e, index) {
        e.preventDefault();
        document.getElementById('slot-' + index).classList.remove('ring-2', 'ring-orange-400');

        if (slotAsal === null || slotAsal === index) return;

        const dipindah = daftarSlot.splice(slotAsal, 1)[0];
        daftarSlot.splice(index, 0, dipindah);

        rapatkanSlot();
        gambarUlangSlot();

        const info = document.getElementById('info-massal');
        if (info) {
            info.textContent = index === 0
                ? 'Foto dijadikan Cover Utama.'
                : `Foto dipindahkan ke posisi ${index + 1}.`;
            info.className = 'text-[11px] font-semibold mb-3 text-emerald-600';
        }
    }

    function selesaiGeser() {
        for (let i = 0; i < JUMLAH_SLOT; i++) {
            const kotak = document.getElementById('slot-' + i);
            if (kotak) kotak.classList.remove('opacity-40', 'ring-2', 'ring-orange-400');
        }
        slotAsal = null;
    }

    // Siapkan larik begitu halaman siap. Bila skrip dimuat setelah DOM
    // selesai dibangun, DOMContentLoaded tidak akan terpicu lagi — karena
    // itu kondisinya diperiksa dulu.
    function mulaiKelolaSlot() {
        siapkanSlot();
        rapatkanSlot();
        gambarUlangSlot();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', mulaiKelolaSlot);
    } else {
        mulaiKelolaSlot();
    }

    /**
     * Ingatkan admin bila total berkas mendekati batas server,
     * sebelum permintaan dikirim dan ditolak di tengah jalan.
     */
    function periksaTotalUkuran() {
        const batasMb = {{ (int) (ini_get('post_max_size') ? rtrim(ini_get('post_max_size'), 'MmGgKk') : 8) }};
        let total = 0;

        for (let i = 0; i < JUMLAH_SLOT; i++) {
            const f = document.getElementById('file-input-' + i);
            if (f && f.files && f.files[0]) total += f.files[0].size;
        }
        document.querySelectorAll('#color-photos input[type="file"]').forEach(f => {
            if (f.files && f.files[0]) total += f.files[0].size;
        });

        const kotak = document.getElementById('peringatan-ukuran');
        const teks  = document.getElementById('peringatan-ukuran-teks');
        if (!kotak || !teks) return;

        const totalMb = total / 1048576;
        // Sisakan ruang untuk kolom teks lain pada formulir
        const aman = batasMb * 0.85;

        if (totalMb > aman) {
            teks.textContent = `Total foto ${totalMb.toFixed(1)}MB, mendekati batas server ${batasMb}MB. `
                + 'Kurangi jumlah foto atau perkecil ukurannya agar penyimpanan tidak gagal.';
            kotak.classList.remove('hidden');
        } else {
            kotak.classList.add('hidden');
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // NEW STRUCTURED VARIANT BUILDER (IMAGE 2 COMPLIANT)
    // ──────────────────────────────────────────────────────────────────────────

    let v1Options = [];
    let v2Options = [];

    function initVariantBuilder() {
        if (variants.length > 0) {
            // Extract colors and sizes from existing product variants
            let uniqueColors = [...new Set(variants.map(v => v.color))];
            let uniqueSizes = [...new Set(variants.map(v => v.size))];
            
            uniqueColors.forEach(c => v1Options.push(c));
            uniqueSizes.forEach(s => v2Options.push(s));
        } else {
            // Defaults for new products
            v1Options = ['Hitam', 'Putih'];
            v2Options = ['38', '39', '40', '41', '42'];
        }
        
        renderV1Inputs();
        renderV2Inputs();
    }

    function renderV1Inputs() {
        let container = document.getElementById('v1-options-container');
        container.innerHTML = '';
        v1Options.forEach((val, idx) => {
            let div = document.createElement('div');
            div.className = 'flex items-center space-x-2 bg-white p-2 rounded-xl border border-gray-200';
            div.innerHTML = `
                <input type="text" value="${val}" 
                       oninput="v1Options[${idx}] = this.value; debounceGenerate()"
                       class="flex-grow px-3 py-1.5 border border-gray-200 rounded-lg text-xs focus:ring-1 focus:ring-orange-400 focus:outline-none" placeholder="Misal: Hitam">
                <button type="button" onclick="addV1OptionAtIndex(${idx})" class="text-slate-400 hover:text-orange-500 transition text-sm" title="Tambah Opsi">
                    <i class="fa-solid fa-plus"></i>
                </button>
                <button type="button" onclick="removeV1Option(${idx})" class="text-slate-400 hover:text-red-500 transition text-sm" title="Hapus Opsi">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            `;
            container.appendChild(div);
        });
    }

    function addV1Option(val = '') {
        v1Options.push(val);
        renderV1Inputs();
        generateVariantsFromBuilder();
    }

    function addV1OptionAtIndex(idx) {
        v1Options.splice(idx + 1, 0, '');
        renderV1Inputs();
        generateVariantsFromBuilder();
    }

    function removeV1Option(idx) {
        if (v1Options.length > 1) {
            v1Options.splice(idx, 1);
            renderV1Inputs();
            generateVariantsFromBuilder();
        }
    }

    function renderV2Inputs() {
        let container = document.getElementById('v2-options-container');
        container.innerHTML = '';
        v2Options.forEach((val, idx) => {
            let div = document.createElement('div');
            div.className = 'flex items-center space-x-2 bg-white p-2 rounded-xl border border-gray-200';
            div.innerHTML = `
                <input type="text" value="${val}" 
                       oninput="v2Options[${idx}] = this.value; debounceGenerate()"
                       class="flex-grow px-3 py-1.5 border border-gray-200 rounded-lg text-xs focus:ring-1 focus:ring-orange-400 focus:outline-none" placeholder="Misal: 39">
                <button type="button" onclick="addV2OptionAtIndex(${idx})" class="text-slate-400 hover:text-orange-500 transition text-sm" title="Tambah Opsi">
                    <i class="fa-solid fa-plus"></i>
                </button>
                <button type="button" onclick="removeV2Option(${idx})" class="text-slate-400 hover:text-red-500 transition text-sm" title="Hapus Opsi">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            `;
            container.appendChild(div);
        });
    }

    function addV2Option(val = '') {
        v2Options.push(val);
        renderV2Inputs();
        generateVariantsFromBuilder();
    }

    function addV2OptionAtIndex(idx) {
        v2Options.splice(idx + 1, 0, '');
        renderV2Inputs();
        generateVariantsFromBuilder();
    }

    function removeV2Option(idx) {
        if (v2Options.length > 1) {
            v2Options.splice(idx, 1);
            renderV2Inputs();
            generateVariantsFromBuilder();
        }
    }

    function clearVariasi2() {
        v2Options = [];
        renderV2Inputs();
        let customRadio = document.querySelector('input[name="v2-preset"][value="Custom"]');
        if (customRadio) customRadio.checked = true;
        generateVariantsFromBuilder();
    }

    function applyPreset(type) {
        if (type === 'US') {
            v2Options = ['7', '8', '9', '10', '11'];
        } else if (type === 'EU') {
            v2Options = ['38', '39', '40', '41', '42'];
        } else if (type === 'UK') {
            v2Options = ['6', '7', '8', '9', '10'];
        } else {
            v2Options = [''];
        }
        renderV2Inputs();
        generateVariantsFromBuilder();
    }

    // Debounce to prevent table regeneration on every single keystroke
    let _debounceTimer = null;
    function debounceGenerate() {
        clearTimeout(_debounceTimer);
        _debounceTimer = setTimeout(() => generateVariantsFromBuilder(), 400);
    }

    function generateVariantsFromBuilder() {
        let activeV1 = v1Options.map(o => o.trim()).filter(o => o !== '');
        let activeV2 = v2Options.map(o => o.trim()).filter(o => o !== '');

        // Silently skip if no color defined yet (user is still typing)
        if (activeV1.length === 0) return;

        let newVariants = [];
        let existingMap = {};
        
        // Preserve values (price, stock, hex) from existing variant table state
        variants.forEach(v => {
            let key = `${v.color.trim().toLowerCase()}-${v.size.trim().toLowerCase()}`;
            existingMap[key] = v;
        });

        activeV1.forEach(color => {
            if (activeV2.length > 0) {
                activeV2.forEach(size => {
                    let key = `${color.toLowerCase()}-${size.toLowerCase()}`;
                    let existing = existingMap[key];

                    newVariants.push({
                        color: color,
                        color_hex: existing ? existing.color_hex : getColorHexFromName(color),
                        size: size,
                        price: existing ? existing.price : (initialBasePrice || 249000),
                        stock: existing ? existing.stock : 10,
                        sku: existing ? existing.sku : generateSKU(color, size)
                    });
                });
            } else {
                let key = `${color.toLowerCase()}-all size`;
                let existing = existingMap[key];

                newVariants.push({
                    color: color,
                    color_hex: existing ? existing.color_hex : getColorHexFromName(color),
                    size: 'All Size',
                    price: existing ? existing.price : (initialBasePrice || 249000),
                    stock: existing ? existing.stock : 10,
                    sku: existing ? existing.sku : generateSKU(color, 'ALL')
                });
            }
        });

        variants = newVariants;
        sortVariants();
        recalculateBasePriceAndAdjustments();
        renderVariantsTable();
    }

    /**
     * Sort variants so they are grouped by color, and size sequentially.
     */
    function sortVariants() {
        variants.sort((a, b) => {
            let colorA = (a.color || '').toLowerCase();
            let colorB = (b.color || '').toLowerCase();
            if (colorA !== colorB) {
                return colorA.localeCompare(colorB);
            }
            // Sort by size (numeric conversion check)
            let sizeA = parseInt(a.size) || a.size;
            let sizeB = parseInt(b.size) || b.size;
            if (typeof sizeA === 'number' && typeof sizeB === 'number') {
                return sizeA - sizeB;
            }
            return String(sizeA).localeCompare(String(sizeB));
        });
    }

    function getColorHexFromName(name) {
        let n = name.toLowerCase();
        if (n.includes('hitam')) return '#000000';
        if (n.includes('putih')) return '#FFFFFF';
        if (n.includes('merah')) return '#EF4444';
        if (n.includes('biru'))  return '#3B82F6';
        if (n.includes('hijau')) return '#10B981';
        if (n.includes('kuning'))return '#F59E0B';
        if (n.includes('pink'))  return '#EC4899';
        return '#CCCCCC';
    }

    function generateSKU(color, size) {
        let cleanName  = (document.getElementById('name').value || 'PROD').toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 6);
        let cleanColor = color.toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 3);
        let rand       = Math.random().toString(36).substring(2, 6).toUpperCase();
        return `${cleanName}-${cleanColor}-${size}-${rand}`;
    }

    function addEmptyVariantRow() {
        variants.push({
            color: 'Default',
            color_hex: '#CCCCCC',
            size: 'Free Size',
            price: initialBasePrice || 249000,
            stock: 10,
            sku: 'SKU-' + Math.random().toString(36).substring(2, 8).toUpperCase()
        });
        recalculateBasePriceAndAdjustments();
        renderVariantsTable();
    }

    function removeVariantRow(index) {
        variants.splice(index, 1);
        recalculateBasePriceAndAdjustments();
        renderVariantsTable();
    }

    function recalculateBasePriceAndAdjustments() {
        if (variants.length === 0) {
            basePriceInput.value = 0;
            return;
        }
        let minPrice = Math.min(...variants.map(v => parseFloat(v.price) || 0));
        if (minPrice === Infinity || minPrice < 0) minPrice = 0;
        basePriceInput.value = minPrice;
        
        variants.forEach(v => {
            let p = parseFloat(v.price) || 0;
            v.price_adjustment = p - minPrice;
        });
    }

    // Helper to format number to Rupiah with dot thousands separator
    function formatRupiah(value) {
        if (value === undefined || value === null) return '';
        let numberString = value.toString().replace(/[^0-9]/g, '');
        let split = numberString.split(',');
        let sisa = split[0].length % 3;
        let rupiah = split[0].substr(0, sisa);
        let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        return rupiah;
    }

    // Helper to extract clean integer value from Rupiah string
    function parseRupiah(value) {
        if (!value) return 0;
        return parseFloat(value.toString().replace(/\./g, '')) || 0;
    }

    function renderVariantsTable() {
        let tbody      = document.getElementById('variants-table-body');
        tbody.innerHTML = '';
        let basePrice  = parseFloat(basePriceInput.value) || 0;
        let totalStock = 0;

        variants.forEach((item, idx) => {
            let adj        = parseFloat(item.price_adjustment) || 0;
            let finalPrice = item.price; // directly use item absolute price
            totalStock    += parseInt(item.stock) || 0;

            let tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50/50 transition border-b border-gray-100';
            tr.innerHTML = `
                <!-- Read-only: Warna -->
                <td class="px-4 py-3">
                    <input type="hidden" name="variants[${idx}][color_hex]" value="${item.color_hex || '#cccccc'}">
                    <input type="hidden" name="variants[${idx}][color]" value="${item.color}">
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full border border-gray-200 inline-block flex-shrink-0" style="background:${item.color_hex || '#cccccc'}"></span>
                        <span class="font-semibold text-gray-700 text-xs">${item.color}</span>
                    </div>
                </td>
                <!-- Read-only: Ukuran -->
                <td class="px-4 py-3">
                    <input type="hidden" name="variants[${idx}][size]" value="${item.size}">
                    <span class="inline-block px-3 py-1 bg-slate-100 text-slate-700 rounded-lg text-xs font-bold text-center">${item.size}</span>
                </td>
                <!-- Editable: Harga -->
                <td class="px-4 py-3">
                    <div class="flex items-center gap-1">
                        <span class="text-gray-400 text-xs">Rp</span>
                        <input type="text" id="vprice-${idx}" value="${formatRupiah(finalPrice)}"
                               oninput="this.value = formatRupiah(this.value); updateVariantPrice(${idx}, parseRupiah(this.value))"
                               class="px-2 py-1.5 border border-gray-200 rounded-lg w-28 text-xs font-semibold focus:ring-1 focus:ring-orange-400 focus:outline-none">
                        <input type="hidden" name="variants[${idx}][price_adjustment]" id="vadjustment-${idx}" value="${adj}">
                    </div>
                </td>
                <!-- Editable: Stok -->
                <td class="px-4 py-3">
                    <input type="number" name="variants[${idx}][stock]" value="${item.stock}" required
                           onchange="updateVariantStock(${idx}, this.value)"
                           class="px-2 py-1.5 border border-gray-200 rounded-lg w-16 text-xs text-center font-semibold focus:ring-1 focus:ring-orange-400 focus:outline-none">
                </td>
                <!-- Editable: SKU -->
                <td class="px-4 py-3">
                    <input type="text" name="variants[${idx}][sku]" value="${item.sku}"
                           onchange="variants[${idx}].sku = this.value"
                           class="px-2 py-1.5 border border-gray-200 rounded-lg w-full text-xs font-mono focus:ring-1 focus:ring-orange-400 focus:outline-none" placeholder="SKU">
                </td>
                <!-- Aksi: Hapus -->
                <td class="px-4 py-3 text-center">
                    <button type="button" onclick="removeVariantRow(${idx})" class="text-red-400 hover:text-red-600 transition" title="Hapus baris">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        document.getElementById('stock').value = totalStock;

        // Kotak unggah foto ikut menyesuaikan warna yang ada
        renderColorPhotos();
    }

    // ───────────────────────────────────────────────────────────── Foto per warna Satu kotak unggah un...

    // Foto warna yang sudah tersimpan (diisi saat mode edit)
    const fotoWarnaTersimpan = {!! json_encode($fotoWarna ?? []) !!};

    // Menyimpan pratinjau berkas yang baru dipilih, agar tidak hilang saat
    // tabel varian dirender ulang
    const pratinjauWarna = {};

    function renderColorPhotos() {
        const wadah = document.getElementById('color-photos');
        const kosong = document.getElementById('color-photos-kosong');
        if (!wadah) return;

        // Ambil daftar warna unik, urut sesuai kemunculannya
        const warnaUnik = [];
        variants.forEach(v => {
            const nama = (v.color || '').trim();
            if (nama !== '' && !warnaUnik.some(w => w.nama === nama)) {
                warnaUnik.push({ nama: nama, hex: v.color_hex || '#cccccc' });
            }
        });

        if (warnaUnik.length === 0) {
            wadah.innerHTML = '';
            kosong.classList.remove('hidden');
            return;
        }
        kosong.classList.add('hidden');

        // Simpan berkas yang sudah dipilih sebelum wadah ditimpa,
        // sebab innerHTML akan menghapus elemen input beserta isinya
        const berkasTersimpan = {};
        wadah.querySelectorAll('input[type="file"]').forEach(inp => {
            if (inp.files && inp.files.length > 0) {
                berkasTersimpan[inp.dataset.warna] = inp.files;
            }
        });

        wadah.innerHTML = '';

        warnaUnik.forEach((w, i) => {
            const lama     = fotoWarnaTersimpan[w.nama] || null;
            const pratinjau = pratinjauWarna[w.nama] || lama;
            const idInput  = 'foto-warna-' + i;

            const kotak = document.createElement('div');
            kotak.className = 'border border-gray-200 rounded-xl p-3 bg-white';
            kotak.innerHTML = `
                <div class="flex items-center gap-2 mb-2.5">
                    <span class="w-4 h-4 rounded-full border border-gray-200 inline-block flex-shrink-0"
                          style="background:${w.hex}"></span>
                    <span class="text-xs font-bold text-gray-700 truncate" title="${w.nama}">${w.nama}</span>
                </div>

                <input type="hidden" name="color_image_colors[${i}]" value="${w.nama}">

                <label for="${idInput}"
                       class="block aspect-square rounded-lg border-2 border-dashed border-gray-200 hover:border-orange-400 bg-gray-50 overflow-hidden cursor-pointer transition relative">
                    ${pratinjau
                        ? `<img src="${pratinjau}" class="w-full h-full object-cover">`
                        : `<span class="absolute inset-0 flex flex-col items-center justify-center text-gray-400">
                               <i class="fa-solid fa-camera text-xl mb-1"></i>
                               <span class="text-[10px] font-semibold">Pilih Foto</span>
                           </span>`}
                </label>

                <input type="file" id="${idInput}" name="color_images[${i}]" accept="image/*"
                       data-warna="${w.nama}" class="hidden"
                       onchange="pilihFotoWarna(this, '${w.nama.replace(/'/g, "\\'")}')">

                <div class="flex items-center gap-2 mt-2">
                    <label for="${idInput}" class="flex-1 text-center text-[10px] font-bold px-2 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 cursor-pointer transition">
                        ${pratinjau ? 'Ganti' : 'Pilih Foto'}
                    </label>
                    ${pratinjau ? `
                        <label class="text-[10px] font-bold px-2 py-1.5 rounded-lg border border-red-200 text-red-500 hover:bg-red-50 cursor-pointer transition">
                            <input type="checkbox" name="color_image_remove[${i}]" value="1" class="sr-only"
                                   onchange="hapusFotoWarna(this, '${w.nama.replace(/'/g, "\\'")}')">
                            <i class="fa-solid fa-trash"></i>
                        </label>` : ''}
                </div>
            `;
            wadah.appendChild(kotak);

            // Kembalikan berkas yang tadi sudah dipilih
            if (berkasTersimpan[w.nama]) {
                kotak.querySelector('input[type="file"]').files = berkasTersimpan[w.nama];
            }
        });
    }

    function pilihFotoWarna(input, warna) {
        const berkas = input.files && input.files[0];
        if (!berkas) return;

        // Lepas URL sementara sebelumnya agar tidak menumpuk di memori
        if (pratinjauWarna[warna] && pratinjauWarna[warna].startsWith('blob:')) {
            URL.revokeObjectURL(pratinjauWarna[warna]);
        }

        pratinjauWarna[warna] = URL.createObjectURL(berkas);
        periksaTotalUkuran();

        // Perbarui pratinjau tanpa merender ulang seluruh wadah,
        // supaya berkas yang sudah dipilih di kotak lain tidak hilang
        const kotak = input.closest('div');
        const label = kotak.querySelector('label[for]');
        if (label) {
            label.innerHTML = `<img src="${pratinjauWarna[warna]}" class="w-full h-full object-cover">`;
        }
    }

    function hapusFotoWarna(checkbox, warna) {
        const kotak = checkbox.closest('.border');
        if (checkbox.checked) {
            kotak.style.opacity = '0.45';
            checkbox.parentElement.classList.add('bg-red-100');
        } else {
            kotak.style.opacity = '';
            checkbox.parentElement.classList.remove('bg-red-100');
        }
    }

    function updateVariantPrice(idx, val) {
        variants[idx].price = parseFloat(val) || 0;
        recalculateBasePriceAndAdjustments();
        
        // Directly update hidden adjustments in the DOM without redraw to preserve focus
        variants.forEach((item, i) => {
            let adjInput = document.getElementById(`vadjustment-${i}`);
            if (adjInput) {
                adjInput.value = item.price_adjustment;
            }
        });
    }

    function updateVariantStock(idx, val) {
        variants[idx].stock = parseInt(val) || 0;
        document.getElementById('stock').value = variants.reduce((s, v) => s + (parseInt(v.stock) || 0), 0);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // BULK CHANGE FUNCTIONS
    // ──────────────────────────────────────────────────────────────────────────

    function applyBulkChanges() {
        let bulkPriceVal = document.getElementById('bulk-price').value;
        let bulkStockVal = document.getElementById('bulk-stock').value;
        let bulkSkuPrefix = document.getElementById('bulk-sku').value.trim();

        variants.forEach((item, idx) => {
            if (bulkPriceVal !== '') {
                item.price = parseRupiah(bulkPriceVal);
            }
            if (bulkStockVal !== '') {
                item.stock = parseInt(bulkStockVal) || 0;
            }
            if (bulkSkuPrefix !== '') {
                let rand = Math.random().toString(36).substring(2, 6).toUpperCase();
                item.sku = `${bulkSkuPrefix.toUpperCase()}-${item.color.toUpperCase().substring(0, 3).replace(/[^A-Z0-9]/g, '')}-${item.size.toUpperCase()}-${rand}`;
            }
        });

        recalculateBasePriceAndAdjustments();
        renderVariantsTable();
        alert('Perubahan massal berhasil diterapkan!');
    }
</script>
@endsection

