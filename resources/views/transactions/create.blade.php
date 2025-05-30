@extends('layouts.app')

@section('title', 'Transaksi Baru')

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<div x-data="{
    products: [],
    categories: [],
    cart: [],
    searchQuery: '',
    selectedCategory: '',
    paymentAmount: 0,
    paymentMethod: 'cash',
    customerName: '',
    isLoading: true,
    isProcessing: false,
    
    async init() {
        try {
            // Fetch products
            const productsResponse = await fetch('{{ route('products.index') }}?active=1');
            const productsData = await productsResponse.json();
            this.products = productsData.products;
            
            // Fetch categories
            const categoriesResponse = await fetch('{{ route('categories.index') }}');
            const categoriesData = await categoriesResponse.json();
            this.categories = categoriesData.categories;
            
            this.isLoading = false;
        } catch (error) {
            console.error('Error fetching data:', error);
            this.isLoading = false;
        }
    },
    
    filteredProducts() {
        let filtered = this.products;
        
        // Filter by search query
        if (this.searchQuery) {
            const query = this.searchQuery.toLowerCase();
            filtered = filtered.filter(p => p.name.toLowerCase().includes(query));
        }
        
        // Filter by category
        if (this.selectedCategory) {
            filtered = filtered.filter(p => p.category_id == this.selectedCategory);
        }
        
        // Only show products with stock > 0
        filtered = filtered.filter(p => p.stock > 0);
        
        return filtered;
    },
    
    addToCart(product) {
        const existingItem = this.cart.find(item => item.id === product.id);
        
        if (existingItem) {
            // Check if we have enough stock
            if (existingItem.quantity >= product.stock) {
                alert('Stok tidak mencukupi!');
                return;
            }
            existingItem.quantity += 1;
        } else {
            this.cart.push({
                id: product.id,
                name: product.name,
                price: product.selling_price,
                quantity: 1,
                max_stock: product.stock
            });
        }
    },
    
    removeFromCart(index) {
        this.cart.splice(index, 1);
    },
    
    updateQuantity(index, value) {
        const item = this.cart[index];
        const newQuantity = parseInt(value);
        
        if (isNaN(newQuantity) || newQuantity < 1) {
            item.quantity = 1;
        } else if (newQuantity > item.max_stock) {
            item.quantity = item.max_stock;
        } else {
            item.quantity = newQuantity;
        }
    },
    
    totalAmount() {
        return this.cart.reduce((total, item) => total + (item.price * item.quantity), 0);
    },
    
    changeAmount() {
        const total = this.totalAmount();
        return this.paymentAmount > total ? this.paymentAmount - total : 0;
    },
    
    async processTransaction() {
        if (this.cart.length === 0) {
            alert('Keranjang tidak boleh kosong!');
            return;
        }
        
        const total = this.totalAmount();
        if (this.paymentAmount < total) {
            alert('Jumlah pembayaran kurang dari total belanja!');
            return;
        }
        
        this.isProcessing = true;
        
        try {
            const response = await fetch('{{ route('transactions.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content')
                },
                body: JSON.stringify({
                    items: this.cart.map(item => ({
                        product_id: item.id,
                        quantity: item.quantity
                    })),
                    payment_method: this.paymentMethod,
                    payment_amount: parseFloat(this.paymentAmount),
                    customer_name: this.customerName || null
                })
            });
            
            const data = await response.json();
            
            if (response.ok) {
                // Redirect to transaction detail page
                window.location.href = `{{ url('transactions') }}/${data.transaction.id}`;
            } else {
                alert('Error: ' + (data.message || 'Gagal memproses transaksi'));
                this.isProcessing = false;
            }
        } catch (error) {
            console.error('Error processing transaction:', error);
            alert('Gagal memproses transaksi');
            this.isProcessing = false;
        }
    }
}">
    <!-- Loading Indicator -->
    <div x-show="isLoading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
    </div>
    
    <div x-show="!isLoading" x-cloak>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Transaksi Baru</h1>
            <a href="{{ route('transactions.index') }}" class="text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>
        
        <div class="lg:grid lg:grid-cols-3 lg:gap-6">
            <!-- Daftar Produk -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow-sm rounded-lg overflow-hidden mb-6">
                    <div class="p-4 sm:p-6 border-b">
                        <h2 class="text-lg font-medium text-gray-800 mb-4">Daftar Produk</h2>
                        
                        <!-- Filter dan Pencarian -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="search" class="sr-only">Cari Produk</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-gray-400"></i>
                                    </div>
                                    <input type="text" x-model="searchQuery" id="search" placeholder="Cari produk..." 
                                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                </div>
                            </div>
                            <div>
                                <select x-model="selectedCategory" class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Semua Kategori</option>
                                    <template x-for="category in categories" :key="category.id">
                                        <option :value="category.id" x-text="category.name + ' (' + category.temperature + ')'"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Daftar Produk -->
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-4">
                            <template x-for="product in filteredProducts()" :key="product.id">
                                <div class="bg-white border rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow cursor-pointer"
                                     @click="addToCart(product)">
                                    <div class="h-24 flex items-center justify-center bg-gray-100 rounded-md mb-3">
                                        <i class="fas fa-box text-3xl text-gray-400"></i>
                                    </div>
                                    <div class="mt-2">
                                        <h3 class="text-sm font-medium text-gray-900 truncate" x-text="product.name"></h3>
                                        <p class="text-xs text-gray-500" x-text="product.category ? product.category.name : ''"></p>
                                        <div class="mt-2 flex items-center justify-between">
                                            <p class="text-sm font-semibold text-gray-900" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(product.selling_price)"></p>
                                            <span class="text-xs bg-green-100 text-green-800 py-1 px-2 rounded-full" x-text="'Stok: ' + product.stock"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            
                            <template x-if="filteredProducts().length === 0">
                                <div class="col-span-full py-8 text-center text-gray-500">
                                    Tidak ada produk yang ditemukan
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Keranjang dan Checkout -->
            <div class="lg:col-span-1">
                <!-- Keranjang -->
                <div class="bg-white shadow-sm rounded-lg overflow-hidden mb-4">
                    <div class="p-4 border-b">
                        <h2 class="text-lg font-medium text-gray-800">Keranjang</h2>
                    </div>
                    <div class="p-4">
                        <div class="space-y-3">
                            <template x-if="cart.length === 0">
                                <div class="py-6 text-center text-gray-500">
                                    Keranjang kosong
                                </div>
                            </template>
                            
                            <template x-for="(item, index) in cart" :key="index">
                                <div class="flex justify-between items-center border-b pb-3">
                                    <div>
                                        <h3 class="text-sm font-medium" x-text="item.name"></h3>
                                        <div class="flex items-center mt-1">
                                            <div class="flex items-center border rounded">
                                                <button type="button" class="px-3 py-1 text-gray-600 hover:bg-gray-100"
                                                        @click="updateQuantity(index, item.quantity - 1)">-</button>
                                                <input type="number" x-model.number="item.quantity" min="1" :max="item.max_stock"
                                                       @change="updateQuantity(index, item.quantity)"
                                                       class="w-12 text-center border-0 focus:outline-none focus:ring-0 p-0">
                                                <button type="button" class="px-3 py-1 text-gray-600 hover:bg-gray-100"
                                                        @click="updateQuantity(index, item.quantity + 1)">+</button>
                                            </div>
                                            <span class="text-xs text-gray-500 ml-2" x-text="'@' + new Intl.NumberFormat('id-ID').format(item.price)"></span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-right" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(item.price * item.quantity)"></div>
                                        <button type="button" class="text-red-600 hover:text-red-800 text-xs mt-1" @click="removeFromCart(index)">Hapus</button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                
                <!-- Checkout -->
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="p-4 border-b">
                        <h2 class="text-lg font-medium text-gray-800">Detail Pembayaran</h2>
                    </div>
                    <div class="p-4">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-medium mb-2" for="customer_name">
                                Nama Pelanggan (Opsional)
                            </label>
                            <input type="text" id="customer_name" x-model="customerName"
                                   class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Masukkan nama pelanggan">
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-medium mb-2" for="payment_method">
                                Metode Pembayaran
                            </label>
                            <select id="payment_method" x-model="paymentMethod"
                                    class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="cash">Tunai</option>
                                <option value="card">Kartu</option>
                                <option value="digital">Digital</option>
                            </select>
                        </div>
                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-medium mb-2" for="payment_amount">
                                Jumlah Pembayaran
                            </label>
                            <input type="number" id="payment_amount" x-model="paymentAmount"
                                   class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="0" min="0">
                        </div>
                        
                        <div class="border-t pt-4">
                            <div class="flex justify-between mb-2">
                                <span class="text-gray-600">Total</span>
                                <span class="font-semibold" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(totalAmount())"></span>
                            </div>
                            <div class="flex justify-between mb-2">
                                <span class="text-gray-600">Pembayaran</span>
                                <span class="font-semibold" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(paymentAmount)"></span>
                            </div>
                            <div class="flex justify-between mb-4">
                                <span class="text-gray-600">Kembalian</span>
                                <span class="font-semibold" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(changeAmount())"></span>
                            </div>
                            
                            <button type="button" @click="processTransaction"
                                    :disabled="cart.length === 0 || paymentAmount < totalAmount() || isProcessing"
                                    :class="{'opacity-50 cursor-not-allowed': cart.length === 0 || paymentAmount < totalAmount() || isProcessing}"
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg focus:outline-none">
                                <span x-show="!isProcessing">Proses Pembayaran</span>
                                <span x-show="isProcessing">
                                    <i class="fas fa-spinner fa-spin mr-2"></i> Memproses...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection