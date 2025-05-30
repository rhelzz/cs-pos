<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'POS System') }} - @yield('title')</title>
    
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Tambahan Alpine.js untuk interaktivitas -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Font Awesome untuk ikon -->
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
                @apply fixed top-0 left-0 z-40 w-64 h-screen pt-20 bg-white border-r border-gray-200 hidden sm:block;
            }
            
            .navbar {
                @apply fixed top-0 left-0 right-0 z-50 bg-white border-b border-gray-200 px-4 py-2.5 h-16;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-50">
    <!-- Navbar Atas -->
    <nav class="navbar">
        <div class="flex flex-wrap justify-between items-center mx-auto">
            <div class="flex items-center">
                <button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar" aria-controls="logo-sidebar" type="button" class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200">
                    <span class="sr-only">Buka sidebar</span>
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 rtl:space-x-reverse ml-2 sm:ml-0">
                    <span class="self-center text-xl font-semibold whitespace-nowrap">POS System</span>
                </a>
            </div>
            <div class="flex items-center">
                <div x-data="{ userDropdown: false }" class="relative ml-3">
                    <div>
                        <button type="button" class="flex text-sm rounded-full focus:ring-4 focus:ring-gray-300" id="user-menu-button" 
                                @click="userDropdown = !userDropdown" 
                                @keydown.escape.window="userDropdown = false" 
                                @click.away="userDropdown = false" 
                                aria-expanded="false" aria-haspopup="true">
                            <span class="sr-only">Buka menu user</span>
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-user-circle text-2xl text-gray-500"></i>
                                <div class="hidden sm:block text-left">
                                    <div class="text-gray-800">{{ Auth::user()->name ?? 'User' }}</div>
                                    <div class="text-sm text-gray-500">{{ Auth::user()->role ?? 'Role' }}</div>
                                </div>
                            </div>
                        </button>
                    </div>
                    <div x-show="userDropdown" 
                         x-transition:enter="transition ease-out duration-100" 
                         x-transition:enter-start="transform opacity-0 scale-95" 
                         x-transition:enter-end="transform opacity-100 scale-100" 
                         x-transition:leave="transition ease-in duration-75" 
                         x-transition:leave-start="transform opacity-100 scale-100" 
                         x-transition:leave-end="transform opacity-0 scale-95" 
                         class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" 
                         role="menu" 
                         aria-orientation="vertical" 
                         aria-labelledby="user-menu-button" 
                         tabindex="-1"
                         style="display: none;">
                        
                        <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Profile</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <aside id="logo-sidebar" class="sidebar transition-transform -translate-x-full sm:translate-x-0" aria-label="Sidebar">
        <div class="h-full px-3 py-4 overflow-y-auto">
            <ul class="space-y-2 font-medium">
                <li>
                    <a href="{{ route('dashboard') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 {{ request()->routeIs('dashboard') ? 'bg-gray-100' : '' }}">
                        <i class="fas fa-tachometer-alt w-5 h-5 text-gray-500 transition duration-75"></i>
                        <span class="ml-3">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('transactions.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 {{ request()->routeIs('transactions.*') ? 'bg-gray-100' : '' }}">
                        <i class="fas fa-cash-register w-5 h-5 text-gray-500 transition duration-75"></i>
                        <span class="ml-3">Kasir</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('products.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 {{ request()->routeIs('products.*') ? 'bg-gray-100' : '' }}">
                        <i class="fas fa-box w-5 h-5 text-gray-500 transition duration-75"></i>
                        <span class="ml-3">Produk</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('categories.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 {{ request()->routeIs('categories.*') ? 'bg-gray-100' : '' }}">
                        <i class="fas fa-tag w-5 h-5 text-gray-500 transition duration-75"></i>
                        <span class="ml-3">Kategori</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('ingredients.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 {{ request()->routeIs('ingredients.*') ? 'bg-gray-100' : '' }}">
                        <i class="fas fa-mortar-pestle w-5 h-5 text-gray-500 transition duration-75"></i>
                        <span class="ml-3">Bahan</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('expenses.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 {{ request()->routeIs('expenses.*') ? 'bg-gray-100' : '' }}">
                        <i class="fas fa-money-bill-wave w-5 h-5 text-gray-500 transition duration-75"></i>
                        <span class="ml-3">Pengeluaran</span>
                    </a>
                </li>
                <li>
                    <div x-data="{ reportsOpen: false }" class="relative">
                        <button type="button" class="flex items-center w-full p-2 text-gray-900 rounded-lg hover:bg-gray-100" 
                                @click="reportsOpen = !reportsOpen">
                            <i class="fas fa-chart-bar w-5 h-5 text-gray-500 transition duration-75"></i>
                            <span class="flex-1 ml-3 text-left whitespace-nowrap">Laporan</span>
                            <i class="fas fa-chevron-down w-4 h-4" :class="{'transform rotate-180': reportsOpen}"></i>
                        </button>
                        <ul x-show="reportsOpen" 
                            x-transition 
                            class="py-2 space-y-2 pl-4">
                            <li>
                                <a href="{{ route('reports.best-selling-products') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 pl-5">
                                    <span class="ml-3">Produk Terlaris</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('reports.busiest-hours') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 pl-5">
                                    <span class="ml-3">Jam Teramai</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('reports.daily-sales') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 pl-5">
                                    <span class="ml-3">Penjualan Harian</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('reports.daily-profit') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 pl-5">
                                    <span class="ml-3">Profit Harian</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>
    </aside>

    <!-- Konten Utama -->
    <div class="content-container">
        @yield('content')
    </div>

    <!-- Mobile Bottom Navigation -->
    <div class="mobile-bottombar">
        <div class="grid h-full grid-cols-5 mx-auto">
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center text-center py-2">
                <i class="fas fa-tachometer-alt w-6 h-6 mb-1 {{ request()->routeIs('dashboard') ? 'text-blue-600' : 'text-gray-500' }}"></i>
                <span class="text-xs {{ request()->routeIs('dashboard') ? 'text-blue-600' : 'text-gray-500' }}">Dashboard</span>
            </a>
            <a href="{{ route('transactions.index') }}" class="flex flex-col items-center justify-center text-center py-2">
                <i class="fas fa-cash-register w-6 h-6 mb-1 {{ request()->routeIs('transactions.*') ? 'text-blue-600' : 'text-gray-500' }}"></i>
                <span class="text-xs {{ request()->routeIs('transactions.*') ? 'text-blue-600' : 'text-gray-500' }}">Kasir</span>
            </a>
            <a href="{{ route('products.index') }}" class="flex flex-col items-center justify-center text-center py-2">
                <i class="fas fa-box w-6 h-6 mb-1 {{ request()->routeIs('products.*') ? 'text-blue-600' : 'text-gray-500' }}"></i>
                <span class="text-xs {{ request()->routeIs('products.*') ? 'text-blue-600' : 'text-gray-500' }}">Produk</span>
            </a>
            <a href="{{ route('expenses.index') }}" class="flex flex-col items-center justify-center text-center py-2">
                <i class="fas fa-money-bill-wave w-6 h-6 mb-1 {{ request()->routeIs('expenses.*') ? 'text-blue-600' : 'text-gray-500' }}"></i>
                <span class="text-xs {{ request()->routeIs('expenses.*') ? 'text-blue-600' : 'text-gray-500' }}">Pengeluaran</span>
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

    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarButton = document.querySelector('[data-drawer-toggle="logo-sidebar"]');
            const sidebar = document.getElementById('logo-sidebar');
            
            if (sidebarButton && sidebar) {
                sidebarButton.addEventListener('click', function() {
                    sidebar.classList.toggle('-translate-x-full');
                });
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>