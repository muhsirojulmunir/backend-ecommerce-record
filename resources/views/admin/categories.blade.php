@extends('layouts.app')

@section('title', 'Kelola Kategori')
@section('page_title', 'Kelola Kategori')
@section('page_subtitle', 'Kategori yang aktif langsung tampil di halaman utama toko.')

@section('content')
<div class="space-y-6" x-data="categoryManager()">

    {{-- ── Statistik ── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-layer-group"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400">Total Kategori</p>
                <h3 class="text-xl font-black text-gray-800 mt-0.5">{{ $stats['total'] }}</h3>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-eye"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400">Tampil di Toko</p>
                <h3 class="text-xl font-black text-gray-800 mt-0.5">{{ $stats['active'] }}</h3>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-box-open"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400">Total Produk</p>
                <h3 class="text-xl font-black text-gray-800 mt-0.5">{{ $stats['products'] }}</h3>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-folder-closed"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400">Kategori Kosong</p>
                <h3 class="text-xl font-black text-gray-800 mt-0.5">{{ $stats['empty'] }}</h3>
            </div>
        </div>
    </div>

    {{-- Flash Errors --}}
    @if ($errors->any())
        <div class="flash-alert p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm shadow-sm">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Flash Success --}}
    @if (session('success'))
        <div class="flash-alert p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.closest('.flash-alert').remove()" class="text-emerald-500 hover:text-emerald-700">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    {{-- ── Header ── --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="p-3.5 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-xs flex items-start gap-2.5 flex-1">
            <i class="fa-solid fa-circle-info text-blue-500 mt-0.5"></i>
            <span class="leading-relaxed">
                Kategori yang <strong>aktif</strong> langsung muncul di halaman utama toko dan katalog.
                Kategori yang dinonaktifkan disembunyikan dari pembeli, tetapi produknya tetap tersimpan.
            </span>
        </div>
        <button type="button" @click="openAddModal()"
            class="inline-flex items-center gap-2 bg-orange-600 text-white text-xs font-bold px-5 py-2.5 rounded-xl shadow hover:bg-orange-700 transition shrink-0 cursor-pointer">
            <i class="fa-solid fa-plus"></i>
            Tambah Kategori
        </button>
    </div>

    {{-- ── Daftar Kategori ── --}}
    @if($categories->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-16 text-center">
            <i class="fa-regular fa-folder-open text-4xl text-gray-300 mb-4 block"></i>
            <h3 class="text-sm font-bold text-gray-500">Belum ada kategori</h3>
            <p class="text-xs text-gray-400 mt-1">Klik <strong>Tambah Kategori</strong> untuk mulai menata produk toko.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @foreach($categories as $index => $category)
                @php
                    $payload = [
                        'id'          => $category->id,
                        'name'        => $category->name,
                        'description' => $category->description ?? '',
                        'sort_order'  => $category->sort_order,
                        'is_active'   => (bool) $category->is_active,
                        'image'       => $category->image ? asset('storage/' . $category->image) : null,
                        'position_x'  => $category->image_position_x ?? 50,
                        'position_y'  => $category->image_position_y ?? 50,
                        'zoom'        => (float) ($category->image_zoom ?: 1),
                    ];
                @endphp
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col {{ $category->is_active ? '' : 'opacity-70' }}">

                    {{-- Pratinjau kartu gambar --}}
                    <div class="relative bg-slate-800 overflow-hidden" style="aspect-ratio: 1/1;">
                        @if($category->image)
                            <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}"
                                 class="w-full h-full" style="{{ $category->image_style }}">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-slate-700 to-slate-900">
                                <i class="fa-solid fa-image text-3xl text-slate-600"></i>
                            </div>
                        @endif

                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/90 via-slate-900/30 to-transparent"></div>

                        <div class="absolute bottom-0 left-0 right-0 p-4">
                            <h3 class="text-base font-black text-white uppercase tracking-wide truncate">{{ $category->name }}</h3>
                        </div>

                        <div class="absolute top-3 left-3 flex gap-1.5">
                            @if($category->is_active)
                                <span class="bg-emerald-500 text-white text-[9px] font-black px-2 py-0.5 rounded-full uppercase">Tampil</span>
                            @else
                                <span class="bg-gray-500 text-white text-[9px] font-black px-2 py-0.5 rounded-full uppercase">Disembunyikan</span>
                            @endif
                        </div>

                        <span class="absolute top-3 right-3 bg-black/40 backdrop-blur text-white text-[9px] font-black px-2 py-0.5 rounded-full">
                            #{{ $category->sort_order }}
                        </span>
                    </div>

                    {{-- Info --}}
                    <div class="p-5 flex-1 space-y-2">
                        <div class="flex items-center gap-3 text-[11px] font-bold">
                            <span class="text-gray-700">
                                <i class="fa-solid fa-box-open text-gray-300 mr-1"></i>
                                {{ $category->products_count }} produk
                            </span>
                            <span class="text-emerald-600">
                                {{ $category->active_products_count }} aktif
                            </span>
                        </div>

                        @if($category->description)
                            <p class="text-[11px] text-gray-500 leading-relaxed line-clamp-2">{{ $category->description }}</p>
                        @endif

                        <code class="block text-[10px] text-gray-300">/category/{{ $category->slug }}</code>
                    </div>

                    {{-- Aksi --}}
                    <div class="px-4 py-3 border-t border-gray-50 bg-gray-50/50 flex flex-wrap items-center gap-1.5">
                        {{-- Urutan --}}
                        <form action="{{ route('admin.categories.reorder', $category->id) }}" method="POST">
                            @csrf @method('PATCH')
                            <input type="hidden" name="direction" value="up">
                            <button type="submit" @disabled($loop->first)
                                class="w-8 h-8 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-100 transition disabled:opacity-30 disabled:cursor-not-allowed"
                                title="Naikkan urutan">
                                <i class="fa-solid fa-arrow-up text-[10px]"></i>
                            </button>
                        </form>
                        <form action="{{ route('admin.categories.reorder', $category->id) }}" method="POST">
                            @csrf @method('PATCH')
                            <input type="hidden" name="direction" value="down">
                            <button type="submit" @disabled($loop->last)
                                class="w-8 h-8 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-100 transition disabled:opacity-30 disabled:cursor-not-allowed"
                                title="Turunkan urutan">
                                <i class="fa-solid fa-arrow-down text-[10px]"></i>
                            </button>
                        </form>

                        {{-- Tombol Edit --}}
                        <button type="button"
                            @click="openEditModal({{ Js::from($payload) }})"
                            class="text-xs font-bold px-3 py-1.5 rounded-lg border border-blue-200 text-blue-600 hover:bg-blue-50 transition cursor-pointer">
                            <i class="fa-solid fa-pen-to-square mr-1"></i>Edit
                        </button>

                        {{-- Toggle Status --}}
                        <form action="{{ route('admin.categories.toggle', $category->id) }}" method="POST">
                            @csrf @method('PATCH')
                            <button type="submit"
                                class="text-xs font-bold px-3 py-1.5 rounded-lg border transition
                                    {{ $category->is_active ? 'border-amber-200 text-amber-600 hover:bg-amber-50' : 'border-emerald-200 text-emerald-600 hover:bg-emerald-50' }}">
                                <i class="fa-solid {{ $category->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                            </button>
                        </form>

                        {{-- Hapus --}}
                        <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="ml-auto"
                              onsubmit="return confirm('Hapus kategori &quot;{{ $category->name }}&quot;?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                class="text-xs font-bold px-3 py-1.5 rounded-lg border border-red-200 text-red-500 hover:bg-red-50 transition
                                    {{ $category->products_count > 0 ? 'opacity-40' : '' }}"
                                title="{{ $category->products_count > 0 ? 'Masih ada produk di kategori ini' : 'Hapus kategori' }}">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ════════════════════════════════ --}}
    {{-- MODAL: Tambah / Edit Kategori    --}}
    {{-- ════════════════════════════════ --}}
    <div x-show="showModal" x-cloak
         class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg flex flex-col overflow-hidden"
             style="max-height:90vh;"
             @click.away="closeModal()">

            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center shrink-0">
                <h2 class="font-black text-gray-800 text-sm uppercase tracking-wide"
                    x-text="isEdit ? 'Edit Kategori' : 'Tambah Kategori'"></h2>
                <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <form :action="formUrl" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 overflow-hidden">
                @csrf
                <input type="hidden" name="_method" :value="isEdit ? 'PUT' : 'POST'">

                <div class="px-6 py-5 space-y-4 overflow-y-auto flex-1" style="max-height:calc(90vh - 145px);">

                    {{-- ── Pengatur Gambar Kategori ── --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-2">Gambar Kategori</label>

                        {{-- Hidden inputs --}}
                        <input type="hidden" name="image_position_x" :value="posX">
                        <input type="hidden" name="image_position_y" :value="posY">
                        <input type="hidden" name="image_zoom" :value="zoom">
                        <input type="hidden" name="remove_image" :value="removeImage ? '1' : '0'">

                        {{-- File input --}}
                        <input type="file" x-ref="imageInput" name="image" accept="image/*" class="hidden" @change="onFileSelected($event)">

                        <div class="flex flex-col sm:flex-row items-start gap-5">
                            {{-- Bingkai pratinjau gambar --}}
                            <div class="relative w-56 shrink-0 rounded-xl overflow-hidden border-2 bg-slate-100 select-none"
                                 :class="hasImage && !removeImage ? 'border-orange-300 cursor-grab active:cursor-grabbing' : 'border-dashed border-gray-300 cursor-pointer'"
                                 style="aspect-ratio: 1/1;"
                                 @pointerdown="onPointerDown($event)"
                                 @pointermove="onPointerMove($event)"
                                 @pointerup="onPointerUp($event)"
                                 @pointercancel="onPointerUp($event)"
                                 @click="if (!hasImage || removeImage) $refs.imageInput.click()">

                                <template x-if="hasImage && !removeImage">
                                    <img :src="imagePreview" alt="" draggable="false"
                                         class="absolute inset-0 w-full h-full pointer-events-none"
                                         :style="imageStyle">
                                </template>

                                <template x-if="!hasImage || removeImage">
                                    <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 p-4 text-center">
                                        <i class="fa-solid fa-cloud-arrow-up text-3xl mb-2 text-orange-500/60"></i>
                                        <p class="text-xs font-bold text-gray-600">Klik untuk pilih gambar</p>
                                        <p class="text-[10px] text-gray-400 mt-0.5">JPG, PNG, WEBP — maks. 8MB</p>
                                    </div>
                                </template>

                                <template x-if="hasImage && !removeImage">
                                    <div class="absolute bottom-2 left-2 right-2 bg-black/60 backdrop-blur text-white text-[10px] font-bold px-2.5 py-1 rounded-lg text-center pointer-events-none">
                                        <i class="fa-solid fa-arrows-up-down-left-right mr-1"></i>Tahan & geser untuk atur posisi
                                    </div>
                                </template>
                            </div>

                            {{-- Kontrol Gambar --}}
                            <div class="flex-1 min-w-0 w-full space-y-3">
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" @click="$refs.imageInput.click()"
                                            class="text-xs font-bold px-3.5 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                                        <i class="fa-solid fa-upload mr-1"></i>
                                        <span x-text="hasImage && !removeImage ? 'Ganti Gambar' : 'Pilih Gambar'"></span>
                                    </button>

                                    <button type="button" x-show="hasImage && !removeImage" @click="resetPosition()"
                                            class="text-xs font-bold px-3.5 py-2 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                                        <i class="fa-solid fa-arrows-rotate mr-1"></i>Tengah
                                    </button>

                                    <button type="button" x-show="hasImage && !removeImage" @click="removeImage = true"
                                            class="text-xs font-bold px-3.5 py-2 rounded-xl border border-red-200 text-red-500 hover:bg-red-50 transition">
                                        <i class="fa-solid fa-trash mr-1"></i>Hapus
                                    </button>
                                </div>

                                <div x-show="removeImage" class="text-xs font-bold text-red-600 bg-red-50 border border-red-200 rounded-xl px-3 py-2">
                                    <i class="fa-solid fa-circle-exclamation mr-1"></i>
                                    Gambar akan dihapus saat disimpan.
                                    <button type="button" @click="removeImage = false" class="underline ml-1">Batalkan</button>
                                </div>

                                <div x-show="hasImage && !removeImage">
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="text-[11px] font-bold text-gray-600">Perbesaran (Zoom)</label>
                                        <span class="text-[11px] font-black text-orange-600" x-text="Math.round(zoom * 100) + '%'"></span>
                                    </div>
                                    <input type="range" min="1" max="3" step="0.05" x-model.number="zoom"
                                           class="w-full accent-orange-600">
                                </div>

                                <p class="text-[10px] text-gray-400 leading-relaxed">
                                    Tampilan bingkai di atas sama persis dengan kartu di halaman depan toko.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Nama Kategori --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1.5">Nama Kategori <span class="text-red-500">*</span></label>
                        <input type="text" name="name" x-model="form.name" required maxlength="100"
                               class="w-full border border-gray-200 rounded-xl py-2.5 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500"
                               placeholder="Contoh: Sepatu Sekolah">
                    </div>

                    {{-- Deskripsi --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1.5">Deskripsi</label>
                        <textarea name="description" x-model="form.description" rows="2" maxlength="500"
                                  class="w-full border border-gray-200 rounded-xl py-2.5 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500"
                                  placeholder="Keterangan singkat (opsional)"></textarea>
                    </div>

                    {{-- Urutan Tampil --}}
                    <div x-show="isEdit">
                        <label class="block text-xs font-bold text-gray-600 mb-1.5">Urutan Tampil</label>
                        <input type="number" name="sort_order" x-model.number="form.sort_order" min="0"
                               class="w-32 border border-gray-200 rounded-xl py-2.5 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                    </div>

                    {{-- Checkbox Aktif --}}
                    <label class="flex items-center gap-3 cursor-pointer p-3 rounded-xl bg-gray-50 border border-gray-100">
                        <input type="checkbox" name="is_active" value="1" x-model="form.is_active"
                               class="w-4 h-4 accent-orange-600 rounded">
                        <div>
                            <span class="text-xs font-bold text-gray-700">Tampilkan di halaman toko</span>
                            <p class="text-[10px] text-gray-400">Jika dimatikan, kategori disembunyikan dari pembeli.</p>
                        </div>
                    </label>
                </div>

                {{-- Footer Modal --}}
                <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3 shrink-0 bg-white rounded-b-3xl">
                    <button type="button" @click="closeModal()"
                        class="text-xs font-bold px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 transition">Batal</button>
                    <button type="submit"
                        class="text-xs font-bold px-6 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white shadow transition">
                        <i class="fa-solid fa-floppy-disk mr-1.5"></i>Simpan Kategori
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function categoryManager() {
    return {
        showModal: false,
        isEdit: false,
        formUrl: '',
        form: {
            id: null,
            name: '',
            description: '',
            sort_order: 0,
            is_active: true,
        },
        imagePreview: null,
        hasImage: false,
        removeImage: false,
        posX: 50,
        posY: 50,
        zoom: 1,

        // Status Dragging
        isDragging: false,
        dragStartX: 0,
        dragStartY: 0,
        dragStartPosX: 50,
        dragStartPosY: 50,

        get imageStyle() {
            return `object-fit:cover;object-position:${this.posX}% ${this.posY}%;`
                 + `transform:scale(${this.zoom});transform-origin:${this.posX}% ${this.posY}%;`;
        },

        openAddModal() {
            this.isEdit = false;
            this.formUrl = '{{ route('admin.categories.store') }}';
            this.form = { id: null, name: '', description: '', sort_order: 0, is_active: true };
            this.imagePreview = null;
            this.hasImage = false;
            this.removeImage = false;
            this.resetPosition();
            this.showModal = true;
        },

        openEditModal(data) {
            this.isEdit = true;
            this.formUrl = '{{ url('admin/categories') }}/' + data.id;
            this.form = {
                id: data.id,
                name: data.name || '',
                description: data.description || '',
                sort_order: data.sort_order || 0,
                is_active: !!data.is_active,
            };
            this.imagePreview = data.image || null;
            this.hasImage = !!data.image;
            this.removeImage = false;
            this.posX = data.position_x ?? 50;
            this.posY = data.position_y ?? 50;
            this.zoom = data.zoom ?? 1;
            this.showModal = true;
        },

        closeModal() {
            this.showModal = false;
        },

        isCompressing: false,

        async onFileSelected(e) {
            const file = e.target.files && e.target.files[0];
            if (!file) return;

            this.isCompressing = true;

            try {
                // Kompresi otomatis gambar besar di browser
                const compressedFile = await this.compressImage(file);

                // Masukkan file terkompresi kembali ke input file
                if (window.DataTransfer) {
                    const dt = new DataTransfer();
                    dt.items.add(compressedFile);
                    this.$refs.imageInput.files = dt.files;
                }

                if (this.imagePreview && this.imagePreview.startsWith('blob:')) {
                    URL.revokeObjectURL(this.imagePreview);
                }

                this.imagePreview = URL.createObjectURL(compressedFile);
                this.hasImage = true;
                this.removeImage = false;
                this.resetPosition();
            } catch (err) {
                console.error('Compress error:', err);
                if (this.imagePreview && this.imagePreview.startsWith('blob:')) {
                    URL.revokeObjectURL(this.imagePreview);
                }
                this.imagePreview = URL.createObjectURL(file);
                this.hasImage = true;
                this.removeImage = false;
                this.resetPosition();
            } finally {
                this.isCompressing = false;
            }
        },

        compressImage(file) {
            return new Promise((resolve) => {
                // Jika file sudah kecil (< 400KB), gunakan langsung
                if (file.size < 400 * 1024) {
                    resolve(file);
                    return;
                }

                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = (event) => {
                    const img = new Image();
                    img.src = event.target.result;
                    img.onload = () => {
                        const canvas = document.createElement('canvas');
                        const maxDimension = 1200; // Maksimal resolusi 1200px (Sangat cukup untuk e-commerce)
                        let width = img.width;
                        let height = img.height;

                        if (width > height) {
                            if (width > maxDimension) {
                                height = Math.round((height * maxDimension) / width);
                                width = maxDimension;
                            }
                        } else {
                            if (height > maxDimension) {
                                width = Math.round((width * maxDimension) / height);
                                height = maxDimension;
                            }
                        }

                        canvas.width = width;
                        canvas.height = height;

                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);

                        canvas.toBlob(
                            (blob) => {
                                if (blob) {
                                    const compressedFile = new File(
                                        [blob],
                                        file.name.replace(/\.[^/.]+$/, "") + ".jpg",
                                        { type: 'image/jpeg', lastModified: Date.now() }
                                    );
                                    resolve(compressedFile);
                                } else {
                                    resolve(file);
                                }
                            },
                            'image/jpeg',
                            0.82 // Kualitas 82% (tajam & ukuran kecil ~200KB)
                        );
                    };
                    img.onerror = () => resolve(file);
                };
                reader.onerror = () => resolve(file);
            });
        },

        resetPosition() {
            this.posX = 50;
            this.posY = 50;
            this.zoom = 1;
        },

        onPointerDown(e) {
            if (!this.hasImage || this.removeImage) return;
            this.isDragging = true;
            this.dragStartX = e.clientX;
            this.dragStartY = e.clientY;
            this.dragStartPosX = this.posX;
            this.dragStartPosY = this.posY;
            e.currentTarget.setPointerCapture?.(e.pointerId);
        },

        onPointerMove(e) {
            if (!this.isDragging) return;
            e.preventDefault();
            const rect = e.currentTarget.getBoundingClientRect();
            const deltaX = ((e.clientX - this.dragStartX) / rect.width) * 100;
            const deltaY = ((e.clientY - this.dragStartY) / rect.height) * 100;
            this.posX = Math.min(100, Math.max(0, Math.round(this.dragStartPosX - deltaX)));
            this.posY = Math.min(100, Math.max(0, Math.round(this.dragStartPosY - deltaY)));
        },

        onPointerUp(e) {
            if (!this.isDragging) return;
            this.isDragging = false;
            e.currentTarget.releasePointerCapture?.(e.pointerId);
        }
    }
}
</script>
@endsection
