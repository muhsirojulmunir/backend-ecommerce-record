@extends('layouts.app')

@section('title', 'Pengguna Role')
@section('page_title', 'Pengguna Role: ' . $role->name)
@section('page_subtitle', $users->count() . ' pengguna memegang role ini.')

@section('content')
<div class="space-y-6">

    <a href="{{ route('admin.roles') }}"
       class="inline-flex items-center gap-2 text-xs font-bold text-gray-500 hover:text-gray-800 transition">
        <i class="fa-solid fa-arrow-left"></i>
        Kembali ke Kelola Role
    </a>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center">
                    <i class="fa-solid fa-shield-halved"></i>
                </span>
                <div>
                    <h4 class="text-sm font-black text-gray-800">{{ $role->name }}</h4>
                    <p class="text-[11px] text-gray-400">{{ $users->count() }} pengguna</p>
                </div>
            </div>
        </div>

        @if($users->isEmpty())
            <div class="p-16 text-center">
                <i class="fa-regular fa-user text-4xl text-gray-300 mb-4 block"></i>
                <h3 class="text-sm font-bold text-gray-500">Belum ada pengguna dengan role ini</h3>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-500">
                    <thead class="text-xs text-gray-400 uppercase bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3.5 font-bold">Pengguna</th>
                            <th class="px-4 py-3.5 font-bold">Email</th>
                            <th class="px-4 py-3.5 font-bold">Kolom Role</th>
                            <th class="px-6 py-3.5 font-bold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($users as $user)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-orange-500 to-rose-500 flex items-center justify-center font-bold text-white text-xs shrink-0">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <p class="text-xs font-bold text-gray-800">{{ $user->name }}</p>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-xs">{{ $user->email }}</td>
                                <td class="px-4 py-4">
                                    <span class="px-2.5 py-1 text-[10px] font-black rounded-full bg-gray-100 text-gray-600 uppercase">{{ $user->role }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if($user->role === 'customer')
                                        @can('manage customers')
                                            <a href="{{ route('admin.customers.show', $user->id) }}"
                                               class="text-xs font-bold px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                                                Detail
                                            </a>
                                        @endcan
                                    @else
                                        <span class="text-[10px] text-gray-300 font-semibold">Akun internal</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
