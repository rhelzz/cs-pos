@extends('layouts.app')

@section('title', 'Detail Transaksi')

@section('content')
<div x-data="{
    transaction: null,
    isLoading: true,
    showPrintModal: false,
    printLoading: false,
    
    init() {
        this.fetchTransactionData();
    },
    
    async fetchTransactionData() {
        try {
            const response = await fetch('{{ route('transactions.show', $transaction->id) }}');
            const data = await response.json();
            this.transaction = data.transaction;
            this.isLoading = false;
        } catch (error) {
            console.error('Error fetching transaction:', error);
            this.isLoading = false;
        }
    },
    
    printReceipt() {
        this.printLoading = true;
        setTimeout(() => {
            const printContent = document.getElementById('receiptContent').innerHTML;
            const originalContent = document.body.innerHTML;
            
            document.body.innerHTML = printContent;
            window.print();
            document.body.innerHTML = originalContent;
            
            this.printLoading = false;
            this.showPrintModal = false;
            
            // Reinitialize Alpine.js
            Alpine.initTree(document.body);
        }, 500);
    }
}">
    <!-- Loading Indicator -->
    <div x-show="isLoading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
    </div>
    
    <div x-show="!isLoading" x-cloak>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Detail Transaksi #{{ $transaction->id }}</h1>
            <div class="flex space-x-2">
                <button @click="showPrintModal = true" class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg flex items-center">
                    <i class="fas fa-print mr-2"></i>
                    Cetak Struk
                </button>
                <a href="{{ route('transactions.index') }}" class="text-gray-600 hover:text-gray-900">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
            </div>
        </div>
        
        <div class="bg-white shadow-sm rounded-lg overflow-hidden mb-6">
            <div class="p-4 sm:p-6 border-b">
                <h2 class="text-lg font-medium text-gray-800 mb-4">Informasi Transaksi</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Tanggal & Waktu</p>
                        <p class="font-medium">{{ $transaction->created_at->format('d M Y, H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Kasir</p>
                        <p class="font-medium">{{ $transaction->user->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Metode Pembayaran</p>
                        <p class="font-medium">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $transaction->payment_method == 'cash' ? 'bg-green-100 text-green-800' : 
                                   ($transaction->payment_method == 'card' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800') }}">
                                {{ $transaction->payment_method == 'cash' ? 'Tunai' : 
                                   ($transaction->payment_method == 'card' ? 'Kartu' : 'Digital') }}
                            </span>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Pelanggan</p>
                        <p class="font-medium">{{ $transaction->customer_name ?: 'Umum' }}</p>
                    </div>
                </div>
            </div>
            
            <div class="p-4 sm:p-6">
                <h3 class="text-lg font-medium text-gray-800 mb-4">Item Transaksi</h3>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($transaction->items as $index => $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $index + 1 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $item->product->name ?? 'Produk tidak ditemukan' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $item->quantity }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-6 border-t pt-4">
                    <div class="flex justify-end">
                        <div class="w-full md:w-1/2 lg:w-1/3 space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total</span>
                                <span class="font-medium">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Pembayaran</span>
                                <span class="font-medium">Rp {{ number_format($transaction->payment_amount, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between pt-2 border-t">
                                <span class="text-gray-600 font-medium">Kembalian</span>
                                <span class="font-bold">Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Print Modal -->
        <div x-show="showPrintModal" class="fixed inset-0 overflow-y-auto z-50" x-cloak>
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                    Cetak Struk
                                </h3>
                                
                                <!-- Preview Struk -->
                                <div class="mt-2 bg-gray-100 p-4 overflow-y-auto h-64 text-sm">
                                    <div id="receiptContent" class="text-center">
                                        <h2 class="text-xl font-bold mb-1">POS System</h2>
                                        <p class="text-xs mb-4">Jl. Contoh No. 123, Kota</p>
                                        
                                        <div class="text-left text-xs">
                                            <p>No: #{{ $transaction->id }}</p>
                                            <p>Tanggal: {{ $transaction->created_at->format('d/m/Y H:i') }}</p>
                                            <p>Kasir: {{ $transaction->user->name ?? '-' }}</p>
                                            <p>Pelanggan: {{ $transaction->customer_name ?: 'Umum' }}</p>
                                        </div>
                                        
                                        <div class="my-2 border-t border-b py-2">
                                            <table class="w-full text-xs">
                                                <thead>
                                                    <tr class="text-left">
                                                        <th>Item</th>
                                                        <th class="text-right">Qty</th>
                                                        <th class="text-right">Harga</th>
                                                        <th class="text-right">Subtotal</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($transaction->items as $item)
                                                    <tr>
                                                        <td>{{ $item->product->name ?? 'Produk tidak ditemukan' }}</td>
                                                        <td class="text-right">{{ $item->quantity }}</td>
                                                        <td class="text-right">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                                        <td class="text-right">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        
                                        <div class="text-xs text-right space-y-1">
                                            <p>Total: Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</p>
                                            <p>Bayar ({{ $transaction->payment_method == 'cash' ? 'Tunai' : 
                                               ($transaction->payment_method == 'card' ? 'Kartu' : 'Digital') }}): 
                                               Rp {{ number_format($transaction->payment_amount, 0, ',', '.') }}</p>
                                            <p>Kembali: Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</p>
                                        </div>
                                        
                                        <div class="mt-4 text-xs">
                                            <p>Terima Kasih Atas Kunjungan Anda</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="printReceipt" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            <span x-show="!printLoading">Cetak</span>
                            <span x-show="printLoading">
                                <i class="fas fa-spinner fa-spin mr-2"></i> Memproses...
                            </span>
                        </button>
                        <button type="button" @click="showPrintModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection