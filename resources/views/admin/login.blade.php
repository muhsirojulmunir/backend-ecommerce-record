<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Seller Center</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-orange-50 via-slate-100 to-orange-100 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden transform transition duration-500 hover:scale-[1.01]">
        <!-- Header -->
        <div class="bg-gradient-to-r from-orange-500 to-rose-500 p-8 text-center text-white relative">
            <div class="absolute inset-0 bg-black opacity-10 pattern-grid-lg"></div>
            <div class="relative z-10">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-white/20 backdrop-blur-md mb-3 shadow-inner">
                    <i class="fa-solid fa-store text-3xl"></i>
                </div>
                <h1 class="text-2xl font-bold tracking-tight">SELLER CENTER</h1>
                <p class="text-orange-100 text-sm mt-1">Kelola toko Anda dengan efisien</p>
            </div>
        </div>

        <!-- Form Form -->
        <div class="p-8">
            <!-- Errors Alert -->
            @if ($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-50 border-l-4 border-red-500 text-red-700 text-sm flex items-start">
                    <i class="fa-solid fa-triangle-exclamation mt-0.5 mr-3 shrink-0 text-red-500"></i>
                    <div>
                        <span class="font-bold">Gagal masuk!</span>
                        <ul class="list-disc list-inside mt-1 text-xs">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form action="{{ route('admin.login.submit') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Alamat Email</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400">
                            <i class="fa-regular fa-envelope text-base"></i>
                        </span>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                            class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm placeholder-gray-400 transition"
                            placeholder="Masukkan email admin...">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Kata Sandi</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400">
                            <i class="fa-solid fa-lock text-base"></i>
                        </span>
                        <input type="password" name="password" id="password" required
                            class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm placeholder-gray-400 transition"
                            placeholder="••••••••">
                    </div>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center text-gray-600 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="rounded border-gray-300 text-orange-600 focus:ring-orange-500 mr-2">
                        Ingat saya
                    </label>
                    <a href="#" class="text-orange-600 hover:text-orange-700 font-semibold transition">Lupa password?</a>
                </div>

                <button type="submit"
                    class="w-full bg-gradient-to-r from-orange-500 to-rose-500 hover:from-orange-600 hover:to-rose-600 text-white font-bold py-3.5 px-4 rounded-xl shadow-lg shadow-orange-500/20 active:scale-[0.98] transition duration-150 flex items-center justify-center space-x-2 text-sm uppercase tracking-wider">
                    <span>Masuk Ke Panel</span>
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                </button>
            </form>
        </div>
    </div>

</body>
</html>
