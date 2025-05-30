<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with('user');

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('expense_date', [
                $request->start_date,
                $request->end_date,
            ]);
        } elseif ($request->has('date')) {
            $query->whereDate('expense_date', $request->date);
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $expenses = $query->orderBy('expense_date', 'desc')
                         ->orderBy('created_at', 'desc')
                         ->paginate(15);
        
        // Get all users for the filter (admin only)
        $users = [];
        if (Auth::user()->role === 'admin') {
            $users = User::all();
        }
        
        if ($request->expectsJson()) {
            return response()->json($expenses);
        }
        
        return view('expenses.index', compact('expenses', 'users'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            
            return back()->withErrors($validator)->withInput();
        }

        $expense = Expense::create([
            'description' => $request->description,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'user_id' => $request->user()->id,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Expense recorded successfully',
                'expense' => $expense->load('user')
            ], 201);
        }
        
        return redirect()->route('expenses.index')->with('success', 'Pengeluaran berhasil ditambahkan');
    }

    public function show(Request $request, Expense $expense)
    {
        $expense->load('user');
        
        if ($request->expectsJson()) {
            return response()->json(['expense' => $expense]);
        }
        
        return view('expenses.show', compact('expense'));
    }

    public function update(Request $request, Expense $expense)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'sometimes|string|max:255',
            'amount' => 'sometimes|numeric|min:0.01',
            'expense_date' => 'sometimes|date',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            
            return back()->withErrors($validator)->withInput();
        }

        $expense->update($request->only(['description', 'amount', 'expense_date']));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Expense updated successfully',
                'expense' => $expense->fresh('user')
            ]);
        }
        
        return redirect()->route('expenses.index')->with('success', 'Pengeluaran berhasil diperbarui');
    }

    public function destroy(Request $request, Expense $expense)
    {
        $expense->delete();
        
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Expense deleted successfully']);
        }
        
        return redirect()->route('expenses.index')->with('success', 'Pengeluaran berhasil dihapus');
    }

    // Daily expense summary (for dashboard)
    public function dailyExpense(Request $request)
    {
        $date = $request->date ? Carbon::parse($request->date) : Carbon::today();
        
        $dailyExpense = Expense::whereDate('expense_date', $date)
                              ->sum('amount');
        
        $expenseCount = Expense::whereDate('expense_date', $date)
                              ->count();
        
        if ($request->expectsJson()) {
            return response()->json([
                'date' => $date->toDateString(),
                'expense' => $dailyExpense,
                'expense_count' => $expenseCount
            ]);
        }
        
        return view('expenses.daily', compact('date', 'dailyExpense', 'expenseCount'));
    }
}
