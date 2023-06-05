<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\DataModel;

class DataLoadController extends Controller
{
    public function loadData(Request $request)
    {
        DB::table('mystore')->truncate();

        // Read data from the text file
        $data = file_get_contents('mystore.txt');
        $lines = explode(PHP_EOL, $data);

        // Remove the header line
        unset($lines[0]);

        // Store data in the database
        foreach ($lines as $line) {
            $fields = explode(',', $line);

            $date = trim($fields[0]);
            $sku = trim($fields[1]);
            $unitPrice = trim($fields[2]);
            $quantity = trim($fields[3]);
            $totalPrice = trim($fields[4]);

            DataModel::create([
                'Date' => $date,
                'SKU' => $sku,
                'Unit Price' => $unitPrice,
                'Quantity' => $quantity,
                'Total Price' => $totalPrice
            ]);
        }

        return response()->json(['message' => 'Data stored successfully']);
    }

    public function create(Request $request)
    {
        // logger("ghfy");
        // exit();
        // Validate the request data
        $validatedData = array(
            'Date' => "2019-04-01",
            'SKU' => 'Cake Fudge',
            'Unit Price' => 100,
            'Quantity' => 4,
            'Total Price' => 400
        );

        // Create a new item
        $item = DataModel::create($validatedData);

        return response()->json([
            'message' => 'Item created successfully',
            'item' => $item
        ]);
    }

    public function totalSales()
    {
        $sales = DataModel::groupBy('SKU')
            ->select('SKU', DB::raw('SUM(Quantity) as total_quantity'), DB::raw('SUM(`Unit Price` * Quantity) as total_sales'))
            ->get();

        $result = [];
        foreach ($sales as $sale) {
            $result[] = [
                'item' => $sale->SKU,
                'quantity' => $sale->total_quantity,
                'totalsales' => $sale->total_sales,
            ];
        }

        return response()->json($result);
    }

    public function totalSalesByMonth()
    {
        $salesByMonth = DataModel::select(DB::raw('MONTH(date) as month'), DB::raw('SUM(`Unit Price` * quantity) as total_sales'))
            ->groupBy('month')
            ->get();

        $result = [];

        foreach ($salesByMonth as $sale) {
            $month = date('F', mktime(0, 0, 0, $sale->month, 1));
            $result[$month] = $sale->total_sales;
        }

        return response()->json($result);
    }

    public function popularItemOfMonth(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
        ]);

        $month = $request->input('month');

        $popularItem = DataModel::select('SKU', DB::raw('SUM(Quantity) as total_quantity'))
            ->whereMonth('date', $month)
            ->groupBy('SKU')
            ->orderByDesc('total_quantity')
            ->first();

        return response()->json($popularItem);
    }

    public function mostRevenueByMonth(Request $request)
    {
        $month = $request->query('month');

        $query = DataModel::select('SKU', DB::raw('SUM(`Unit Price` * quantity) as total_revenue'))
            ->groupBy('SKU')
            ->orderByDesc('total_revenue');

        if ($month) {
            $query->whereMonth('date', $month);
        }

        $mostRevenueItem = $query->first();

        return response()->json($mostRevenueItem);
    }
}
