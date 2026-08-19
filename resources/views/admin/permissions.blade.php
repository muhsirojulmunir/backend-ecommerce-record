@extends('layouts.app')

@section('title', 'Kelola Permission')
@section('page_title', 'Kelola Permission')
@section('page_subtitle', 'Tentukan hak akses apa saja yang dimiliki setiap role.')

@section('content')
@php
    // Matriks awal: { roleId: [namaPermission, ...] }
    $initialMatrix = $matrix->mapWithKeys(fn ($perms, $roleId) => [(string) $roleId => $perms]);
    $lockedRoleIds = $roles->where('name', 'super_admin')->pluck('id')->map(fn ($i) => (string) $i)->values();
@endphp

<div class="space-y-6"
     x-data="{
        matrix: @js($initialMatrix),
        original: @js($initialMatrix),
        locked: @js($lockedRoleIds),
        showAdd: false,
        showEdit: false,
        editForm: { id: null, name: '' },

        isLocked(roleId) { return this.locked.includes(String(roleId)); },
        has(roleId, perm) { return (this.matrix[roleId] || []).includes(perm); },
        toggle(roleId, perm) {
            if (this.isLocked(roleId)) return;
            const list = this.matrix[roleId] || (this.matrix[roleId] = []);
            const i = list.indexOf(perm);
            i === -1 ? list.push(perm) : list.splice(i, 1);
        },
        toggleRow(perm, roleIds) {
            const targets = roleIds.filter(id => !this.isLocked(id));
            const allOn = targets.every(id => this.has(id, perm));
            targets.forEach(id => {
                const list = this.matrix[id] || (this.matrix[id] = []);
                const i = list.indexOf(perm);
                if (allOn && i !== -1) list.splice(i, 1);
                if (!allOn && i === -1) list.push(perm);
            });
        },
        roleCount(roleId) { return (this.matrix[roleId] || []).length; },
        get dirty() {
            return JSON.stringify(this.matrix) !== JSON.stringify(this.original);
        },
        reset() { this.matrix = JSON.parse(JSON.stringify(this.original)); },
        openEdit(id, name) { this.editForm = { id, name }; this.showEdit = true; }
     }">

    {{-- ── Statistik ── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-key"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400">Total Permission</p>
                <h3 class="text-xl font-black text-gray-800 mt-0.5">{{ $stats['total'] }}</h3>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400">Buatan Sendiri</p>
                <h3 class="text-xl font-black text-gray-800 mt-0.5">{{ $stats['custom'] }}</h3>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-link-slash"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400">Belum Dipakai</p>
                <h3 class="text-xl font-black text-gray-800 mt-0.5">{{ $stats['unused'] }}</h3>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400">Jumlah Role</p>
                <h3 class="text-xl font-black text-gray-800 mt-0.5">{{ $stats['roles'] }}</h3>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="flash-alert p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm shadow-sm">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ── Header aksi ── --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="p-3.5 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-xs flex items-start gap-2.5 flex-1">
            <i class="fa-solid fa-circle-info text-blue-500 mt-0.5"></i>
            <span class="leading-relaxed">
                Klik kotak pada matriks untuk memberi atau mencabut hak akses, lalu tekan <strong>Simpan Matriks</strong>.
                Kolom <strong>super_admin</strong> dikunci penuh agar akses tidak bisa terkunci dari dalam.
            </span>
        </div>
        <button @click="showAdd = true"
            class="inline-flex items-center gap-2 bg-orange-600 text-white text-xs font-bold px-5 py-2.5 rounded-xl shadow hover:bg-orange-700 transition shrink-0">
            <i class="fa-solid fa-plus"></i>
            Buat Permission Baru
        </button>
    </div>

    {{-- ── Matriks Role × Permission ── --}}
    <form action="{{ route('admin.permissions.matrix') }}" method="POST">
        @csrf

        {{-- Nilai matriks dikirim sebagai input tersembunyi supaya kolom terkunci tetap konsisten --}}
        <template x-for="(perms, roleId) in matrix" :key="roleId">
            <template x-for="perm in perms" :key="roleId + '-' + perm">
                <input type="hidden" :name="'matrix[' + roleId + '][]'" :value="perm">
            </template>
        </template>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div>
                    <h4 class="text-sm font-black text-gray-800 uppercase tracking-wide">Matriks Hak Akses</h4>
                    <p class="text-[11px] text-gray-400 mt-0.5">Baris = permission, kolom = role.</p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <span x-show="dirty" x-cloak
                          class="text-[10px] font-black uppercase px-2.5 py-1 rounded-lg bg-amber-100 text-amber-700">
                        <i class="fa-solid fa-pen mr-1"></i>Ada perubahan belum disimpan
                    </span>
                    <button type="button" @click="reset()" x-show="dirty" x-cloak
                            class="text-xs font-bold px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                        Batalkan
                    </button>
                    <button type="submit"
                            class="text-xs font-bold px-5 py-2 rounded-xl text-white shadow transition"
                            :class="dirty ? 'bg-orange-600 hover:bg-orange-700' : 'bg-gray-300 cursor-not-allowed'"
                            :disabled="!dirty">
                        <i class="fa-solid fa-floppy-disk mr-1.5"></i>Simpan Matriks
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3.5 text-xs font-bold text-gray-400 uppercase sticky left-0 bg-gray-50 z-10 min-w-[280px]">Permission</th>
                            @foreach($roles as $role)
                                <th class="px-3 py-3.5 text-center min-w-[110px]">
                                    <p class="text-xs font-black text-gray-700">{{ $role->name }}</p>
                                    <p class="text-[10px] text-gray-400 font-semibold mt-0.5">
                                        <span x-text="roleCount({{ $role->id }})"></span> akses
                                    </p>
                                    @if($role->name === 'super_admin')
                                        <span class="inline-block mt-1 text-[9px] font-black uppercase px-1.5 py-0.5 rounded bg-amber-100 text-amber-700">
                                            <i class="fa-solid fa-lock"></i> Terkunci
                                        </span>
                                    @endif
                                </th>
                            @endforeach
                            <th class="px-4 py-3.5 text-center text-xs font-bold text-gray-400 uppercase">Semua</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @foreach($permissionGroups as $groupName => $items)
                            {{-- Baris pemisah grup --}}
                            <tr class="bg-slate-50/70">
                                <td colspan="{{ $roles->count() + 2 }}" class="px-6 py-2">
                                    <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest">{{ $groupName }}</span>
                                </td>
                            </tr>

                            @foreach($items as $p)
                                @php $permModel = $permissions->firstWhere('name', $p['name']); @endphp
                                <tr class="hover:bg-orange-50/30 transition">
                                    {{-- Nama permission --}}
                                    <td class="px-6 py-3.5 sticky left-0 bg-white hover:bg-orange-50/30 z-10">
                                        <div class="flex items-start gap-2.5">
                                            <i class="fa-solid {{ $p['icon'] }} text-gray-300 text-xs mt-1 w-4 text-center shrink-0"></i>
                                            <div class="min-w-0">
                                                <p class="text-xs font-bold text-gray-800 flex items-center gap-1.5 flex-wrap">
                                                    {{ $p['label'] }}
                                                    @if($p['is_custom'])
                                                        <span class="text-[9px] font-black uppercase px-1.5 py-0.5 rounded bg-purple-100 text-purple-700">Kustom</span>
                                                    @endif
                                                    @if($permModel && $permModel->roles_count === 0)
                                                        <span class="text-[9px] font-black uppercase px-1.5 py-0.5 rounded bg-amber-100 text-amber-700">Belum dipakai</span>
                                                    @endif
                                                </p>
                                                <p class="text-[10px] text-gray-400 mt-0.5 leading-relaxed">{{ $p['description'] }}</p>
                                                <code class="text-[9px] text-gray-300">{{ $p['name'] }}</code>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Kotak centang per role --}}
                                    @foreach($roles as $role)
                                        <td class="px-3 py-3.5 text-center">
                                            <button type="button"
                                                    @click="toggle({{ $role->id }}, '{{ $p['name'] }}')"
                                                    :disabled="isLocked({{ $role->id }})"
                                                    class="w-8 h-8 rounded-lg border-2 transition-all duration-150 inline-flex items-center justify-center"
                                                    :class="has({{ $role->id }}, '{{ $p['name'] }}')
                                                        ? (isLocked({{ $role->id }})
                                                            ? 'bg-amber-400 border-amber-400 text-white cursor-not-allowed'
                                                            : 'bg-emerald-500 border-emerald-500 text-white hover:bg-emerald-600')
                                                        : (isLocked({{ $role->id }})
                                                            ? 'bg-gray-100 border-gray-200 text-gray-300 cursor-not-allowed'
                                                            : 'bg-white border-gray-200 text-transparent hover:border-emerald-400 hover:bg-emerald-50')">
                                                <i class="fa-solid fa-check text-xs"></i>
                                            </button>
                                        </td>
                                    @endforeach

                                    {{-- Aksi baris --}}
                                    <td class="px-4 py-3.5 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <button type="button" @click="toggleRow('{{ $p['name'] }}', @js($roles->pluck('id')->all()))"
                                                    class="text-[10px] font-bold px-2.5 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600 transition"
                                                    title="Aktifkan / matikan untuk semua role">
                                                <i class="fa-solid fa-arrows-left-right-to-line"></i>
                                            </button>

                                            @if($p['is_custom'] && $permModel)
                                                <button type="button" @click="openEdit({{ $permModel->id }}, '{{ $p['name'] }}')"
                                                        class="text-[10px] font-bold px-2.5 py-1.5 rounded-lg border border-blue-200 text-blue-600 hover:bg-blue-50 transition"
                                                        title="Ganti nama">
                                                    <i class="fa-solid fa-pen"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </form>

    {{-- ── Hapus permission kustom ── --}}
    @php $customPerms = $permissions->filter(fn ($p) => \App\Support\PermissionCatalog::describe($p)['is_custom']); @endphp
    @if($customPerms->isNotEmpty())
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h4 class="text-sm font-black text-gray-800 uppercase tracking-wide mb-1">Permission Buatan Sendiri</h4>
            <p class="text-[11px] text-gray-400 mb-4">Hanya permission kustom yang bisa diganti nama atau dihapus — yang bawaan dipakai langsung di kode aplikasi.</p>

            <div class="flex flex-wrap gap-2">
                @foreach($customPerms as $perm)
                    <div class="inline-flex items-center gap-2 bg-purple-50 border border-purple-200 rounded-xl pl-3 pr-1.5 py-1.5">
                        <span class="text-xs font-bold text-purple-800">{{ $perm->name }}</span>
                        <span class="text-[10px] text-purple-500 font-semibold">{{ $perm->roles_count }} role</span>
                        <form action="{{ route('admin.permissions.destroy', $perm->id) }}" method="POST"
                              onsubmit="return confirm('Hapus permission &quot;{{ $perm->name }}&quot;? Role yang memilikinya akan kehilangan akses ini.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-6 h-6 rounded-lg text-purple-400 hover:bg-purple-200 hover:text-purple-700 transition">
                                <i class="fa-solid fa-xmark text-xs"></i>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ════════════════════════════════ --}}
    {{-- MODAL: Buat Permission --}}
    {{-- ════════════════════════════════ --}}
    <div x-show="showAdd" x-transition style="display:none;"
         class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg" @click.away="showAdd = false">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h2 class="font-black text-gray-800 text-sm uppercase tracking-wide">Buat Permission Baru</h2>
                <button @click="showAdd = false" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <form action="{{ route('admin.permissions.store') }}" method="POST">
                @csrf
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1.5">Nama Permission <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required
                               class="w-full border border-gray-200 rounded-xl py-2.5 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500"
                               placeholder="contoh: manage returns">
                        <p class="text-[10px] text-gray-400 mt-1">
                            Huruf kecil, angka, spasi, dan tanda hubung. Ikuti pola yang sudah ada:
                            <code class="bg-gray-100 px-1 rounded">manage ...</code> atau <code class="bg-gray-100 px-1 rounded">view ...</code>
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-2">Langsung Berikan ke Role</label>
                        <div class="space-y-1.5 max-h-48 overflow-y-auto border border-gray-100 rounded-xl p-3">
                            @foreach($roles as $role)
                                <label class="flex items-center gap-3 px-2 py-1.5 rounded-lg hover:bg-gray-50 cursor-pointer">
                                    <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                           @checked($role->name === 'super_admin') @disabled($role->name === 'super_admin')
                                           class="w-4 h-4 accent-orange-600 rounded">
                                    <span class="text-xs font-bold text-gray-700">{{ $role->name }}</span>
                                    @if($role->name === 'super_admin')
                                        <span class="text-[9px] text-gray-400 font-semibold">(otomatis)</span>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-[11px] flex items-start gap-2">
                        <i class="fa-solid fa-lightbulb mt-0.5"></i>
                        <span>Permission baru belum otomatis mengunci halaman mana pun. Kamu masih perlu memakainya di kode lewat <code class="bg-amber-100 px-1 rounded">@@can('nama permission')</code> atau middleware rute.</span>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3 rounded-b-3xl">
                    <button type="button" @click="showAdd = false"
                        class="text-xs font-bold px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 transition">Batal</button>
                    <button type="submit"
                        class="text-xs font-bold px-6 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white shadow transition">
                        <i class="fa-solid fa-floppy-disk mr-1.5"></i>Buat Permission
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ════════════════════════════════ --}}
    {{-- MODAL: Ganti Nama Permission --}}
    {{-- ════════════════════════════════ --}}
    <div x-show="showEdit" x-transition style="display:none;"
         class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md" @click.away="showEdit = false">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h2 class="font-black text-gray-800 text-sm uppercase tracking-wide">Ganti Nama Permission</h2>
                <button @click="showEdit = false" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <form :action="'{{ url('admin/permissions') }}/' + editForm.id" method="POST">
                @csrf @method('PUT')
                <div class="px-6 py-5">
                    <label class="block text-xs font-bold text-gray-600 mb-1.5">Nama Permission <span class="text-red-500">*</span></label>
                    <input type="text" name="name" x-model="editForm.name" required
                           class="w-full border border-gray-200 rounded-xl py-2.5 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500">
                    <p class="text-[10px] text-amber-600 font-semibold mt-2">
                        <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                        Kalau permission ini sudah dipakai di kode, jangan lupa perbarui pemanggilannya juga.
                    </p>
                </div>

                <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3 rounded-b-3xl">
                    <button type="button" @click="showEdit = false"
                        class="text-xs font-bold px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 transition">Batal</button>
                    <button type="submit"
                        class="text-xs font-bold px-6 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white shadow transition">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
