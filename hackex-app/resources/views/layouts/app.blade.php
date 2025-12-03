<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'HACKEX - Scan fast. Launch safe.')</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'sky-blue': '#0EA5E9',
                        'hackex-black': '#000000',
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js for interactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @stack('styles')
</head>
<body class="bg-white text-gray-900 antialiased">
    <!-- Header -->
    <header class="bg-hackex-black text-white shadow-lg">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center space-x-2">
                    <svg class="w-8 h-8 text-sky-blue" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                    </svg>
                    <span class="text-2xl font-bold">HACKEX</span>
                </a>

                <nav class="hidden md:flex items-center space-x-6">
                    <a href="{{ route('home') }}" class="hover:text-sky-blue transition">Home</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="hover:text-sky-blue transition">Dashboard</a>
                        <a href="{{ route('scan.history') }}" class="hover:text-sky-blue transition">History</a>
                    @endauth
                    <a href="{{ route('home') }}#features" class="hover:text-sky-blue transition">Features</a>
                </nav>

                <div class="flex items-center space-x-4">
                    <a href="{{ route('home') }}" class="bg-sky-blue hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold transition">
                        Start Scan
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-hackex-black text-white mt-20">
        <div class="container mx-auto px-4 py-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4 text-sky-blue">HACKEX</h3>
                    <p class="text-gray-400">Pre-launch security scanner for web apps, APIs & MVPs.</p>
                    <p class="text-gray-400 mt-2 text-sm">Scan fast. Launch safe.</p>
                </div>

                <div>
                    <h4 class="font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="{{ route('home') }}" class="hover:text-sky-blue transition">Home</a></li>
                        <li><a href="{{ route('home') }}#features" class="hover:text-sky-blue transition">Features</a></li>
                        @auth
                            <li><a href="{{ route('dashboard') }}" class="hover:text-sky-blue transition">Dashboard</a></li>
                        @endauth
                    </ul>
                </div>

                <div>
                    <h4 class="font-semibold mb-4">Legal</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-sky-blue transition">Terms of Service</a></li>
                        <li><a href="#" class="hover:text-sky-blue transition">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-sky-blue transition">Responsible Disclosure</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400 text-sm">
                <p>&copy; {{ date('Y') }} HACKEX. All rights reserved.</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
