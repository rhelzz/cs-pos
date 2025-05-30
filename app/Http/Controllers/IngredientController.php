<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class IngredientController extends Controller
{
    // TAMPILAN CRUD
    public function index()
    {
        $ingredients = Ingredient::paginate(10);
        return view('ingredients.index', compact('ingredients'));
    }

    // ENDPOINT JSON UNTUK AJAX/API
    public function jsonList()
    {
        $ingredients = Ingredient::all();
        return response()->json(['ingredients' => $ingredients]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'unit' => 'sometimes|string|max:20',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $ingredient = Ingredient::create($request->all());

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Ingredient created successfully', 'ingredient' => $ingredient], 201);
        }
        return redirect()->route('ingredients.index')->with('success', 'Bahan berhasil ditambahkan');
    }

    public function show(Request $request, Ingredient $ingredient)
    {
        if ($request->expectsJson()) {
            return response()->json(['ingredient' => $ingredient]);
        }
        return view('ingredients.show', compact('ingredient'));
    }

    public function update(Request $request, Ingredient $ingredient)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'unit' => 'sometimes|string|max:20',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $ingredient->update($request->all());

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Ingredient updated successfully', 'ingredient' => $ingredient]);
        }
        return redirect()->route('ingredients.index')->with('success', 'Bahan berhasil diperbarui');
    }

    public function destroy(Request $request, Ingredient $ingredient)
    {
        if ($ingredient->products()->count() > 0) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Cannot delete ingredient used in products'], 400);
            }
            return back()->with('error', 'Tidak dapat menghapus bahan yang digunakan dalam produk');
        }

        $ingredient->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Ingredient deleted successfully']);
        }
        return redirect()->route('ingredients.index')->with('success', 'Bahan berhasil dihapus');
    }
}
