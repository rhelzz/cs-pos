<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    // Report for best-selling products
    public function bestSellingProducts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'period' => 'required|string|in:day,week,month,year,custom',
            'start_date' => 'required_if:period,custom|date',
            'end_date' => 'required_if:period,custom|date|after_or_equal:start_date',
            'limit' => 'sometimes|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $limit = $request->limit ?? 10;

        // Define date range based on period
        $startDate = Carbon::now();
        $endDate = Carbon::now();

        switch ($request->period) {
            case 'day':
                $startDate = Carbon::today();
                break;
            case 'week':
                $startDate = Carbon::now()->startOfWeek();
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                break;
            case 'year':
                $startDate = Carbon::now()->startOfYear();
                break;
            case 'custom':
                $startDate = Carbon::parse($request->start_date)->startOfDay();
                $endDate = Carbon::parse($request->end_date)->endOfDay();
                break;
        }

        $bestSellingProducts = DB::table('transaction_items')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->whereBetween('transactions.created_at', [$startDate, $endDate])
            ->select(
                'products.id',
                'products.name',
                'products.selling_price',
                'products.cost_price',
                DB::raw('SUM(transaction_items.quantity) as total_quantity'),
                DB::raw('SUM(transaction_items.subtotal) as total_sales')
            )
            ->groupBy('products.id', 'products.name', 'products.selling_price', 'products.cost_price')
            ->orderBy('total_quantity', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'period' => $request->period,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'products' => $bestSellingProducts
        ]);
    }

    // Report for busiest hours
    public function busiestHours(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'period' => 'required|string|in:day,week,month,year,custom',
            'start_date' => 'required_if:period,custom|date',
            'end_date' => 'required_if:period,custom|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Define date range based on period
        $startDate = Carbon::now();
        $endDate = Carbon::now();

        switch ($request->period) {
            case 'day':
                $startDate = Carbon::today();
                break;
            case 'week':
                $startDate = Carbon::now()->startOfWeek();
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                break;
            case 'year':
                $startDate = Carbon::now()->startOfYear();
                break;
            case 'custom':
                $startDate = Carbon::parse($request->start_date)->startOfDay();
                $endDate = Carbon::parse($request->end_date)->endOfDay();
                break;
        }

        $busiestHours = DB::table('transactions')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(total_amount) as total_sales')
            )
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->orderBy('transaction_count', 'desc')
            ->get();

        // Format hours in 12-hour format
        $formattedHours = $busiestHours->map(function ($item) {
            $hour = $item->hour;
            $period = $hour >= 12 ? 'PM' : 'AM';
            $hour12 = $hour % 12;
            $hour12 = $hour12 ? $hour12 : 12; // Convert 0 to 12
            
            return [
                'hour' => $hour,
                'hour_formatted' => $hour12 . ' ' . $period,
                'transaction_count' => $item->transaction_count,
                'total_sales' => $item->total_sales
            ];
        });

        return response()->json([
            'period' => $request->period,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'hours' => $formattedHours
        ]);
    }

    // Daily sales report
    public function dailySalesReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'sometimes|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $date = $request->date ? Carbon::parse($request->date) : Carbon::today();

        // Get total sales amount
        $totalSales = Transaction::whereDate('created_at', $date)->sum('total_amount');

        // Get transaction count
        $transactionCount = Transaction::whereDate('created_at', $date)->count();

        // Get product sales breakdown
        $productSales = DB::table('transaction_items')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->whereDate('transactions.created_at', $date)
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(transaction_items.quantity) as quantity_sold'),
                DB::raw('SUM(transaction_items.subtotal) as total_sales')
            )
            ->groupBy('products.id', 'products.name')
            ->orderBy('quantity_sold', 'desc')
            ->get();

        // Get payment method breakdown
        $paymentMethods = DB::table('transactions')
            ->whereDate('created_at', $date)
            ->select(
                'payment_method',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy('payment_method')
            ->get();

        return response()->json([
            'date' => $date->toDateString(),
            'total_sales' => $totalSales,
            'transaction_count' => $transactionCount,
            'product_sales' => $productSales,
            'payment_methods' => $paymentMethods
        ]);
    }

    // Daily profit report
    public function dailyProfitReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'sometimes|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $date = $request->date ? Carbon::parse($request->date) : Carbon::today();

        // Get sales revenue
        $salesRevenue = Transaction::whereDate('created_at', $date)->sum('total_amount');

        // Get cost of goods sold
        $cogs = DB::table('transaction_items')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->whereDate('transactions.created_at', $date)
            ->select(DB::raw('SUM(products.cost_price * transaction_items.quantity) as total_cost'))
            ->value('total_cost') ?? 0;

        // Get expenses
        $expenses = DB::table('expenses')
            ->whereDate('expense_date', $date)
            ->sum('amount');

        // Calculate gross profit and net profit
        $grossProfit = $salesRevenue - $cogs;
        $netProfit = $grossProfit - $expenses;

        // Get profit breakdown by product
        $productProfit = DB::table('transaction_items')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->whereDate('transactions.created_at', $date)
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(transaction_items.quantity) as quantity_sold'),
                DB::raw('SUM(transaction_items.subtotal) as revenue'),
                DB::raw('SUM(products.cost_price * transaction_items.quantity) as cost'),
                DB::raw('SUM(transaction_items.subtotal - (products.cost_price * transaction_items.quantity)) as profit')
            )
            ->groupBy('products.id', 'products.name')
            ->orderBy('profit', 'desc')
            ->get();

        return response()->json([
            'date' => $date->toDateString(),
            'sales_revenue' => $salesRevenue,
            'cost_of_goods_sold' => $cogs,
            'expenses' => $expenses,
            'gross_profit' => $grossProfit,
            'net_profit' => $netProfit,
            'product_profit' => $productProfit
        ]);
    }
}
