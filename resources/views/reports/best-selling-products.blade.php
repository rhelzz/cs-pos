@extends('layouts.app')

@section('title', 'Laporan Produk Terlaris')

@section('content')
<div x-data="{
    reports: null,
    selectedPeriod: 'day',
    startDate: '{{ date('Y-m-d') }}',
    endDate: '{{ date('Y-m-d') }}',
    limit: 10,
    isLoading: true,
    
    init() {
        this.fetchReports();
    },
    
    async fetchReports() {
        this.isLoading = true;
        
        const params = new URLSearchParams({
            period: this.selectedPeriod,
            limit: this.limit
        });
        
        if (this.selectedPeriod === 'custom') {
            params.append('start_date', this.startDate);
            params.append('end_date', this.endDate);
        }
        
        try {
            const response = await fetch(`{{ route('reports.best-selling-products') }}?${params.toString()}`);
            const data = await response.json();
            this.reports = data;
            this.isLoading = false;
        } catch (error) {
            console.error('Error:', error);
            this.isLoading = false;
        }
    },
    
    changePeriod(period) {
        this.selectedPeriod = period;
        this.fetchReports();
    }
}">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Laporan Produk Terlaris</h1>
        <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>
    
    <div class="bg-white shadow-sm rounded-lg overflow-hidden mb-6">
        <div class="p-4 sm:p-6">
            <h2 class="text-lg font-medium text-gray-800 mb-4">Filter Laporan</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">Periode</label>
                    <div class="flex space-x-2">
                        <button @click="changePeriod('day')" 
                                class="px-3 py-2 text-sm rounded-md"
                                :class="selectedPeriod === 'day' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'">
                            Hari
                        </button>
                        <button @click="changePeriod('week')"
                                class="px-3 py-2 text-sm rounded-md"
                                :class="selectedPeriod === 'week' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'">
                            Minggu
                        </button>
                        <button @click="changePeriod('month')"
                                class="px-3 py-2 text-sm rounded-md"
                                :class="selectedPeriod === 'month' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'">
                            Bulan
                        </button>
                        <button @click="changePeriod('year')"
                                class="px-3 py-2 text-sm rounded-md"
                                :class="selectedPeriod === 'year' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'">
                            Tahun
                        </button>
                        <button @click="changePeriod('custom')"
                                class="px-3 py-2 text-sm rounded-md"
                                :class="selectedPeriod === 'custom' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'">
                            Custom
                        </button>
                    </div>
                </div>
                
                <template x-if="selectedPeriod === 'custom'">
                    <div>
                        <label for="start_date" class="block text-gray-700 text-sm font-medium mb-2">Tanggal Mulai</label>
                        <input type="date" id="start_date" x-model="startDate"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                    </div>
                </template>
                
                <template x-if="selectedPeriod === 'custom'">
                    <div>
                        <label for="end_date" class="block text-gray-700 text-sm font-medium mb-2">Tanggal Selesai</label>
                        <input type="date" id="end_date" x-model="endDate"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                    </div>
                </template>
                
                <div>
                    <label for="limit" class="block text-gray-700 text-sm font-medium mb-2">Jumlah Data</label>
                    <select id="limit" x-model="limit" @change="fetchReports()"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                        <option value="5">5 produk</option>
                        <option value="10">10 produk</option>
                        <option value="25">25 produk</option>
                        <option value="50">50 produk</option>
                    </select>
                </div>
                
                <template x-if="selectedPeriod === 'custom'">
                    <div class="flex items-end">
                        <button @click="fetchReports()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg w-full">
                            <i class="fas fa-search mr-2"></i> Terapkan
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>
    
    <!-- Loading Indicator -->
    <div x-show="isLoading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
    </div>
    
    <div x-show="!isLoading" x-cloak>
        <div class="bg-white shadow-sm rounded-lg overflow-hidden mb-6">
            <div class="p-4 sm:p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-medium text-gray-800">
                        Produk Terlaris
                        <span x-text="reports ? `(${reports.start_date} - ${reports.end_date})` : ''"></span>
                    </h2>
                </div>
                
                <template x-if="reports && reports.products && reports.products.length">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Terjual</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Penjualan</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Profit</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="(product, index) in reports.products" :key="product.id">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="index + 1"></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900" x-text="product.name"></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="product.total_quantity"></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(product.total_sales)"></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(product.total_sales - (product.cost_price * product.total_quantity))"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </template>
                
                <template x-if="!reports || !reports.products || !reports.products.length">
                    <div class="py-8 text-center text-gray-500">
                        Tidak ada data produk terlaris untuk periode yang dipilih
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
@endsection