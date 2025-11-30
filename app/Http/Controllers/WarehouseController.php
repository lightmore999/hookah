<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Purchase;
use App\Models\StockLevel;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(): View
    {
        $warehouses = Warehouse::latest()->get();
        $purchases = Purchase::with(['product', 'warehouse'])->latest()->get();
        $products = Product::all();

        $warehousesData = $warehouses->map(function (Warehouse $warehouse) {
            return [
                'id' => $warehouse->id,
                'name' => $warehouse->name,
            ];
        })->values();

        $productsData = $products->map(function (Product $product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
            ];
        })->values();

        return view('warehouses.index', compact('warehouses', 'purchases', 'products', 'warehousesData', 'productsData'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(): View
    {
        return view('warehouses.create');
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
            'name' => 'required|string|max:255',
        ]);

        Warehouse::create($validated);

        return redirect()->route('warehouses.index')
            ->with('success', 'Склад успешно добавлен.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Warehouse  $warehouse
     * @return \Illuminate\Http\Response
     */
    public function show(Warehouse $warehouse): View
    {
        $stockLevels = $warehouse->stockLevels()->with('product')->get();
        $otherWarehouses = Warehouse::where('id', '!=', $warehouse->id)->get();
        return view('warehouses.show', compact('warehouse', 'stockLevels', 'otherWarehouses'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Warehouse  $warehouse
     * @return \Illuminate\Http\Response
     */
    public function edit(Warehouse $warehouse): View
    {
        return view('warehouses.edit', compact('warehouse'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Warehouse  $warehouse
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $warehouse->update($validated);

        return redirect()->route('warehouses.index')
            ->with('success', 'Склад успешно обновлен.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Warehouse  $warehouse
     * @return \Illuminate\Http\Response
     */
    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        $warehouse->delete();

        return redirect()->route('warehouses.index')
            ->with('success', 'Склад успешно удален.');
    }

    /**
     * Move stock from current warehouse to another warehouse.
     */
    public function moveStock(Request $request, Warehouse $warehouse, StockLevel $stockLevel): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'target_warehouse_id' => 'required|exists:warehouses,id',
        ]);

        if ($validated['quantity'] > $stockLevel->quantity) {
            return redirect()->back()
                ->withErrors(['quantity' => 'Недостаточно товара на складе.']);
        }

        DB::transaction(function () use ($warehouse, $stockLevel, $validated) {
            // Уменьшаем количество на текущем складе
            $stockLevel->quantity -= $validated['quantity'];
            $stockLevel->last_updated = now();
            $stockLevel->save();

            // Увеличиваем количество на целевом складе
            $targetStockLevel = StockLevel::where('warehouse_id', $validated['target_warehouse_id'])
                ->where('product_id', $stockLevel->product_id)
                ->first();

            if ($targetStockLevel) {
                $targetStockLevel->quantity += $validated['quantity'];
                $targetStockLevel->last_updated = now();
                $targetStockLevel->save();
            } else {
                StockLevel::create([
                    'warehouse_id' => $validated['target_warehouse_id'],
                    'product_id' => $stockLevel->product_id,
                    'quantity' => $validated['quantity'],
                    'last_updated' => now(),
                ]);
            }

            // Если количество на текущем складе стало 0, удаляем запись
            if ($stockLevel->quantity <= 0) {
                $stockLevel->delete();
            }
        });

        return redirect()->route('warehouses.show', $warehouse)
            ->with('success', 'Товар успешно перемещен.');
    }

    /**
     * Write off stock to work.
     */
    public function writeoffWork(Request $request, Warehouse $warehouse, StockLevel $stockLevel): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validated['quantity'] > $stockLevel->quantity) {
            return redirect()->back()
                ->withErrors(['quantity' => 'Недостаточно товара на складе.']);
        }

        DB::transaction(function () use ($stockLevel, $validated) {
            // Уменьшаем количество
            $stockLevel->quantity -= $validated['quantity'];
            $stockLevel->last_updated = now();

            if ($stockLevel->quantity <= 0) {
                $stockLevel->delete();
            } else {
                $stockLevel->save();
            }
        });

        return redirect()->route('warehouses.show', $warehouse)
            ->with('success', 'Товар успешно списан в работу.');
    }

    /**
     * Write off stock.
     */
    public function writeoff(Request $request, Warehouse $warehouse, StockLevel $stockLevel): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validated['quantity'] > $stockLevel->quantity) {
            return redirect()->back()
                ->withErrors(['quantity' => 'Недостаточно товара на складе.']);
        }

        DB::transaction(function () use ($stockLevel, $validated) {
            // Уменьшаем количество
            $stockLevel->quantity -= $validated['quantity'];
            $stockLevel->last_updated = now();

            if ($stockLevel->quantity <= 0) {
                $stockLevel->delete();
            } else {
                $stockLevel->save();
            }
        });

        return redirect()->route('warehouses.show', $warehouse)
            ->with('success', 'Товар успешно списан.');
    }
}
