@extends('layouts.app')

@section('title', 'Kelola Diskon')
@section('page_title', 'Kelola Diskon Produk')

@section('content')
<!-- Alpine.js -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<div class="space-y-6 pb-24" x-data="discountManager()">

    {{-- Flash --}}
    @if(session('success'))
        <div class="flash-alert flex items-center justify-between bg-green-50 border border-green-200 text-green-800 text-sm px-5 py-3.5 rounded-2xl shadow-sm transition-all duration-500 overflow-hidden">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-green-500"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.closest('.flash-alert').remove()" class="text-green-600 hover:text-green-800 transition p-1">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
    @endif

    {{-- ── Header: Search + Bulk ── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center">
            <form method="GET" action="{{ route('admin.discounts') }}" class="flex-1 max-w-xs relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nama produk..."
                    class="block w-full pl-8 pr-3 py-2 border border-gray-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-orange-500 transition placeholder-gray-400">
            </form>

            <div class="flex-1 bg-orange-50/70 border border-orange-100 rounded-2xl p-4 w-full">
                <p class="text-[10px] font-extrabold text-orange-800 uppercase tracking-wider mb-2">
                    <i class="fa-solid fa-pen-to-square mr-1"></i>Ubah Massal
                    <span class="ml-1 text-orange-500">(<span x-text="selectedIds.length"></span> produk dipilih)</span>
                </p>
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <input type="number" x-model="bulkDiscount" min="0" max="100" step="1"
                            placeholder="%" 
                            class="pl-3 pr-9 py-2 border border-gray-200 rounded-xl text-xs w-24 focus:ring-2 focus:ring-orange-500 focus:outline-none">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs">%</span>
                    </div>
                    <button @click="applyBulk()"
                        class="bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold py-2 px-4 rounded-xl transition shadow-sm flex items-center gap-1.5">
                        <i class="fa-solid fa-check"></i> Terapkan ke Semua
                    </button>
                    <button @click="deleteBulk()"
                        class="bg-gray-100 hover:bg-red-50 text-gray-600 hover:text-red-600 text-xs font-bold py-2 px-4 rounded-xl transition border border-gray-200 flex items-center gap-1.5">
                        <i class="fa-solid fa-trash-can"></i> Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Table ── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 text-slate-400 uppercase border-b border-gray-100 text-[10px] tracking-wider">
                    <tr>
                        <th class="px-4 py-3 w-8">
                            <input type="checkbox" @change="toggleAll($event)">
                        </th>
                        <th class="px-4 py-3 min-w-[200px]">Nama Produk / Varian</th>
                        <th class="px-4 py-3 whitespace-nowrap">Harga Awal</th>
                        <th class="px-4 py-3 whitespace-nowrap">Harga Diskon</th>
                        <th class="px-4 py-3">Diskon (%)</th>
                        <th class="px-4 py-3 whitespace-nowrap">Tanggal Mulai</th>
                        <th class="px-4 py-3 whitespace-nowrap">Tanggal Berakhir</th>
                        <th class="px-4 py-3 text-center">Aktif</th>
                    </tr>
                </thead>

                {{-- One tbody per product (Alpine scope) --}}
                @forelse($products as $product)
                @php
                    $productDiscount = $product->allDiscounts()->whereNull('product_variant_id')->first();
                    $discPct   = $productDiscount ? (float)$productDiscount->discount_percentage : 0;
                    $basePrice = (float)$product->price;
                    $isActive  = $productDiscount ? (bool)$productDiscount->is_active : false;
                    $startsAt  = $productDiscount?->starts_at?->format('Y-m-d') ?? '';
                    $endsAt    = $productDiscount?->ends_at?->format('Y-m-d') ?? '';

                    // Build variant data with their own discounts
                    $variantsData = [];
                    foreach ($product->variants as $v) {
                        $vDiscount = $v->discounts()->active()->first() ?? null;
                        $vPrice = $basePrice + (float)$v->price_adjustment;
                        $variantsData[] = [
                            'id'        => $v->id,
                            'color'     => $v->color,
                            'color_hex' => $v->color_hex,
                            'size'      => $v->size,
                            'sku'       => $v->sku,
                            'stock'     => $v->stock,
                            'price'     => $vPrice,
                            'pct'       => $vDiscount ? (float)$vDiscount->discount_percentage : -1,
                            // -1 means "follow product discount"
                        ];
                    }
                @endphp
                <tbody class="divide-y divide-gray-100"
                       x-data="productRow(
                           {{ $product->id }},
                           {{ $discPct }},
                           {{ $basePrice }},
                           {{ $isActive ? 'true' : 'false' }},
                           '{{ $startsAt }}',
                           '{{ $endsAt }}',
                           {{ json_encode($variantsData) }}
                       )">

                    {{-- ── Product Row ── --}}
                    <tr class="bg-white hover:bg-slate-50/50 transition border-t-2 border-slate-200">
                        <td class="px-4 py-4">
                            <input type="checkbox" value="{{ $product->id }}"
                                :checked="selectedIds.includes({{ $product->id }})"
                                @change="toggleSelect({{ $product->id }}, $event.target.checked)">
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-3">
                                @if($product->variants->isNotEmpty())
                                <button type="button" @click="expanded = !expanded"
                                    class="text-gray-400 hover:text-orange-500 transition p-1 shrink-0">
                                    <i class="fa-solid text-xs" :class="expanded ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
                                </button>
                                @else
                                <span class="w-5 shrink-0"></span>
                                @endif
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                    class="w-10 h-10 rounded-lg object-cover bg-gray-100 border border-gray-100 shrink-0">
                                <div>
                                    <p class="font-bold text-gray-800 max-w-[200px] truncate">{{ $product->name }}</p>
                                    <p class="text-gray-400 text-[10px] flex items-center gap-1 mt-0.5">
                                        <span>ID: {{ $product->id }}</span>
                                        @if($product->variants->isNotEmpty())
                                        <span class="bg-orange-100 text-orange-600 px-1.5 py-0.5 rounded text-[8px] font-bold">
                                            {{ $product->variants->count() }} Varian
                                        </span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap font-semibold text-gray-700">
                            Rp {{ number_format($product->price, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="font-bold text-orange-600" x-text="discountedPriceFormatted(basePrice)"></span>
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-1.5">
                                <input type="number" min="0" max="100" step="1"
                                    x-model="pct"
                                    @input="markDirty()"
                                    class="px-2.5 py-1.5 border border-gray-200 rounded-lg w-16 text-center text-xs font-semibold focus:ring-1 focus:ring-orange-400 focus:outline-none">
                                <span class="text-gray-400 text-xs">%</span>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <input type="date" x-model="startsAt" @change="markDirty()"
                                class="px-2.5 py-1.5 border border-gray-200 rounded-lg text-xs focus:ring-1 focus:ring-orange-400 focus:outline-none w-36">
                        </td>
                        <td class="px-4 py-4">
                            <input type="date" x-model="endsAt" @change="markDirty()"
                                class="px-2.5 py-1.5 border border-gray-200 rounded-lg text-xs focus:ring-1 focus:ring-orange-400 focus:outline-none w-36">
                        </td>
                        <td class="px-4 py-4 text-center">
                            <button @click="toggleActive({{ $product->id }})"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-300"
                                :class="active ? 'bg-green-500' : 'bg-gray-300'">
                                <span class="inline-block h-4 w-4 rounded-full bg-white shadow transition-transform duration-300"
                                    :class="active ? 'translate-x-6' : 'translate-x-1'"></span>
                            </button>
                        </td>
                    </tr>

                    {{-- ── Variant Sub-rows ── --}}
                    <template x-for="(v, idx) in variants" :key="v.id">
                        <tr x-show="expanded" x-transition class="bg-slate-50/60 border-b border-slate-100 text-xs">
                            <td></td>
                            <td class="px-4 py-2.5 pl-14">
                                <div class="flex items-center gap-2">
                                    <span class="w-3.5 h-3.5 rounded-full border border-gray-200 shrink-0"
                                        :style="'background:' + (v.color_hex || '#ccc')"></span>
                                    <span class="font-semibold text-gray-700" x-text="v.color + ' - ' + v.size"></span>
                                    <span class="text-[10px] text-gray-400 font-mono" x-text="'(' + v.sku + ')'"></span>
                                    <span class="text-[10px] text-gray-400">Stok: <b x-text="v.stock"></b></span>
                                </div>
                            </td>
                            <td class="px-4 py-2.5 whitespace-nowrap">
                                <span class="text-gray-500 font-medium" x-text="'Rp ' + Math.round(v.price).toLocaleString('id-ID')"></span>
                            </td>
                            <td class="px-4 py-2.5 whitespace-nowrap">
                                <span class="font-extrabold text-orange-600"
                                    x-text="effectivePct(v) > 0 ? 'Rp ' + Math.round(v.price * (1 - effectivePct(v)/100)).toLocaleString('id-ID') : '-'">
                                </span>
                            </td>
                            <td class="px-4 py-2.5">
                                <div class="flex items-center gap-1">
                                    {{-- Input variant discount. Placeholder shows "= product%" to indicate inheriting --}}
                                    <input type="number" min="0" max="100" step="1"
                                        :value="v.pct >= 0 ? v.pct : ''"
                                        @input="setVariantPct(idx, $event.target.value); markDirty()"
                                        :placeholder="pct > 0 ? pct + '% (produk)' : 'ikut produk'"
                                        class="px-2 py-1 border border-gray-200 rounded-lg w-24 text-center text-[10px] font-semibold focus:ring-1 focus:ring-orange-400 focus:outline-none placeholder-gray-300">
                                    <span class="text-gray-400 text-[10px]">%</span>
                                </div>
                            </td>
                            <td colspan="3" class="px-4 py-2.5 text-[10px] text-gray-400 italic">
                                <span x-show="v.pct < 0">Mengikuti diskon produk</span>
                                <span x-show="v.pct >= 0" class="text-orange-600 font-bold">Diskon khusus varian</span>
                            </td>
                        </tr>
                    </template>
                </tbody>
                @empty
                <tbody>
                    <tr>
                        <td colspan="8" class="text-center py-16 text-gray-400">
                            <i class="fa-regular fa-face-sad-tear text-4xl mb-2 block"></i>
                            Belum ada produk.
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

{{-- ── Sticky Bottom Bar ── --}}
<div class="fixed bottom-0 left-0 right-0 z-50 bg-white border-t border-gray-200 shadow-2xl"
     x-data="stickyBar()"
     x-show="hasDirty"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="translate-y-full"
     x-transition:enter-end="translate-y-0"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="translate-y-0"
     x-transition:leave-end="translate-y-full"
     style="display:none">
    <div class="max-w-7xl mx-auto px-6 py-3 flex items-center justify-between gap-4">
        <div class="flex items-center gap-2 text-sm text-gray-600">
            <i class="fa-solid fa-circle-info text-orange-500"></i>
            <span>Ada perubahan diskon yang belum disimpan. Konfirmasi untuk menyimpan ke semua produk yang diubah.</span>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <button @click="batal()"
                class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-bold py-2.5 px-6 rounded-xl transition">
                <i class="fa-solid fa-xmark mr-1.5"></i>Batal
            </button>
            <button @click="konfirmasi()"
                :disabled="saving"
                class="bg-orange-500 hover:bg-orange-600 disabled:opacity-60 text-white text-sm font-bold py-2.5 px-6 rounded-xl transition shadow-md flex items-center gap-2">
                <i class="fa-solid fa-floppy-disk"></i>
                <span x-text="saving ? 'Menyimpan...' : 'Konfirmasi & Simpan'"></span>
            </button>
        </div>
    </div>
</div>

{{-- Toast --}}
<div id="discount-toast" 
     class="fixed bottom-20 right-6 z-50 items-center gap-3 bg-gray-900 text-white text-xs font-semibold px-5 py-3 rounded-2xl shadow-xl hidden">
    <i class="fa-solid fa-circle-check text-green-400 mr-1"></i>
    <span id="discount-toast-msg"></span>
</div>

<script>
// ── Shared state between product rows and sticky bar ────────────────────────
const _discountState = {
    dirtyRows: new Map(), // productId => { pct, startsAt, endsAt, active, variants[] }
    listeners: [],

    setDirty(id, data) {
        this.dirtyRows.set(id, data);
        this.notify();
    },

    clearDirty(id) {
        if (id) { this.dirtyRows.delete(id); }
        else { this.dirtyRows.clear(); }
        this.notify();
    },

    hasDirty() {
        return this.dirtyRows.size > 0;
    },

    notify() {
        this.listeners.forEach(fn => fn());
    },

    addListener(fn) {
        this.listeners.push(fn);
    }
};

// ── discountManager (top-level: search & bulk) ──────────────────────────────
function discountManager() {
    return {
        selectedIds: [],
        bulkDiscount: '',

        toggleAll(e) {
            const checkboxes = document.querySelectorAll('tbody input[type=checkbox]');
            this.selectedIds = [];
            checkboxes.forEach(cb => {
                cb.checked = e.target.checked;
                const id = parseInt(cb.value);
                if (e.target.checked && !isNaN(id)) this.selectedIds.push(id);
            });
        },

        toggleSelect(id, checked) {
            if (checked) { if (!this.selectedIds.includes(id)) this.selectedIds.push(id); }
            else { this.selectedIds = this.selectedIds.filter(i => i !== id); }
        },

        async applyBulk() {
            if (!this.selectedIds.length) { alert('Pilih minimal satu produk.'); return; }
            if (this.bulkDiscount === '') { alert('Masukkan persentase diskon.'); return; }
            const r = await fetch('{{ route("admin.discounts.bulk") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ product_ids: this.selectedIds, discount_percentage: parseFloat(this.bulkDiscount) })
            });
            const d = await r.json();
            if (d.success) { showToast(d.message); setTimeout(() => location.reload(), 1200); }
        },

        async deleteBulk() {
            if (!this.selectedIds.length) { alert('Pilih minimal satu produk.'); return; }
            if (!confirm('Hapus diskon untuk ' + this.selectedIds.length + ' produk?')) return;
            const r = await fetch('{{ route("admin.discounts.bulk") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ product_ids: this.selectedIds, discount_percentage: 0 })
            });
            const d = await r.json();
            if (d.success) { showToast('Diskon dihapus.'); setTimeout(() => location.reload(), 1200); }
        }
    };
}

// ── productRow (per product tbody) ──────────────────────────────────────────
function productRow(productId, initialPct, basePrice, initialActive, initialStartsAt, initialEndsAt, initialVariants) {
    return {
        pct: initialPct,
        basePrice,
        active: initialActive,
        startsAt: initialStartsAt,
        endsAt: initialEndsAt,
        variants: JSON.parse(JSON.stringify(initialVariants)), // deep copy so changes are local
        expanded: false,

        // Compute effective discount percentage for a variant
        effectivePct(v) {
            return v.pct >= 0 ? v.pct : (parseFloat(this.pct) || 0);
        },

        discountedPriceFormatted(price) {
            const p = parseFloat(this.pct) || 0;
            if (p <= 0) return '-';
            return 'Rp ' + Math.round(price * (1 - p / 100)).toLocaleString('id-ID');
        },

        setVariantPct(idx, val) {
            const num = val === '' ? -1 : parseFloat(val);
            this.variants[idx].pct = isNaN(num) ? -1 : num;
        },

        markDirty() {
            _discountState.setDirty(productId, {
                pct: this.pct,
                startsAt: this.startsAt,
                endsAt: this.endsAt,
                active: this.active,
                variants: this.variants
            });
        },

        async toggleActive(productId) {
            this.active = !this.active;
            const r = await fetch(`/admin/discounts/${productId}/toggle`, {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            const d = await r.json();
            if (!d.success) this.active = !this.active;
            else this.markDirty();
        }
    };
}

// ── stickyBar (bottom save/cancel bar) ─────────────────────────────────────
function stickyBar() {
    return {
        hasDirty: false,
        saving: false,

        init() {
            _discountState.addListener(() => {
                this.hasDirty = _discountState.hasDirty();
            });
        },

        batal() {
            _discountState.clearDirty();
            this.hasDirty = false;
            location.reload();
        },

        async konfirmasi() {
            if (this.saving) return;
            this.saving = true;

            const promises = [];
            _discountState.dirtyRows.forEach((data, productId) => {
                const variantDiscounts = data.variants
                    .filter(v => v.pct >= 0)
                    .map(v => ({ variant_id: v.id, discount_percentage: v.pct }));

                promises.push(
                    fetch(`/admin/discounts/${productId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            discount_percentage: parseFloat(data.pct) || 0,
                            starts_at: data.startsAt || null,
                            ends_at: data.endsAt || null,
                            is_active: data.active ? 1 : 0,
                            variant_discounts: variantDiscounts
                        })
                    }).then(r => r.json())
                );
            });

            try {
                const results = await Promise.all(promises);
                const allOk = results.every(r => r.success);
                if (allOk) {
                    showToast('Semua diskon berhasil disimpan! ✓');
                    _discountState.clearDirty();
                    this.hasDirty = false;
                    setTimeout(() => location.reload(), 1500);
                } else {
                    alert('Beberapa diskon gagal disimpan. Periksa konsol browser.');
                }
            } catch (err) {
                alert('Terjadi kesalahan jaringan: ' + err.message);
            } finally {
                this.saving = false;
            }
        }
    };
}

// ── Toast helper ────────────────────────────────────────────────────────────
function showToast(msg) {
    const el = document.getElementById('discount-toast');
    const span = document.getElementById('discount-toast-msg');
    if (!el || !span) return;
    span.textContent = msg;
    el.classList.remove('hidden');
    el.classList.add('flex');
    clearTimeout(el._t);
    el._t = setTimeout(() => { el.classList.add('hidden'); el.classList.remove('flex'); }, 3500);
}
</script>
@endsection
