<?php

namespace App\Http\Controllers;

use App\Models\Product;
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
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by temperature
        if ($request->has('temperature')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('temperature', $request->temperature);
            });
        }

        // Filter by active status
        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        $products = $query->get();
        return response()->json(['products' => $products]);
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
            return response()->json(['errors' => $validator->errors()], 422);
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
                'active' => $request->active ?? true,
            ]);

            // Attach ingredients
            foreach ($request->ingredients as $ingredientData) {
                $product->ingredients()->attach($ingredientData['id'], [
                    'quantity' => $ingredientData['quantity']
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Product created successfully',
                'product' => $product->load(['category', 'ingredients'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create product', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(Product $product)
    {
        $product->load(['category', 'ingredients']);
        return response()->json(['product' => $product]);
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
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            // Update basic product info
            $product->fill($request->except('ingredients', 'cost_price'));

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

            return response()->json([
                'message' => 'Product updated successfully',
                'product' => $product->fresh(['category', 'ingredients'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update product', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Product $product)
    {
        // Check if the product is used in any transactions
        if ($product->transactionItems()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete product that has been used in transactions', 
                'suggestion' => 'Consider deactivating the product instead'
            ], 400);
        }

        try {
            DB::beginTransaction();
            
            // Detach all ingredients first
            $product->ingredients()->detach();
            
            // Delete the product
            $product->delete();
            
            DB::commit();
            return response()->json(['message' => 'Product deleted successfully']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to delete product', 'error' => $e->getMessage()], 500);
        }
    }

    public function updateStock(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'stock' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $product->update([
            'stock' => $request->stock
        ]);

        return response()->json([
            'message' => 'Product stock updated successfully',
            'product' => $product
        ]);
    }
}
