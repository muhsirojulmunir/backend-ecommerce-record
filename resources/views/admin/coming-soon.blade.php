@extends('layouts.app')

@section('title', $menu)
@section('page_title', $menu)
@section('page_subtitle', 'Halaman ini sedang dalam pengembangan')

@section('content')
<div class="flex flex-col items-center justify-center py-24 text-center">
    <div class="w-24 h-24 rounded-2xl bg-orange-50 flex items-center justify-center mb-6 text-orange-500 shadow-inner">
        <i class="fa-solid fa-screwdriver-wrench text-4xl"></i>
    </div>
    <h2 class="text-2xl font-bold text-gray-800 mb-2">Sedang Dikembangkan</h2>
    <p class="text-gray-500 max-w-sm">
        Halaman <strong>{{ $menu }}</strong> saat ini sedang dalam proses pengembangan dan akan segera tersedia.
    </p>
    <a href="{{ url()->previous() }}" class="mt-8 inline-flex items-center gap-2 text-sm font-semibold text-orange-600 hover:text-orange-700 transition">
        <i class="fa-solid fa-arrow-left"></i>
        Kembali ke halaman sebelumnya
    </a>
</div>
@endsection
