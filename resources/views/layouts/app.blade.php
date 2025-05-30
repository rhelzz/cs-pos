<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'POS System') }} - @yield('title')</title>

    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js for interactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style type="text/tailwindcss">
        @layer utilities {
            .content-container {
                @apply p-4 sm:ml-64 pt-20 pb-24 min-h-screen bg-gray-50;
            }
            .mobile-bottombar {
                @apply fixed bottom-0 left-0 z-40 w-full h-16 bg-white border-t border-gray-200 sm:hidden;
            }
            .sidebar {
                @apply fixed top-0 left-0 z-40 w-64 h-screen pt-20 transition-transform duration-300 bg-white border-r border-gray-200 overflow-y-auto;
            }
            .navbar {
                @apply fixed top-0 left-0 right-0 z-50 bg-white border-b border-gray-200 px-4 py-2.5 h-16 flex items-center shadow-sm;
            }
            .sidebar-bg {
                @apply fixed inset-0 z-30 bg-black bg-opacity-40 sm:hidden;
            }
            .active-link {
                @apply bg-blue-50 text-blue-700 font-semibold;
            }
        }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50">
<div x-data="{ sidebarOpen: false }">
    <!-- Sidebar Overlay for mobile -->
    <div x-show="sidebarOpen"
         @click="sidebarOpen = false"
         class="sidebar-bg"
         x-transition.opacity
         style="display: none"></div>

    <!-- Navbar -->
    <nav class="navbar">
        <button @click="sidebarOpen = !sidebarOpen"
                class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none"
                aria-label="Toggle sidebar">
            <i class="fas fa-bars text-xl"></i>
        </button>
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 ml-2">
            <i class="fas fa-cash-register text-2xl text-blue-600"></i>
            <span class="text-xl font-bold text-gray-800">POS System</span>
        </a>
        <div class="ml-auto flex items-center">
            <div x-data="{ userDropdown: false }" class="relative">
                <button @click="userDropdown = !userDropdown"
                        @keydown.escape.window="userDropdown = false"
                        @click.away="userDropdown = false"
                        class="flex items-center space-x-2 text-sm rounded-full px-3 py-1 hover:bg-gray-100 focus:outline-none"
                        aria-label="User menu">
                    <i class="fas fa-user-circle text-2xl text-gray-600"></i>
                    <span class="hidden sm:inline text-gray-800">{{ Auth::user()->name ?? 'User' }}</span>
                    <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                </button>
                <div x-show="userDropdown"
                     x-transition
                     class="absolute right-0 z-20 mt-2 w-48 rounded-md shadow-lg bg-white py-2 border border-gray-100"
                     style="display: none;">
                    <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-user mr-2"></i> Profil
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="sidebar sm:translate-x-0 sm:block"
           x-transition:enter="transition-transform duration-200"
           x-transition:leave="transition-transform duration-200"
           x-cloak>
        <div class="h-full px-3 py-4">
            <ul class="space-y-2 font-medium">
                <li>
                    <a href="{{ route('dashboard') }}"
                       class="flex items-center p-2 rounded-lg hover:bg-blue-50 {{ request()->routeIs('dashboard') ? 'active-link' : 'text-gray-700' }}">
                        <i class="fas fa-tachometer-alt w-5 h-5"></i>
                        <span class="ml-3">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('transactions.index') }}"
                       class="flex items-center p-2 rounded-lg hover:bg-blue-50 {{ request()->routeIs('transactions.*') ? 'active-link' : 'text-gray-700' }}">
                        <i class="fas fa-cash-register w-5 h-5"></i>
                        <span class="ml-3">Kasir</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('products.index') }}"
                       class="flex items-center p-2 rounded-lg hover:bg-blue-50 {{ request()->routeIs('products.*') ? 'active-link' : 'text-gray-700' }}">
                        <i class="fas fa-box w-5 h-5"></i>
                        <span class="ml-3">Produk</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('categories.index') }}"
                       class="flex items-center p-2 rounded-lg hover:bg-blue-50 {{ request()->routeIs('categories.*') ? 'active-link' : 'text-gray-700' }}">
                        <i class="fas fa-tag w-5 h-5"></i>
                        <span class="ml-3">Kategori</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('ingredients.index') }}"
                       class="flex items-center p-2 rounded-lg hover:bg-blue-50 {{ request()->routeIs('ingredients.*') ? 'active-link' : 'text-gray-700' }}">
                        <i class="fas fa-mortar-pestle w-5 h-5"></i>
                        <span class="ml-3">Bahan</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('expenses.index') }}"
                       class="flex items-center p-2 rounded-lg hover:bg-blue-50 {{ request()->routeIs('expenses.*') ? 'active-link' : 'text-gray-700' }}">
                        <i class="fas fa-money-bill-wave w-5 h-5"></i>
                        <span class="ml-3">Pengeluaran</span>
                    </a>
                </li>
                <li x-data="{ open: {{ request()->routeIs('reports.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                            class="flex items-center w-full p-2 rounded-lg hover:bg-blue-50 text-gray-700 focus:outline-none">
                        <i class="fas fa-chart-bar w-5 h-5"></i>
                        <span class="ml-3 flex-1 text-left">Laporan</span>
                        <i class="fas fa-chevron-down ml-auto w-3 h-3 transition-transform"
                           :class="{ 'rotate-180': open }"></i>
                    </button>
                    <ul x-show="open" class="pl-8 mt-1 space-y-1" x-transition>
                        <li>
                            <a href="{{ route('reports.best-selling-products') }}"
                               class="block py-1 px-2 rounded hover:bg-blue-50 {{ request()->routeIs('reports.best-selling-products') ? 'active-link' : 'text-gray-700' }}">
                                Produk Terlaris
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('reports.busiest-hours') }}"
                               class="block py-1 px-2 rounded hover:bg-blue-50 {{ request()->routeIs('reports.busiest-hours') ? 'active-link' : 'text-gray-700' }}">
                                Jam Teramai
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('reports.daily-sales') }}"
                               class="block py-1 px-2 rounded hover:bg-blue-50 {{ request()->routeIs('reports.daily-sales') ? 'active-link' : 'text-gray-700' }}">
                                Penjualan Harian
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('reports.daily-profit') }}"
                               class="block py-1 px-2 rounded hover:bg-blue-50 {{ request()->routeIs('reports.daily-profit') ? 'active-link' : 'text-gray-700' }}">
                                Profit Harian
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
            <div class="mt-8 border-t border-gray-200 pt-4">
                <a href="{{ route('profile') }}" class="flex items-center p-2 rounded-lg hover:bg-blue-50 {{ request()->routeIs('profile') ? 'active-link' : 'text-gray-700' }}">
                    <i class="fas fa-user-circle w-5 h-5"></i>
                    <span class="ml-3">Profil</span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center w-full p-2 mt-2 rounded-lg text-left hover:bg-blue-50 text-gray-700">
                        <i class="fas fa-sign-out-alt w-5 h-5"></i>
                        <span class="ml-3">Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="content-container">
        @yield('content')
    </div>

    <!-- Mobile Bottom Navigation -->
    <div class="mobile-bottombar">
        <div class="grid h-full grid-cols-5 mx-auto">
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center text-center py-2 {{ request()->routeIs('dashboard') ? 'text-blue-600' : 'text-gray-500' }}">
                <i class="fas fa-tachometer-alt w-6 h-6 mb-1"></i>
                <span class="text-xs">Dashboard</span>
            </a>
            <a href="{{ route('transactions.index') }}" class="flex flex-col items-center justify-center text-center py-2 {{ request()->routeIs('transactions.*') ? 'text-blue-600' : 'text-gray-500' }}">
                <i class="fas fa-cash-register w-6 h-6 mb-1"></i>
                <span class="text-xs">Kasir</span>
            </a>
            <a href="{{ route('products.index') }}" class="flex flex-col items-center justify-center text-center py-2 {{ request()->routeIs('products.*') ? 'text-blue-600' : 'text-gray-500' }}">
                <i class="fas fa-box w-6 h-6 mb-1"></i>
                <span class="text-xs">Produk</span>
            </a>
            <a href="{{ route('expenses.index') }}" class="flex flex-col items-center justify-center text-center py-2 {{ request()->routeIs('expenses.*') ? 'text-blue-600' : 'text-gray-500' }}">
                <i class="fas fa-money-bill-wave w-6 h-6 mb-1"></i>
                <span class="text-xs">Pengeluaran</span>
            </a>
            <div x-data="{ moreOpen: false }" class="relative">
                <button @click="moreOpen = !moreOpen" type="button" class="flex flex-col items-center justify-center w-full text-center py-2">
                    <i class="fas fa-ellipsis-h w-6 h-6 mb-1 text-gray-500"></i>
                    <span class="text-xs text-gray-500">Lainnya</span>
                </button>
                <div x-show="moreOpen"
                     x-transition
                     @click.away="moreOpen = false"
                     class="absolute bottom-16 right-0 z-10 w-44 bg-white rounded-md shadow-lg">
                    <div class="py-2">
                        <a href="{{ route('categories.index') }}" class="flex px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-tag w-5 h-5 mr-3 text-gray-500"></i>
                            <span>Kategori</span>
                        </a>
                        <a href="{{ route('ingredients.index') }}" class="flex px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-mortar-pestle w-5 h-5 mr-3 text-gray-500"></i>
                            <span>Bahan</span>
                        </a>
                        <a href="{{ route('reports.best-selling-products') }}" class="flex px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-chart-bar w-5 h-5 mr-3 text-gray-500"></i>
                            <span>Laporan</span>
                        </a>
                        <a href="{{ route('profile') }}" class="flex px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-user w-5 h-5 mr-3 text-gray-500"></i>
                            <span>Profile</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full">
                                <i class="fas fa-sign-out-alt w-5 h-5 mr-3 text-gray-500"></i>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stack('scripts')
</body>
</html>