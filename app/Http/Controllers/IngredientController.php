<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class IngredientController extends Controller
{
    public function index()
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
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $ingredient = Ingredient::create($request->all());
        return response()->json(['message' => 'Ingredient created successfully', 'ingredient' => $ingredient], 201);
    }

    public function show(Ingredient $ingredient)
    {
        return response()->json(['ingredient' => $ingredient]);
    }

    public function update(Request $request, Ingredient $ingredient)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'unit' => 'sometimes|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $ingredient->update($request->all());
        return response()->json(['message' => 'Ingredient updated successfully', 'ingredient' => $ingredient]);
    }

    public function destroy(Ingredient $ingredient)
    {
        // Check if there are products associated with this ingredient
        if ($ingredient->products()->count() > 0) {
            return response()->json(['message' => 'Cannot delete ingredient used in products'], 400);
        }

        $ingredient->delete();
        return response()->json(['message' => 'Ingredient deleted successfully']);
    }
}
