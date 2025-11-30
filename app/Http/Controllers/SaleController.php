<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Warehouse;
use App\Models\StockLevel;
use App\Models\Table;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::with(['product.category', 'warehouse', 'client', 'tableBooking'])
            ->orderByDesc('sold_at')
            ->paginate(20);

        $products = Product::with('category')
            ->orderBy('name')
            ->get();

        $categories = Category::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();

        $salesData = $sales->map(function (Sale $sale) {
            return [
                'id' => $sale->id,
                'product_id' => $sale->product_id,
                'warehouse_id' => $sale->warehouse_id,
                'client_id' => $sale->client_id,
                'quantity' => $sale->quantity,
                'payment_method' => $sale->payment_method,
                'sold_at' => $sale->sold_at?->format('Y-m-d\TH:i'),
                'table_booking_id' => $sale->table_booking_id,
            ];
        });

        return view('sales.index', [
            'sales' => $sales,
            'salesData' => $salesData,
            'products' => $products,
            'categories' => $categories,
            'warehouses' => $warehouses,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'table_booking_id' => ['nullable', 'exists:table_bookings,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'payment_method' => ['required', 'in:cash,card'],
            'sold_at' => ['required', 'date'],
        ]);

        $product = Product::findOrFail($validated['product_id']);

        // Проверяем остатки на складе
        $stockLevel = StockLevel::where('warehouse_id', $validated['warehouse_id'])
            ->where('product_id', $validated['product_id'])
            ->first();

        if (!$stockLevel || $stockLevel->quantity < $validated['quantity']) {
            return redirect()->back()
                ->withErrors(['quantity' => 'Недостаточно товара на выбранном складе для проведения продажи.'])
                ->withInput();
        }

        $total = $product->price * $validated['quantity'];

        \DB::transaction(function () use ($validated, $total, $stockLevel) {
            // Создаём продажу
            Sale::create([
                'product_id' => $validated['product_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'client_id' => $validated['client_id'] ?? null,
                'table_booking_id' => $validated['table_booking_id'] ?? null,
                'quantity' => $validated['quantity'],
                'payment_method' => $validated['payment_method'],
                'sold_at' => Carbon::parse($validated['sold_at']),
                'total' => $total,
            ]);

            // Списываем со склада
            $stockLevel->quantity -= $validated['quantity'];
            $stockLevel->last_updated = now();

            if ($stockLevel->quantity <= 0) {
                $stockLevel->delete();
            } else {
                $stockLevel->save();
            }
        });

        // Если продажа привязана к бронированию стола, остаёмся на странице столов
        if (!empty($validated['table_booking_id'])) {
            $booking = Table::find($validated['table_booking_id']);
            $date = $booking?->booking_date?->format('Y-m-d') ?? today()->format('Y-m-d');

            return redirect()
                ->route('tables.index', ['date' => $date])
                ->with('success', 'Продажа успешно добавлена.')
                ->with('open_sales_booking_id', $validated['table_booking_id']);
        }

        return redirect()->route('sales.index')->with('success', 'Продажа успешно добавлена.');
    }

    public function update(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'table_booking_id' => ['nullable', 'exists:table_bookings,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'payment_method' => ['required', 'in:cash,card'],
            'sold_at' => ['required', 'date'],
        ]);

        $product = Product::findOrFail($validated['product_id']);

        // При обновлении пересчитываем списание со склада,
        // возвращаем старое количество и списываем новое.
        \DB::transaction(function () use ($sale, $validated, $product) {
            // Возвращаем предыдущее количество на старый склад/товар
            $oldStock = StockLevel::firstOrNew([
                'warehouse_id' => $sale->warehouse_id,
                'product_id' => $sale->product_id,
            ]);
            $oldStock->quantity = ($oldStock->quantity ?? 0) + $sale->quantity;
            $oldStock->last_updated = now();
            $oldStock->save();

            // Проверяем, что на новом складе хватает товара
            $newStock = StockLevel::where('warehouse_id', $validated['warehouse_id'])
                ->where('product_id', $validated['product_id'])
                ->first();

            if (!$newStock || $newStock->quantity < $validated['quantity']) {
                throw new \RuntimeException('Недостаточно товара на выбранном складе для изменения продажи.');
            }

            // Списываем с нового склада
            $newStock->quantity -= $validated['quantity'];
            $newStock->last_updated = now();

            if ($newStock->quantity <= 0) {
                $newStock->delete();
            } else {
                $newStock->save();
            }

            // Обновляем продажу
            $total = $product->price * $validated['quantity'];

            $sale->update([
                'product_id' => $validated['product_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'client_id' => $validated['client_id'] ?? null,
                'table_booking_id' => $validated['table_booking_id'] ?? null,
                'quantity' => $validated['quantity'],
                'payment_method' => $validated['payment_method'],
                'sold_at' => Carbon::parse($validated['sold_at']),
                'total' => $total,
            ]);
        });

        return redirect()->route('sales.index')->with('success', 'Продажа обновлена.');
    }

    public function destroy(Sale $sale)
    {
        \DB::transaction(function () use ($sale) {
            // Возвращаем товар на склад при удалении продажи
            $stockLevel = StockLevel::firstOrNew([
                'warehouse_id' => $sale->warehouse_id,
                'product_id' => $sale->product_id,
            ]);

            $stockLevel->quantity = ($stockLevel->quantity ?? 0) + $sale->quantity;
            $stockLevel->last_updated = now();
            $stockLevel->save();

            $sale->delete();
        });

        return redirect()->route('sales.index')->with('success', 'Продажа удалена.');
    }

    public function searchClients(Request $request)
    {
        $query = $request->get('q', '');

        if (mb_strlen($query) < 2) {
            return response()->json([]);
        }

        $clients = Client::query()
            ->where('name', 'like', '%' . $query . '%')
            ->orWhere('phone', 'like', '%' . $query . '%')
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'phone']);

        return response()->json($clients);
    }
}


