<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\Client;
use App\Models\Product;
use App\Models\Category;
use App\Models\Warehouse;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class TableController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): View
    {
        // Определяем выбранную дату
        $selectedDate = $request->get('date', today()->format('Y-m-d'));
        $date = Carbon::parse($selectedDate);

        // Получаем бронирования на выбранную дату
        $bookings = Table::with('client')->whereDate('booking_date', $date->format('Y-m-d'))->get();
        $bookingsData = $bookings->map(function (Table $booking) {
            return [
                'id' => $booking->id,
                'table_number' => $booking->table_number,
                'booking_date' => $booking->booking_date->format('Y-m-d'),
                'booking_time' => \Carbon\Carbon::parse($booking->booking_time)->format('H:i'),
                'duration' => $booking->duration,
                'guests_count' => $booking->guests_count,
                'guest_name' => $booking->guest_name,
                'phone' => $booking->phone,
                'comment' => $booking->comment,
                'client_id' => $booking->client_id,
                'client' => $booking->client ? [
                    'id' => $booking->client->id,
                    'name' => $booking->client->name,
                    'phone' => $booking->client->phone,
                ] : null,
            ];
        })->values();

        // Продажи, привязанные к этим бронированиям
        $sales = Sale::with('product')
            ->whereIn('table_booking_id', $bookings->pluck('id'))
            ->get();

        $salesByBooking = $sales->groupBy('table_booking_id')->map(function ($group) {
            return $group->map(function (Sale $sale) {
                return [
                    'id' => $sale->id,
                    'product_name' => $sale->product->name ?? 'Товар удален',
                    'quantity' => $sale->quantity,
                    'total' => $sale->total,
                ];
            })->values();
        });

        // Справочники для формы продаж по столу
        $products = Product::with('category')->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();

        // Определяем активную кнопку фильтра
        $activeFilter = 'custom';
        if ($date->isToday()) {
            $activeFilter = 'today';
        } elseif ($date->isYesterday()) {
            $activeFilter = 'yesterday';
        } elseif ($date->isTomorrow()) {
            $activeFilter = 'tomorrow';
        }

        // Список столов
        $tableNumbers = ['стол 1', 'стол 2', 'стол 3', 'стол 4', 'Барная стойка', 'стол 6', 'стол 7'];

        return view('tables.index', [
            'bookings' => $bookings,
            'date' => $date,
            'activeFilter' => $activeFilter,
            'tableNumbers' => $tableNumbers,
            'selectedDate' => $selectedDate,
            'bookingsData' => $bookingsData,
            'salesByBooking' => $salesByBooking,
            'products' => $products,
            'categories' => $categories,
            'warehouses' => $warehouses,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request): View
    {
        $tableNumbers = ['стол 1', 'стол 2', 'стол 3', 'стол 4', 'Барная стойка', 'стол 6', 'стол 7'];
        $selectedDate = $request->get('date', today()->format('Y-m-d'));
        $selectedTable = $request->get('table');
        $selectedTime = $request->get('time');

        return view('tables.create', compact('tableNumbers', 'selectedDate', 'selectedTable', 'selectedTime'));
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
            'table_number' => 'required|string',
            'booking_date' => 'required|date',
            'booking_time' => 'required',
            'duration' => 'required|integer|min:30',
            'guests_count' => 'required|integer|min:1',
            'comment' => 'nullable|string',
            'phone' => 'nullable|string',
            'guest_name' => 'nullable|string',
            'client_id' => 'nullable|exists:clients,id',
        ]);

        // Если выбран клиент, используем его данные
        if (isset($validated['client_id']) && $validated['client_id']) {
            $client = Client::find($validated['client_id']);
            if ($client) {
                $validated['guest_name'] = $client->name;
                $validated['phone'] = $client->phone;
            }
        }

        // Устанавливаем статус по умолчанию
        $validated['status'] = 'not_opened';

        // Проверка конфликтов времени
        $bookingStart = Carbon::parse($validated['booking_date'] . ' ' . $validated['booking_time']);
        $bookingEnd = $bookingStart->copy()->addMinutes($validated['duration']);

        // Проверяем существующие бронирования на том же столе и дате
        $conflictingBookings = Table::where('table_number', $validated['table_number'])
            ->whereDate('booking_date', $validated['booking_date'])
            ->where('id', '!=', $request->input('id', 0)) // Исключаем текущее бронирование при редактировании
            ->get()
            ->filter(function ($booking) use ($bookingStart, $bookingEnd) {
                $existingStart = Carbon::parse($booking->booking_date->format('Y-m-d') . ' ' . $booking->booking_time);
                $existingEnd = $existingStart->copy()->addMinutes($booking->duration);
                
                // Проверяем пересечение временных интервалов
                return ($bookingStart < $existingEnd && $bookingEnd > $existingStart);
            });

        if ($conflictingBookings->count() > 0) {
            return redirect()->back()
                ->withErrors(['booking_time' => 'Этот стол уже забронирован на указанное время. Выберите другое время или другой стол.'])
                ->withInput();
        }

        Table::create($validated);

        return redirect()->route('tables.index', ['date' => $validated['booking_date']])
            ->with('success', 'Бронирование успешно добавлено.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Table  $table
     * @return \Illuminate\Http\Response
     */
    public function edit(Table $table): View
    {
        $table->load('client');
        $tableNumbers = ['стол 1', 'стол 2', 'стол 3', 'стол 4', 'Барная стойка', 'стол 6', 'стол 7'];
        return view('tables.edit', compact('table', 'tableNumbers'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Table  $table
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Table $table): RedirectResponse
    {
        $validated = $request->validate([
            'table_number' => 'required|string',
            'booking_date' => 'required|date',
            'booking_time' => 'required',
            'duration' => 'required|integer|min:30',
            'guests_count' => 'required|integer|min:1',
            'comment' => 'nullable|string',
            'phone' => 'nullable|string',
            'guest_name' => 'nullable|string',
            'client_id' => 'nullable|exists:clients,id',
        ]);

        // Если выбран клиент, используем его данные
        if (isset($validated['client_id']) && $validated['client_id']) {
            $client = Client::find($validated['client_id']);
            if ($client) {
                $validated['guest_name'] = $client->name;
                $validated['phone'] = $client->phone;
            }
        }

        // Проверка конфликтов времени (исключая текущее бронирование)
        $bookingStart = Carbon::parse($validated['booking_date'] . ' ' . $validated['booking_time']);
        $bookingEnd = $bookingStart->copy()->addMinutes($validated['duration']);

        // Проверяем существующие бронирования на том же столе и дате
        $conflictingBookings = Table::where('table_number', $validated['table_number'])
            ->whereDate('booking_date', $validated['booking_date'])
            ->where('id', '!=', $table->id) // Исключаем текущее бронирование
            ->get()
            ->filter(function ($booking) use ($bookingStart, $bookingEnd) {
                $existingStart = Carbon::parse($booking->booking_date->format('Y-m-d') . ' ' . $booking->booking_time);
                $existingEnd = $existingStart->copy()->addMinutes($booking->duration);
                
                // Проверяем пересечение временных интервалов
                return ($bookingStart < $existingEnd && $bookingEnd > $existingStart);
            });

        if ($conflictingBookings->count() > 0) {
            return redirect()->back()
                ->withErrors(['booking_time' => 'Этот стол уже забронирован на указанное время. Выберите другое время или другой стол.'])
                ->withInput();
        }

        $table->update($validated);

        return redirect()->route('tables.index', ['date' => $validated['booking_date']])
            ->with('success', 'Бронирование успешно обновлено.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Table  $table
     * @return \Illuminate\Http\Response
     */
    public function destroy(Table $table): RedirectResponse
    {
        $bookingDate = $table->booking_date->format('Y-m-d');
        $table->delete();

        return redirect()->route('tables.index', ['date' => $bookingDate])
            ->with('success', 'Бронирование успешно удалено.');
    }

    /**
     * Search clients by name or phone.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchClients(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $clients = Client::where('name', 'like', '%' . $query . '%')
            ->orWhere('phone', 'like', '%' . $query . '%')
            ->limit(10)
            ->get(['id', 'name', 'phone']);

        return response()->json($clients);
    }

    /**
     * Update table status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Table  $table
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, Table $table): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:not_opened,opened,closed',
        ]);

        $table->update(['status' => $validated['status']]);

        return redirect()->route('tables.index', ['date' => $table->booking_date->format('Y-m-d')])
            ->with('success', 'Статус стола успешно обновлен.');
    }
}
