@extends('layouts.app')

@section('title', 'Tambah Produk')

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<div x-data="{
    ingredients: [],
    selectedIngredients: [],
    costPrice: 0,
    isLoading: true,
    
    async init() {
        try {
            const response = await fetch('{{ route('ingredients.json') }}');
            const data = await response.json();
            this.ingredients = data.ingredients;
            this.isLoading = false;
        } catch (error) {
            console.error('Error fetching ingredients:', error);
            this.isLoading = false;
        }
    },
    
    addIngredient(ingredientId) {
        const ingredient = this.ingredients.find(i => i.id == ingredientId);
        
        if (!ingredient) return;
        
        // Check if already added
        if (this.selectedIngredients.some(i => i.id == ingredientId)) {
            alert('Bahan ini sudah ditambahkan');
            return;
        }
        
        this.selectedIngredients.push({
            id: ingredient.id,
            name: ingredient.name,
            price: ingredient.price,
            quantity: 1
        });
        
        this.calculateCostPrice();
    },
    
    removeIngredient(index) {
        this.selectedIngredients.splice(index, 1);
        this.calculateCostPrice();
    },
    
    updateQuantity(index, value) {
        const qty = parseFloat(value);
        
        if (isNaN(qty) || qty <= 0) {
            this.selectedIngredients[index].quantity = 1;
        } else {
            this.selectedIngredients[index].quantity = qty;
        }
        
        this.calculateCostPrice();
    },
    
    calculateCostPrice() {
        this.costPrice = this.selectedIngredients.reduce((total, item) => {
            return total + (item.price * item.quantity);
        }, 0);
    }
}">
    <!-- Loading Indicator -->
    <div x-show="isLoading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
    </div>
    
    <div x-show="!isLoading" x-cloak>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Tambah Produk Baru</h1>
            <a href="{{ route('products.index') }}" class="text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>
        
        @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-500"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">
                        <strong>Whoops!</strong> Ada beberapa masalah dengan input Anda.
                    </p>
                    <ul class="mt-1 text-sm text-red-700 list-disc list-inside pl-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif
        
        <form action="{{ route('products.store') }}" method="POST">
            @csrf
            
            <div class="bg-white shadow-sm rounded-lg overflow-hidden mb-6">
                <div class="p-6">
                    <h2 class="text-lg font-medium text-gray-800 mb-4">Informasi Produk</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Produk <span class="text-red-600">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                        </div>
                        
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Kategori <span class="text-red-600">*</span></label>
                            <select name="category_id" id="category_id" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                                <option value="">Pilih Kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }} ({{ $category->temperature }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label for="selling_price" class="block text-sm font-medium text-gray-700 mb-1">Harga Jual <span class="text-red-600">*</span></label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">Rp</span>
                                </div>
                                <input type="number" name="selling_price" id="selling_price" value="{{ old('selling_price') }}" min="0" step="100" required
                                       class="w-full pl-12 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                            </div>
                        </div>
                        
                        <div>
                            <label for="stock" class="block text-sm font-medium text-gray-700 mb-1">Stok <span class="text-red-600">*</span></label>
                            <input type="number" name="stock" id="stock" value="{{ old('stock', 0) }}" min="0" required
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                        <textarea name="description" id="description" rows="3" 
                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">{{ old('description') }}</textarea>
                    </div>
                    
                    <div>
                        <div class="flex items-center">
                            <input type="checkbox" name="active" id="active" value="1" 
                                   @if(old('active', true)) checked @endif
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                            <label for="active" class="ml-2 block text-sm font-medium text-gray-700">Produk Aktif</label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white shadow-sm rounded-lg overflow-hidden mb-6">
                <div class="p-6">
                    <h2 class="text-lg font-medium text-gray-800 mb-4">Bahan-bahan</h2>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pilih dan Tambahkan Bahan</label>
                        <div class="flex space-x-2">
                            <select id="ingredient_selector" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                                <option value="">Pilih Bahan</option>
                                <template x-for="ingredient in ingredients" :key="ingredient.id">
                                    <option :value="ingredient.id" x-text="ingredient.name + ' (Rp ' + new Intl.NumberFormat('id-ID').format(ingredient.price) + '/' + (ingredient.unit || 'unit') + ')'"></option>
                                </template>
                            </select>
                            <button type="button" @click="addIngredient(document.getElementById('ingredient_selector').value)"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-plus mr-2"></i>
                                Tambah
                            </button>
                        </div>
                    </div>
                    
                    <template x-if="selectedIngredients.length === 0">
                        <div class="bg-gray-50 p-4 text-center text-gray-500 rounded-md">
                            Belum ada bahan yang dipilih
                        </div>
                    </template>
                    
                    <template x-if="selectedIngredients.length > 0">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bahan</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Per Unit</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <template x-for="(item, index) in selectedIngredients" :key="index">
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="item.name"></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(item.price)"></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input type="number" :name="'ingredients['+index+'][id]'" :value="item.id" hidden>
                                                <input type="number" :name="'ingredients['+index+'][quantity]'" x-model="item.quantity" step="0.01" min="0.01" 
                                                       @change="updateQuantity(index, $event.target.value)"
                                                       class="w-20 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(item.price * item.quantity)"></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <button type="button" @click="removeIngredient(index)" class="text-red-600 hover:text-red-900">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 text-right font-bold">Total Harga Modal:</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(costPrice)"></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                            <input type="hidden" name="cost_price" x-model="costPrice">
                        </div>
                    </template>
                </div>
            </div>
            
            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-save mr-2"></i>
                    Simpan Produk
                </button>
            </div>
        </form>
    </div>
</div>
@endsection