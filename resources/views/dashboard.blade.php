@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div x-data="{
    dailyData: null,
    isLoading: true,
    fetchData() {
        this.isLoading = true;
        fetch('{{ route('dashboard') }}')
            .then(response => response.json())
            .then(data => {
                this.dailyData = data;
                this.isLoading = false;
            })
            .catch(error => {
                console.error('Error:', error);
                this.isLoading = false;
            });
    }
}" x-init="fetchData()">
    
    <!-- Loading Indicator -->
    <div x-show="isLoading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
    </div>
    
    <div x-show="!isLoading" x-cloak>
        <h1 class="text-2xl font-semibold text-gray-800 mb-6">Dashboard</h1>
        
        <!-- Tanggal -->
        <div class="text-gray-600 mb-6">
            <template x-if="dailyData">
                <span>Data untuk tanggal: <span x-text="dailyData.date" class="font-medium"></span></span>
            </template>
        </div>
        
        <!-- Statistik -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <!-- Pendapatan Hari Ini -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                <div class="flex justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Pendapatan Hari Ini</p>
                        <template x-if="dailyData">
                            <h2 class="text-2xl font-bold text-gray-800" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(dailyData.daily_income)"></h2>
                        </template>
                    </div>
                    <div class="bg-blue-50 rounded-full w-12 h-12 flex items-center justify-center">
                        <i class="fas fa-money-bill-wave text-blue-500 text-xl"></i>
                    </div>
                </div>
                <template x-if="dailyData">
                    <div class="mt-4 text-sm text-gray-500" x-text="dailyData.transaction_count + ' transaksi'"></div>
                </template>
            </div>
            
            <!-- Pengeluaran Hari Ini -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                <div class="flex justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Pengeluaran Hari Ini</p>
                        <template x-if="dailyData">
                            <h2 class="text-2xl font-bold text-gray-800" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(dailyData.daily_expenses)"></h2>
                        </template>
                    </div>
                    <div class="bg-red-50 rounded-full w-12 h-12 flex items-center justify-center">
                        <i class="fas fa-receipt text-red-500 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <!-- Profit Kotor -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                <div class="flex justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Profit Kotor</p>
                        <template x-if="dailyData">
                            <h2 class="text-2xl font-bold text-gray-800" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(dailyData.gross_profit)"></h2>
                        </template>
                    </div>
                    <div class="bg-green-50 rounded-full w-12 h-12 flex items-center justify-center">
                        <i class="fas fa-chart-line text-green-500 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <!-- Profit Bersih -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                <div class="flex justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Profit Bersih</p>
                        <template x-if="dailyData">
                            <h2 class="text-2xl font-bold text-gray-800" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(dailyData.net_profit)"></h2>
                        </template>
                    </div>
                    <div class="bg-purple-50 rounded-full w-12 h-12 flex items-center justify-center">
                        <i class="fas fa-wallet text-purple-500 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- 5 Transaksi Terakhir -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                <h3 class="text-lg font-medium text-gray-800 mb-4">Transaksi Terakhir</h3>
                <template x-if="dailyData && dailyData.latest_transactions && dailyData.latest_transactions.length > 0">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="(transaction, index) in dailyData.latest_transactions" :key="transaction.id">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a :href="'{{ url('transactions') }}/' + transaction.id" class="text-blue-600 hover:text-blue-900">
                                                #<span x-text="transaction.id"></span>
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900" x-text="new Date(transaction.created_at).toLocaleTimeString('id-ID')"></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(transaction.total_amount)"></div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </template>
                <template x-if="!dailyData || !dailyData.latest_transactions || dailyData.latest_transactions.length === 0">
                    <div class="py-4 text-center text-gray-500">Belum ada transaksi hari ini</div>
                </template>
                <div class="mt-4 text-right">
                    <a href="{{ route('transactions.index') }}" class="text-sm text-blue-600 hover:text-blue-800">Lihat semua transaksi →</a>
                </div>
            </div>
            
            <!-- Produk dengan Stok Menipis -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                <h3 class="text-lg font-medium text-gray-800 mb-4">Stok Menipis</h3>
                <template x-if="dailyData && dailyData.low_stock_products && dailyData.low_stock_products.length > 0">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="(product, index) in dailyData.low_stock_products" :key="product.id">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a :href="'{{ url('products') }}/' + product.id" class="text-blue-600 hover:text-blue-900">
                                                <span x-text="product.name"></span>
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div x-text="product.category ? product.category.name : '-'" class="text-sm text-gray-900"></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm" :class="{'text-red-700': product.stock <= 5, 'text-yellow-700': product.stock > 5}">
                                                <span x-text="product.stock"></span>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </template>
                <template x-if="!dailyData || !dailyData.low_stock_products || dailyData.low_stock_products.length === 0">
                    <div class="py-4 text-center text-gray-500">Tidak ada produk dengan stok menipis</div>
                </template>
                <div class="mt-4 text-right">
                    <a href="{{ route('products.index') }}" class="text-sm text-blue-600 hover:text-blue-800">Kelola produk →</a>
                </div>
            </div>
            
            <!-- Produk Terlaris Hari Ini -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                <h3 class="text-lg font-medium text-gray-800 mb-4">Produk Terlaris Hari Ini</h3>
                <template x-if="dailyData && dailyData.best_selling_products && dailyData.best_selling_products.length > 0">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terjual</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="(product, index) in dailyData.best_selling_products" :key="product.id">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span x-text="product.name" class="text-sm text-gray-900"></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900" x-text="product.total_quantity"></div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </template>
                <template x-if="!dailyData || !dailyData.best_selling_products || dailyData.best_selling_products.length === 0">
                    <div class="py-4 text-center text-gray-500">Belum ada penjualan hari ini</div>
                </template>
                <div class="mt-4 text-right">
                    <a href="{{ route('reports.best-selling-products') }}" class="text-sm text-blue-600 hover:text-blue-800">Lihat laporan lengkap →</a>
                </div>
            </div>
            
            <!-- Jam Teramai Hari Ini -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                <h3 class="text-lg font-medium text-gray-800 mb-4">Jam Teramai</h3>
                <template x-if="dailyData && dailyData.busiest_hours && dailyData.busiest_hours.length > 0">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="(hour, index) in dailyData.busiest_hours" :key="index">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span x-text="hour.hour_formatted" class="text-sm text-gray-900"></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900" x-text="hour.transaction_count"></div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </template>
                <template x-if="!dailyData || !dailyData.busiest_hours || dailyData.busiest_hours.length === 0">
                    <div class="py-4 text-center text-gray-500">Belum ada data jam teramai</div>
                </template>
                <div class="mt-4 text-right">
                    <a href="{{ route('reports.busiest-hours') }}" class="text-sm text-blue-600 hover:text-blue-800">Lihat laporan lengkap →</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection