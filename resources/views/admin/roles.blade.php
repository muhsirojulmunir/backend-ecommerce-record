@extends('layouts.app')

@section('title', 'Kelola Role')
@section('page_title', 'Kelola Role')
@section('page_subtitle', 'Atur kelompok pengguna dan hak akses yang mereka miliki.')

@section('content')
@php
    // Payload role untuk modal edit (dipakai Alpine)
    $rolePayload = $roles->mapWithKeys(fn ($r) => [$r->id => [
        'id'          => $r->id,
        'name'        => $r->name,
        'permissions' => $r->permissions->pluck('name')->all(),
        'isProtected' => in_array($r->name, $protectedRoles, true),
        'usersCount'  => $r->users_count,
    ]]);

    $allPermissionNames = $permissionGroups->flatten(1)->pluck('name')->all();
@endphp

<div class="space-y-6"
     x-data="{
        showAdd: false,
        showEdit: false,
        form: { id: null, name: '', permissions: [], isProtected: false, usersCount: 0 },
        allPermissions: @js($allPermissionNames),
        roles: @js($rolePayload),

        openAdd() {
            this.form = { id: null, name: '', permissions: [], isProtected: false, usersCount: 0 };
            this.showAdd = true;
        },
        openEdit(id) {
            const r = this.roles[id];
            this.form = { id: r.id, name: r.name, permissions: [...r.permissions], isProtected: r.isProtected, usersCount: r.usersCount };
            this.showEdit = true;
        },
        toggle(name) {
            const i = this.form.permissions.indexOf(name);
            i === -1 ? this.form.permissions.push(name) : this.form.permissions.splice(i, 1);
        },
        has(name) { return this.form.permissions.includes(name); },
        toggleGroup(names) {
            const allOn = names.every(n => this.has(n));
            names.forEach(n => {
                const i = this.form.permissions.indexOf(n);
                if (allOn && i !== -1) this.form.permissions.splice(i, 1);
                if (!allOn && i === -1) this.form.permissions.push(n);
            });
        },
        groupState(names) {
            const on = names.filter(n => this.has(n)).length;
            return on === 0 ? 'none' : (on === names.length ? 'all' : 'some');
        },
        selectAll() { this.form.permissions = [...this.allPermissions]; },
        clearAll() { this.form.permissions = []; }
     }">

    {{-- ── Header + Statistik ── --}}
    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3">
            <div class="inline-flex items-center gap-2 bg-white border border-gray-100 shadow-sm text-xs font-bold px-4 py-2.5 rounded-xl">
                <i class="fa-solid fa-shield-halved text-orange-500"></i>
                <span class="text-gray-700">{{ $roles->count() }} Role</span>
            </div>
            <div class="inline-flex items-center gap-2 bg-white border border-gray-100 shadow-sm text-xs font-bold px-4 py-2.5 rounded-xl">
                <i class="fa-solid fa-key text-blue-500"></i>
                <span class="text-gray-700">{{ $totalPermissions }} Hak Akses Tersedia</span>
            </div>
            <div class="inline-flex items-center gap-2 bg-white border border-gray-100 shadow-sm text-xs font-bold px-4 py-2.5 rounded-xl">
                <i class="fa-solid fa-users text-purple-500"></i>
                <span class="text-gray-700">{{ $roles->sum('users_count') }} Pengguna Terhubung</span>
            </div>
        </div>

        <button @click="openAdd()"
            class="inline-flex items-center gap-2 bg-orange-600 text-white text-xs font-bold px-5 py-2.5 rounded-xl shadow hover:bg-orange-700 transition shrink-0">
            <i class="fa-solid fa-plus"></i>
            Buat Role Baru
        </button>
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

    {{-- ── Info Role Bawaan ── --}}
    <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-xs flex items-start gap-3">
        <i class="fa-solid fa-circle-info text-blue-500 text-sm mt-0.5"></i>
        <div class="leading-relaxed">
            <strong>super_admin</strong>, <strong>admin</strong>, dan <strong>customer</strong> adalah role bawaan sistem —
            namanya dikunci dan tidak bisa dihapus karena dipakai langsung di dalam kode.
            Hak akses <strong>super_admin</strong> juga selalu lengkap, supaya tidak ada admin yang tanpa sengaja mengunci dirinya sendiri.
        </div>
    </div>

    {{-- ── Daftar Role ── --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
        @foreach($roles as $role)
            @php
                $isProtected = in_array($role->name, $protectedRoles, true);
                $owned = $role->permissions->pluck('name');
                $coverage = $totalPermissions > 0 ? round($owned->count() / $totalPermissions * 100) : 0;

                $accent = match($role->name) {
                    'super_admin' => ['from-orange-600 to-rose-500', 'fa-crown'],
                    'admin'       => ['from-blue-600 to-indigo-500', 'fa-user-tie'],
                    'customer'    => ['from-emerald-600 to-teal-500', 'fa-user'],
                    default       => ['from-slate-700 to-slate-500', 'fa-user-gear'],
                };
            @endphp

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
                {{-- Header kartu --}}
                <div class="bg-gradient-to-r {{ $accent[0] }} px-5 py-4 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-white text-base shrink-0">
                            <i class="fa-solid {{ $accent[1] }}"></i>
                        </span>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="font-black text-white text-sm truncate">{{ $role->name }}</h3>
                                @if($isProtected)
                                    <span class="shrink-0 text-[9px] font-black uppercase px-2 py-0.5 rounded-full bg-white/25 text-white">Bawaan</span>
                                @endif
                            </div>
                            <p class="text-[11px] text-white/80 mt-0.5">{{ $role->users_count }} pengguna · {{ $owned->count() }} hak akses</p>
                        </div>
                    </div>

                    {{-- Cincin persentase cakupan --}}
                    <div class="shrink-0 text-right">
                        <p class="text-lg font-black text-white leading-none">{{ $coverage }}%</p>
                        <p class="text-[9px] text-white/70 font-bold uppercase mt-0.5">Cakupan</p>
                    </div>
                </div>

                {{-- Daftar hak akses per grup --}}
                <div class="p-5 flex-1 space-y-3">
                    @foreach($permissionGroups as $groupName => $items)
                        @php
                            $groupOwned = $items->filter(fn ($p) => $owned->contains($p['name']));
                        @endphp
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-wide">{{ $groupName }}</span>
                                <span class="text-[10px] font-bold {{ $groupOwned->count() === $items->count() ? 'text-emerald-600' : ($groupOwned->isEmpty() ? 'text-gray-300' : 'text-amber-600') }}">
                                    {{ $groupOwned->count() }}/{{ $items->count() }}
                                </span>
                            </div>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($items as $p)
                                    <span class="inline-flex items-center gap-1.5 text-[10px] font-bold px-2.5 py-1 rounded-lg border
                                        {{ $owned->contains($p['name'])
                                            ? 'bg-emerald-50 border-emerald-200 text-emerald-700'
                                            : 'bg-gray-50 border-gray-100 text-gray-300 line-through' }}"
                                        title="{{ $p['description'] }}">
                                        <i class="fa-solid {{ $p['icon'] }} text-[9px]"></i>
                                        {{ $p['label'] }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    @if($owned->isEmpty())
                        <p class="text-[11px] text-amber-600 font-semibold bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                            <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                            Role ini belum punya hak akses apa pun — pengguna dengannya tidak bisa membuka menu mana pun di Seller Center.
                        </p>
                    @endif
                </div>

                {{-- Tombol aksi --}}
                <div class="px-5 py-3.5 border-t border-gray-50 bg-gray-50/50 flex flex-wrap items-center gap-2">
                    <button @click="openEdit({{ $role->id }})"
                        class="text-xs font-bold px-3.5 py-1.5 rounded-lg border border-blue-200 text-blue-600 hover:bg-blue-50 transition">
                        <i class="fa-solid fa-pen-to-square mr-1"></i>Atur Hak Akses
                    </button>

                    @if($role->users_count > 0)
                        <a href="{{ route('admin.roles.users', $role->id) }}"
                           class="text-xs font-bold px-3.5 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-100 transition">
                            <i class="fa-solid fa-users mr-1"></i>Lihat {{ $role->users_count }} Pengguna
                        </a>
                    @endif

                    @if(!$isProtected)
                        <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="ml-auto"
                              onsubmit="return confirm('Hapus role &quot;{{ $role->name }}&quot;? Tindakan ini tidak bisa dibatalkan.')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                class="text-xs font-bold px-3.5 py-1.5 rounded-lg border border-red-200 text-red-500 hover:bg-red-50 transition">
                                <i class="fa-solid fa-trash mr-1"></i>Hapus
                            </button>
                        </form>
                    @else
                        <span class="ml-auto text-[10px] text-gray-400 font-semibold">
                            <i class="fa-solid fa-lock mr-1"></i>Role bawaan
                        </span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- ════════════════════════════════ --}}
    {{-- MODAL: Buat / Edit Role --}}
    {{-- ════════════════════════════════ --}}
    <template x-if="showAdd || showEdit">
        <div class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" x-transition>
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl flex flex-col" style="max-height:90vh;"
                 @click.away="showAdd = false; showEdit = false">

                {{-- Header --}}
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center shrink-0">
                    <h2 class="font-black text-gray-800 text-sm uppercase tracking-wide"
                        x-text="showAdd ? 'Buat Role Baru' : 'Atur Hak Akses Role'"></h2>
                    <button @click="showAdd = false; showEdit = false" class="text-gray-400 hover:text-gray-600 transition">
                        <i class="fa-solid fa-xmark text-base"></i>
                    </button>
                </div>

                <form :action="showAdd ? '{{ route('admin.roles.store') }}' : '{{ url('admin/roles') }}/' + form.id" method="POST">
                    @csrf
                    <template x-if="showEdit"><input type="hidden" name="_method" value="PUT"></template>

                    <div class="px-6 py-5 space-y-5 overflow-y-auto" style="max-height:calc(90vh - 145px);">

                        {{-- Nama role --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1.5">
                                Nama Role <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" x-model="form.name" required
                                   :disabled="form.isProtected"
                                   class="w-full border border-gray-200 rounded-xl py-2.5 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 disabled:bg-gray-100 disabled:text-gray-400"
                                   placeholder="contoh: staff_gudang">
                            <p class="text-[10px] text-gray-400 mt-1" x-show="!form.isProtected">
                                Huruf kecil, angka, dan garis bawah saja. Contoh: <code class="bg-gray-100 px-1 rounded">staff_gudang</code>
                            </p>
                            <p class="text-[10px] text-amber-600 font-semibold mt-1" x-show="form.isProtected">
                                <i class="fa-solid fa-lock mr-1"></i>Nama role bawaan dikunci karena dipakai langsung di kode aplikasi.
                            </p>
                        </div>

                        {{-- Peringatan super admin --}}
                        <template x-if="form.name === 'super_admin'">
                            <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-[11px] flex items-start gap-2">
                                <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
                                <span>Role <strong>super_admin</strong> selalu diberi seluruh hak akses. Perubahan centang di bawah tidak akan berpengaruh.</span>
                            </div>
                        </template>

                        {{-- Aksi cepat --}}
                        <div class="flex items-center justify-between gap-3 pb-1">
                            <span class="text-xs font-bold text-gray-600">
                                Hak Akses
                                <span class="ml-1.5 px-2 py-0.5 rounded-full bg-orange-100 text-orange-700 text-[10px] font-black"
                                      x-text="form.permissions.length + ' dipilih'"></span>
                            </span>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="selectAll()"
                                        class="text-[10px] font-bold px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                                    Pilih Semua
                                </button>
                                <button type="button" @click="clearAll()"
                                        class="text-[10px] font-bold px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                                    Kosongkan
                                </button>
                            </div>
                        </div>

                        {{-- Matriks permission per grup --}}
                        @foreach($permissionGroups as $groupName => $items)
                            @php $groupNames = $items->pluck('name')->all(); @endphp
                            <div class="border border-gray-100 rounded-2xl overflow-hidden">
                                <div class="px-4 py-2.5 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                                    <span class="text-[10px] font-black text-gray-500 uppercase tracking-wide">{{ $groupName }}</span>
                                    <button type="button" @click="toggleGroup(@js($groupNames))"
                                            class="text-[10px] font-bold px-2.5 py-1 rounded-lg transition"
                                            :class="groupState(@js($groupNames)) === 'all'
                                                ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200'
                                                : 'bg-white border border-gray-200 text-gray-500 hover:bg-gray-100'"
                                            x-text="groupState(@js($groupNames)) === 'all' ? 'Batalkan Grup' : 'Pilih Grup'"></button>
                                </div>

                                <div class="divide-y divide-gray-50">
                                    @foreach($items as $p)
                                        <label class="flex items-start gap-3 px-4 py-3 cursor-pointer hover:bg-orange-50/40 transition">
                                            <input type="checkbox" name="permissions[]" value="{{ $p['name'] }}"
                                                   :checked="has('{{ $p['name'] }}')"
                                                   @change="toggle('{{ $p['name'] }}')"
                                                   class="w-4 h-4 accent-orange-600 rounded mt-0.5 shrink-0">
                                            <div class="min-w-0">
                                                <p class="text-xs font-bold text-gray-800">
                                                    <i class="fa-solid {{ $p['icon'] }} text-gray-400 mr-1.5 text-[10px]"></i>
                                                    {{ $p['label'] }}
                                                    @if($p['is_custom'])
                                                        <span class="ml-1 text-[9px] font-black uppercase px-1.5 py-0.5 rounded bg-purple-100 text-purple-700">Kustom</span>
                                                    @endif
                                                </p>
                                                <p class="text-[10px] text-gray-400 mt-0.5 leading-relaxed">{{ $p['description'] }}</p>
                                                <code class="text-[9px] text-gray-300">{{ $p['name'] }}</code>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between gap-3 shrink-0 bg-white rounded-b-3xl">
                        <span class="text-[10px] text-gray-400 font-semibold" x-show="showEdit && form.usersCount > 0">
                            <i class="fa-solid fa-users mr-1"></i>
                            Perubahan langsung berlaku untuk <span x-text="form.usersCount"></span> pengguna.
                        </span>
                        <div class="flex gap-3 ml-auto">
                            <button type="button" @click="showAdd = false; showEdit = false"
                                class="text-xs font-bold px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                                Batal
                            </button>
                            <button type="submit"
                                class="text-xs font-bold px-6 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white shadow transition">
                                <i class="fa-solid fa-floppy-disk mr-1.5"></i>Simpan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </template>

</div>
@endsection
