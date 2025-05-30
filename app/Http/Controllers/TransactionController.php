<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'items.product']);

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay(),
            ]);
        } elseif ($request->has('date')) {
            $date = Carbon::parse($request->date);
            $query->whereDate('created_at', $date);
        }

        // Filter by cashier/user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by payment method
        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15);
        return response()->json($transactions);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|string|in:cash,card,digital',
            'payment_amount' => 'required|numeric|min:0',
            'customer_name' => 'sometimes|nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $totalAmount = 0;
            $items = [];

            // Calculate total and prepare items
            foreach ($request->items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                
                // Check if enough stock is available
                if ($product->stock < $itemData['quantity']) {
                    return response()->json([
                        'message' => 'Insufficient stock for product: ' . $product->name,
                        'available' => $product->stock,
                        'requested' => $itemData['quantity']
                    ], 400);
                }

                $subtotal = $product->selling_price * $itemData['quantity'];
                $totalAmount += $subtotal;

                $items[] = [
                    'product_id' => $product->id,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $product->selling_price,
                    'subtotal' => $subtotal,
                ];

                // Reduce stock
                $product->decrement('stock', $itemData['quantity']);
            }

            // Check if payment amount is sufficient
            if ($request->payment_amount < $totalAmount) {
                return response()->json([
                    'message' => 'Insufficient payment amount',
                    'total' => $totalAmount,
                    'paid' => $request->payment_amount
                ], 400);
            }

            // Create transaction
            $transaction = Transaction::create([
                'user_id' => $request->user()->id,
                'total_amount' => $totalAmount,
                'payment_amount' => $request->payment_amount,
                'change_amount' => $request->payment_amount - $totalAmount,
                'payment_method' => $request->payment_method,
                'customer_name' => $request->customer_name,
            ]);

            // Create transaction items
            foreach ($items as $item) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Transaction created successfully',
                'transaction' => $transaction->load(['items.product', 'user'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create transaction', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['items.product', 'user']);
        return response()->json(['transaction' => $transaction]);
    }

    // Get latest transactions (for dashboard)
    public function latest(Request $request)
    {
        $limit = $request->limit ?? 5;
        $transactions = Transaction::with(['items.product', 'user'])
                                  ->orderBy('created_at', 'desc')
                                  ->limit($limit)
                                  ->get();
        
        return response()->json(['transactions' => $transactions]);
    }

    // Daily income summary (for dashboard)
    public function dailyIncome(Request $request)
    {
        $date = $request->date ? Carbon::parse($request->date) : Carbon::today();
        
        $dailyIncome = Transaction::whereDate('created_at', $date)
                                 ->sum('total_amount');
        
        $transactionCount = Transaction::whereDate('created_at', $date)
                                     ->count();
        
        return response()->json([
            'date' => $date->toDateString(),
            'income' => $dailyIncome,
            'transaction_count' => $transactionCount
        ]);
    }
}
