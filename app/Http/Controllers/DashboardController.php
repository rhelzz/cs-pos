<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        
        // Daily income
        $dailyIncome = Transaction::whereDate('created_at', $today)->sum('total_amount');
        
        // Daily expenses
        $dailyExpenses = Expense::whereDate('expense_date', $today)->sum('amount');
        
        // Daily profit
        $cogsToday = DB::table('transaction_items')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->whereDate('transactions.created_at', $today)
            ->select(DB::raw('SUM(products.cost_price * transaction_items.quantity) as total_cost'))
            ->value('total_cost') ?? 0;
            
        $grossProfit = $dailyIncome - $cogsToday;
        $netProfit = $grossProfit - $dailyExpenses;
        
        // Transaction count today
        $transactionCount = Transaction::whereDate('created_at', $today)->count();
        
        // Last 5 transactions
        $latestTransactions = Transaction::with(['items.product', 'user'])
                                       ->orderBy('created_at', 'desc')
                                       ->limit(5)
                                       ->get();
        
        // Low stock products (less than 10)
        $lowStockProducts = Product::where('stock', '<', 10)
                                 ->where('active', true)
                                 ->with('category')
                                 ->get();
        
        // Today's best selling products
        $bestSellingProducts = DB::table('transaction_items')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->whereDate('transactions.created_at', $today)
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(transaction_items.quantity) as total_quantity')
            )
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_quantity', 'desc')
            ->limit(5)
            ->get();
        
        // Busiest hours today
        $busiestHours = DB::table('transactions')
            ->whereDate('created_at', $today)
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->orderBy('transaction_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                $hour = $item->hour;
                $period = $hour >= 12 ? 'PM' : 'AM';
                $hour12 = $hour % 12;
                $hour12 = $hour12 ? $hour12 : 12; // Convert 0 to 12
                $item->hour_formatted = $hour12 . ' ' . $period;
                return $item;
            });
        
        return response()->json([
            'date' => $today->toDateString(),
            'daily_income' => $dailyIncome,
            'daily_expenses' => $dailyExpenses,
            'gross_profit' => $grossProfit,
            'net_profit' => $netProfit,
            'transaction_count' => $transactionCount,
            'latest_transactions' => $latestTransactions,
            'low_stock_products' => $lowStockProducts,
            'best_selling_products' => $bestSellingProducts,
            'busiest_hours' => $busiestHours
        ]);
    }
}
