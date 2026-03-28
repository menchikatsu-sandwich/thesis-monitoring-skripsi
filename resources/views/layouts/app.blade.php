<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Skripsi Monitor')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.3); }
        .bento-card { transition: all 0.3s ease; }
        .bento-card:hover { transform: translateY(-4px); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased selection:bg-indigo-500 selection:text-white">

    <!-- Navbar -->
    <nav class="fixed w-full z-50 glass border-b border-slate-200/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center text-white font-bold">S</div>
                    <span class="font-semibold text-lg tracking-tight text-slate-900">Skripsi<span class="text-indigo-600">Monitor</span></span>
                </div>
                
                <div class="flex items-center gap-4" x-data="{ open: false }">
                    <div class="hidden md:flex flex-col text-right">
                        <span class="text-sm font-medium">{{ Auth::user()->name }}</span>
                        <span class="text-xs text-slate-500 uppercase">{{ Auth::user()->role }}</span>
                    </div>
                    
                    <button @click="open = !open" class="relative z-10 p-2 rounded-full hover:bg-slate-100 transition">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=random" class="w-8 h-8 rounded-full border border-slate-200">
                    </button>

                    <!-- Dropdown -->
                    <div x-show="open" @click.away="open = false" 
                         class="absolute right-4 top-16 w-48 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 overflow-hidden origin-top-right transition-all duration-200"
                         style="display: none;">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 font-medium">Sign out</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="pt-24 pb-12 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
        @yield('content')
    </main>

</body>
</html>