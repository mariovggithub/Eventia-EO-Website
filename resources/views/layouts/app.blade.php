<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Eventia') — EO & Vendor Marketplace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1F6B7E;
            --accent: #B88843;
            --secondary: #925E30;
        }
        body { font-family: Inter, system-ui, sans-serif; background: linear-gradient(180deg, #f8fafc 0%, #fff 100%); }
        .glass { background: rgba(255,255,255,0.7); backdrop-filter: blur(6px); }
        .card-shadow { box-shadow: 0 10px 30px rgba(2,6,23,0.08); }
        .site-hero { background-image: linear-gradient(95deg, rgba(31,107,126,0.06), rgba(184,136,64,0.02)); }
        .bg-primary { background-color: var(--primary); }
        .text-primary { color: var(--primary); }
        .bg-accent { background-color: var(--accent); }
        .text-accent { color: var(--accent); }
        .border-accent { border-color: var(--accent); }
        .bg-secondary { background-color: var(--secondary); }
        .navlink.active { font-weight: 700; color: var(--accent); }
    </style>
    @stack('styles')
</head>
<body class="antialiased">
    {{-- Header --}}
    <header class="sticky top-0 z-50 bg-white/70 glass border-b">
        <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
            <a class="flex items-center gap-3" href="{{ route('home') }}">
                <img src="{{ asset('assets/LogoS.png') }}" alt="logo" class="rounded-md shadow-sm w-10 h-15" onerror="this.style.display='none'">
                <div>
                    <div class="text-xl font-bold text-primary">Eventia</div>
                    <div class="text-xs text-gray-500">Create • Hire • Celebrate</div>
                </div>
            </a>

            <nav class="hidden md:flex items-center gap-6">
                @auth
                    @if(auth()->user()->isUser())
                        <a class="navlink text-sm {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a>
                        <a class="navlink text-sm {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">About</a>
                        <a class="navlink text-sm {{ request()->routeIs('order.*') ? 'active' : '' }}" href="{{ route('order.create') }}">Order</a>
                        <a class="navlink text-sm {{ request()->routeIs('order.my-orders') ? 'active' : '' }}" href="{{ route('order.my-orders') }}">My Orders</a>
                        <a class="navlink text-sm {{ request()->routeIs('jobs.*') ? 'active' : '' }}" href="{{ route('jobs.index') }}">Job Apply</a>
                    @elseif(auth()->user()->isEO())
                        <a class="navlink text-sm {{ request()->routeIs('eo.orders') ? 'active' : '' }}" href="{{ route('eo.orders') }}">Orders</a>
                        <a class="navlink text-sm {{ request()->routeIs('eo.hiring') ? 'active' : '' }}" href="{{ route('eo.hiring') }}">Hiring</a>
                        <a class="navlink text-sm {{ request()->routeIs('eo.profile') ? 'active' : '' }}" href="{{ route('eo.profile') }}">Profile</a>
                    @elseif(auth()->user()->isVendor())
                        <a class="navlink text-sm {{ request()->routeIs('vendor.orders') ? 'active' : '' }}" href="{{ route('vendor.orders') }}">Orders</a>
                        <a class="navlink text-sm {{ request()->routeIs('vendor.products') ? 'active' : '' }}" href="{{ route('vendor.products') }}">Products</a>
                    @endif
                @else
                    <a class="navlink text-sm {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a>
                    <a class="navlink text-sm {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">About</a>
                    <a class="navlink text-sm {{ request()->routeIs('jobs.*') ? 'active' : '' }}" href="{{ route('jobs.index') }}">Jobs</a>
                @endauth
            </nav>

            <div class="flex items-center gap-3">
                @auth
                    @if(auth()->user()->isUser())
                        <a href="{{ route('order.create') }}" class="px-4 py-2 rounded-lg text-sm font-semibold bg-accent text-white shadow-md hover:opacity-95">Buat Pesanan</a>
                    @endif
                    
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2 px-3 py-2 rounded-lg border hover:shadow-sm">
                            <div class="w-7 h-7 rounded-full bg-accent text-white flex items-center justify-center text-xs font-bold">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <span class="text-sm font-medium hidden sm:inline">{{ auth()->user()->name }}</span>
                            <svg class="w-4 h-4 ml-1 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.584l3.71-4.354a.75.75 0 111.14.976l-4.25 5a.75.75 0 01-1.14 0l-4.25-5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg p-2" style="display: none;">
                            <div class="px-3 py-2 text-xs text-gray-500 border-b">
                                Role: <span class="font-semibold">{{ ucfirst(auth()->user()->role) }}</span>
                            </div>
                            @if(auth()->user()->isEO())
                            <a href="{{ route('eo.profile') }}" class="block w-full text-left text-sm px-3 py-2 rounded hover:bg-gray-50">
                                Profile EO
                            </a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left text-sm px-3 py-2 rounded hover:bg-gray-50 text-red-600">Logout</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 rounded-lg text-sm font-semibold border border-primary text-primary hover:bg-primary/10">Login</a>
                    <a href="{{ route('register') }}" class="px-4 py-2 rounded-lg text-sm font-semibold bg-accent text-white shadow-md hover:opacity-95">Register</a>
                @endauth
            </div>
        </div>
    </header>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="max-w-6xl mx-auto px-6 mt-4">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-6xl mx-auto px-6 mt-4">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                {{ session('error') }}
            </div>
        </div>
    @endif

    @if(session('info'))
        <div class="max-w-6xl mx-auto px-6 mt-4">
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative">
                {{ session('info') }}
            </div>
        </div>
    @endif

    {{-- Main Content --}}
    <main class="max-w-6xl mx-auto px-6 py-10">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="max-w-6xl mx-auto px-6 py-10 text-sm text-gray-500">
        <div class="flex items-center justify-between">
            <div class="text-primary">© 2025 Eventia — Laravel Version</div>
            <div>Built with ❤️ — Kelompok 6 A4</div>
        </div>
    </footer>

    {{-- Alpine.js for dropdowns --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('scripts')
</body>
</html>