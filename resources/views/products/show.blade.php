@extends('layouts.app')

@section('title', 'Detail Produk')

@section('content')
<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Detail Produk</h1>
        <div class="flex space-x-2">
            <a href="{{ route('products.edit', $product->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg flex items-center">
                <i class="fas fa-edit mr-2"></i>
                Edit
            </a>
            <a href="{{ route('products.index') }}" class="text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>
    </div>
    
    <div class="bg-white shadow-sm rounded-lg overflow-hidden mb-6">
        <div class="p-6 border-b">
            <h2 class="text-lg font-medium text-gray-800 mb-4">Informasi Produk</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Nama</p>
                    <p class="font-medium">{{ $product->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Kategori</p>
                    <p class="font-medium">{{ $product->category->name ?? '-' }} ({{ $product->category->temperature ?? '-' }})</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Harga Jual</p>
                    <p class="font-medium">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Harga Modal</p>
                    <p class="font-medium">Rp {{ number_format($product->cost_price, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Margin Keuntungan</p>
                    <p class="font-medium text-green-600">
                        Rp {{ number_format($product->selling_price - $product->cost_price, 0, ',', '.') }}
                        ({{ number_format(($product->selling_price - $product->cost_price) / $product->cost_price * 100, 2) }}%)
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Stok</p>
                    <p class="font-medium">{{ $product->stock }}</p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-sm text-gray-600 mb-1">Deskripsi</p>
                    <p class="font-medium">{{ $product->description ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Status</p>
                    <p>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @if($product->active) 
                                bg-green-100 text-green-800 
                            @else 
                                bg-red-100 text-red-800 
                            @endif">
                            {{ $product->active ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-800 mb-4">Bahan-bahan</h2>
            
            @if($product->ingredients->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Bahan</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Per Unit</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($product->ingredients as $ingredient)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $ingredient->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rp {{ number_format($ingredient->price, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $ingredient->pivot->quantity }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    Rp {{ number_format($ingredient->price * $ingredient->pivot->quantity, 0, ',', '.') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-right text-sm font-bold text-gray-900">Total Harga Modal:</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">Rp {{ number_format($product->cost_price, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="bg-gray-50 p-4 text-center text-gray-500 rounded-md">
                    Tidak ada bahan untuk produk ini
                </div>
            @endif
        </div>
    </div>
    
    <!-- Update Stock Section -->
    <div class="bg-white shadow-sm rounded-lg overflow-hidden mb-6">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-800 mb-4">Update Stok</h2>
            
            <form action="{{ route('products.stock.update', $product->id) }}" method="POST" class="max-w-md">
                @csrf
                @method('PUT')
                
                <div class="flex items-center space-x-2">
                    <div class="w-full">
                        <label for="stock" class="block text-sm font-medium text-gray-700 mb-1">Jumlah Stok</label>
                        <input type="number" name="stock" id="stock" value="{{ $product->stock }}" min="0" 
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                    </div>
                    <div class="pt-6">
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-save mr-2"></i> Update Stok
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection