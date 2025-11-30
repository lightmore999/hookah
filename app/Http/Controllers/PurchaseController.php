<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\StockLevel;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(): View
    {
        $products = Product::all();
        $warehouses = Warehouse::all();
        return view('purchases.create', compact('products', 'warehouses'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
        ]);

        // Автоматически устанавливаем текущую дату и время
        $validated['purchase_date'] = now();

        DB::transaction(function () use ($validated) {
            // Создаем закупку
            $purchase = Purchase::create($validated);

            // Проверяем есть ли на складе такой товар
            $stockLevel = StockLevel::where('warehouse_id', $validated['warehouse_id'])
                ->where('product_id', $validated['product_id'])
                ->first();

            if ($stockLevel) {
                // Если есть - добавляем к существующему количеству
                $stockLevel->quantity += $validated['quantity'];
                $stockLevel->last_updated = now();
                $stockLevel->save();
            } else {
                // Если нет - создаем новый stock_level
                StockLevel::create([
                    'warehouse_id' => $validated['warehouse_id'],
                    'product_id' => $validated['product_id'],
                    'quantity' => $validated['quantity'],
                    'last_updated' => now(),
                ]);
            }
        });

        return redirect()->route('warehouses.index')
            ->with('success', 'Закупка успешно добавлена.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function destroy(Purchase $purchase): RedirectResponse
    {
        DB::transaction(function () use ($purchase) {
            // Уменьшаем количество товара на складе
            $stockLevel = StockLevel::where('warehouse_id', $purchase->warehouse_id)
                ->where('product_id', $purchase->product_id)
                ->first();

            if ($stockLevel) {
                $stockLevel->quantity -= $purchase->quantity;
                if ($stockLevel->quantity < 0) {
                    $stockLevel->quantity = 0;
                }
                $stockLevel->last_updated = now();
                $stockLevel->save();
            }

            // Удаляем закупку
            $purchase->delete();
        });

        return redirect()->route('warehouses.index')
            ->with('success', 'Закупка успешно удалена.');
    }
}
