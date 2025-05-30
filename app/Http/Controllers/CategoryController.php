<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::withCount('products')->paginate(10);
        
        if ($request->expectsJson()) {
            return response()->json(['categories' => $categories]);
        }
        
        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories',
            'temperature' => 'required|string|in:hot,cold',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            
            return back()->withErrors($validator)->withInput();
        }

        $category = Category::create($request->all());
        
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Category created successfully', 'category' => $category], 201);
        }
        
        return redirect()->route('categories.index')->with('success', 'Kategori berhasil ditambahkan');
    }

    public function show(Request $request, Category $category)
    {
        if ($request->expectsJson()) {
            return response()->json(['category' => $category]);
        }
        
        return view('categories.show', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255|unique:categories,name,' . $category->id,
            'temperature' => 'sometimes|string|in:hot,cold',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            
            return back()->withErrors($validator)->withInput();
        }

        $category->update($request->all());
        
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Category updated successfully', 'category' => $category]);
        }
        
        return redirect()->route('categories.index')->with('success', 'Kategori berhasil diperbarui');
    }

    public function destroy(Request $request, Category $category)
    {
        // Check if there are products associated with this category
        if ($category->products()->count() > 0) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Cannot delete category with associated products'], 400);
            }
            
            return back()->with('error', 'Tidak dapat menghapus kategori yang memiliki produk terkait');
        }

        $category->delete();
        
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Category deleted successfully']);
        }
        
        return redirect()->route('categories.index')->with('success', 'Kategori berhasil dihapus');
    }
}