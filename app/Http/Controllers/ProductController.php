<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'ingredients']);

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by temperature
        if ($request->filled('temperature')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('temperature', $request->temperature);
            });
        }

        // Filter by active status
        if ($request->filled('active')) {
            $query->where('active', $request->boolean('active'));
        }

        $products = $query->paginate(10);
        $categories = Category::all();
        
        if ($request->expectsJson()) {
            return response()->json(['products' => $products]);
        }
        
        return view('products.index', compact('products', 'categories'));
    }
    
    public function create()
    {
        $categories = Category::all();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'selling_price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'stock' => 'required|integer|min:0',
            'description' => 'sometimes|nullable|string',
            'active' => 'sometimes|boolean',
            'ingredients' => 'required|array',
            'ingredients.*.id' => 'required|exists:ingredients,id',
            'ingredients.*.quantity' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            
            return back()->withErrors($validator)->withInput();
        }

        // Calculate cost price based on ingredients
        $costPrice = 0;
        foreach ($request->ingredients as $ingredientData) {
            $ingredient = Ingredient::find($ingredientData['id']);
            $costPrice += $ingredient->price * $ingredientData['quantity'];
        }

        try {
            DB::beginTransaction();

            $product = Product::create([
                'name' => $request->name,
                'selling_price' => $request->selling_price,
                'cost_price' => $costPrice,
                'category_id' => $request->category_id,
                'stock' => $request->stock,
                'description' => $request->description,
                'active' => $request->filled('active') ? true : false,
            ]);

            // Attach ingredients
            foreach ($request->ingredients as $ingredientData) {
                $product->ingredients()->attach($ingredientData['id'], [
                    'quantity' => $ingredientData['quantity']
                ]);
            }

            DB::commit();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Product created successfully',
                    'product' => $product->load(['category', 'ingredients'])
                ], 201);
            }
            
            return redirect()->route('products.show', $product)->with('success', 'Produk berhasil ditambahkan');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Failed to create product', 'error' => $e->getMessage()], 500);
            }
            
            return back()->with('error', 'Gagal menambahkan produk: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Request $request, Product $product)
    {
        $product->load(['category', 'ingredients']);
        
        if ($request->expectsJson()) {
            return response()->json(['product' => $product]);
        }
        
        return view('products.show', compact('product'));
    }
    
    public function edit(Product $product)
    {
        $product->load('ingredients');
        $categories = Category::all();
        
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'selling_price' => 'sometimes|numeric|min:0',
            'category_id' => 'sometimes|exists:categories,id',
            'stock' => 'sometimes|integer|min:0',
            'description' => 'sometimes|nullable|string',
            'active' => 'sometimes|boolean',
            'ingredients' => 'sometimes|array',
            'ingredients.*.id' => 'required|exists:ingredients,id',
            'ingredients.*.quantity' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Update basic product info
            $product->fill($request->except('ingredients', 'cost_price', 'active'));
            
            // Handle active status
            $product->active = $request->filled('active');

            // Update ingredients if provided
            if ($request->has('ingredients')) {
                // Calculate new cost price
                $costPrice = 0;
                foreach ($request->ingredients as $ingredientData) {
                    $ingredient = Ingredient::find($ingredientData['id']);
                    $costPrice += $ingredient->price * $ingredientData['quantity'];
                }
                $product->cost_price = $costPrice;

                // Detach all existing ingredients and attach new ones
                $product->ingredients()->detach();
                foreach ($request->ingredients as $ingredientData) {
                    $product->ingredients()->attach($ingredientData['id'], [
                        'quantity' => $ingredientData['quantity']
                    ]);
                }
            }

            $product->save();
            DB::commit();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Product updated successfully',
                    'product' => $product->fresh(['category', 'ingredients'])
                ]);
            }
            
            return redirect()->route('products.show', $product)->with('success', 'Produk berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Failed to update product', 'error' => $e->getMessage()], 500);
            }
            
            return back()->with('error', 'Gagal memperbarui produk: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Request $request, Product $product)
    {
        // Check if the product is used in any transactions
        if ($product->transactionItems()->count() > 0) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Cannot delete product that has been used in transactions', 
                    'suggestion' => 'Consider deactivating the product instead'
                ], 400);
            }
            
            return back()->with('error', 'Tidak dapat menghapus produk yang telah digunakan dalam transaksi. Nonaktifkan produk sebagai gantinya.');
        }

        try {
            DB::beginTransaction();
            
            // Detach all ingredients first
            $product->ingredients()->detach();
            
            // Delete the product
            $product->delete();
            
            DB::commit();
            
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Product deleted successfully']);
            }
            
            return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Failed to delete product', 'error' => $e->getMessage()], 500);
            }
            
            return back()->with('error', 'Gagal menghapus produk: ' . $e->getMessage());
        }
    }

    public function updateStock(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'stock' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            
            return back()->withErrors($validator)->withInput();
        }

        $product->update([
            'stock' => $request->stock
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Product stock updated successfully',
                'product' => $product
            ]);
        }
        
        return redirect()->route('products.show', $product)->with('success', 'Stok produk berhasil diperbarui');
    }
}
