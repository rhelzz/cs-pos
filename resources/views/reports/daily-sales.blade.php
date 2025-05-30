@extends('layouts.app')

@section('title', 'Laporan Penjualan Harian')

@section('content')
<div x-data="{
    report: null,
    date: '{{ date('Y-m-d') }}',
    isLoading: true,
    
    init() {
        this.fetchReport();
    },
    
    async fetchReport() {
        this.isLoading = true;
        
        try {
            const response = await fetch(`{{ route('reports.daily-sales') }}?date=${this.date}`);
            const data = await response.json();
            this.report = data;
            this.isLoading = false;
        } catch (error) {
            console.error('Error:', error);
            this.isLoading = false;
        }
    },
}">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Laporan Penjualan Harian</h1>
        <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>
    
    <div class="bg-white shadow-sm rounded-lg overflow-hidden mb-6">
        <div class="p-4 sm:p-6">
            <h2 class="text-lg font-medium text-gray-800 mb-4">Pilih Tanggal</h2>
            
            <div class="flex flex-wrap items-end gap-4">
                <div class="w-full sm:w-auto">
                    <label for="date" class="block text-gray-700 text-sm font-medium mb-2">Tanggal</label>
                    <input type="date" id="date" x-model="date"
                           class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                </div>
                
                <div>
                    <button @click="fetchReport()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg">
                        <i class="fas fa-search mr-2"></i> Tampilkan
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Loading Indicator -->
    <div x-show="isLoading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
    </div>
    
    <div x-show="!isLoading" x-cloak>
        <!-- Ringkasan -->
        <div class="bg-white shadow-sm rounded-lg overflow-hidden mb-6">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-800 mb-4">
                    <span x-text="report ? `Ringkasan Penjualan ${report.date}` : 'Ringkasan Penjualan'"></span>
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-600 mb-1">Total Penjualan</p>
                        <p class="text-xl font-bold text-gray-900" x-text="report ? 'Rp ' + new Intl.NumberFormat('id-ID').format(report.total_sales) : 'Rp 0'"></p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-600 mb-1">Jumlah Transaksi</p>
                        <p class="text-xl font-bold text-gray-900" x-text="report ? report.transaction_count : '0'"></p>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-600 mb-1">Rata-rata per Transaksi</p>
                        <p class="text-xl font-bold text-gray-900" x-text="report && report.transaction_count > 0 ? 'Rp ' + new Intl.NumberFormat('id-ID').format(report.total_sales / report.transaction_count) : 'Rp 0'"></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Rincian Penjualan Produk -->
        <div class="bg-white shadow-sm rounded-lg overflow-hidden mb-6">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-800 mb-4">Rincian Penjualan per Produk</h2>
                
                <template x-if="report && report.product_sales && report.product_sales.length">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Terjual</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Penjualan</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="(product, index) in report.product_sales" :key="index">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="index + 1"></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900" x-text="product.name"></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="product.quantity_sold"></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(product.total_sales)"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </template>
                
                <template x-if="!report || !report.product_sales || !report.product_sales.length">
                    <div class="py-8 text-center text-gray-500">
                        Tidak ada data penjualan produk untuk tanggal yang dipilih
                    </div>
                </template>
            </div>
        </div>
        
        <!-- Rincian Metode Pembayaran -->
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-800 mb-4">Rincian per Metode Pembayaran</h2>
                
                <template x-if="report && report.payment_methods && report.payment_methods.length">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metode Pembayaran</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Transaksi</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="(method, index) in report.payment_methods" :key="index">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900" 
                                                 x-text="method.payment_method === 'cash' ? 'Tunai' : 
                                                        (method.payment_method === 'card' ? 'Kartu' : 'Digital')"></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="method.count"></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(method.total)"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </template>
                
                <template x-if="!report || !report.payment_methods || !report.payment_methods.length">
                    <div class="py-8 text-center text-gray-500">
                        Tidak ada data metode pembayaran untuk tanggal yang dipilih
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
@endsection