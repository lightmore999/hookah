<?php

namespace App\Http\Controllers;

use App\Models\TableClosure;
use App\Models\Sale;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AccountingController extends Controller
{
    /**
     * Display the accounting page.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        // Определяем неделю (понедельник - воскресенье)
        $weekStart = $request->get('week_start', Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d'));
        $startDate = Carbon::parse($weekStart)->startOfDay();
        $endDate = $startDate->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();

        // Генерируем даты недели
        $weekDates = [];
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $weekDates[] = $currentDate->copy();
            $currentDate->addDay();
        }

        // Получаем данные о закрытых столах за неделю
        $closures = TableClosure::with(['tableBooking', 'employee'])
            ->whereBetween('closed_at', [$startDate, $endDate])
            ->get()
            ->groupBy(function ($closure) {
                return Carbon::parse($closure->closed_at)->format('Y-m-d');
            });

        // Получаем продажи за неделю
        $sales = Sale::with('product')
            ->whereBetween('sold_at', [$startDate, $endDate])
            ->get()
            ->groupBy(function ($sale) {
                return Carbon::parse($sale->sold_at)->format('Y-m-d');
            });

        // Подготавливаем данные для каждого дня недели
        $dailyData = [];
        foreach ($weekDates as $date) {
            $dateKey = $date->format('Y-m-d');
            $dayClosures = $closures->get($dateKey, collect());
            $daySales = $sales->get($dateKey, collect());

            // Касса
            $cashTotal = $dayClosures->where('payment_method', 'cash')->sum('total_amount');
            $cardTotal = $dayClosures->where('payment_method', 'card')->sum('total_amount');
            $cashRegisterTotal = $cashTotal + $cardTotal;

            // Выручка (итоговая сумма всех закрытых столов)
            $revenue = $dayClosures->sum('total_amount');

            // Столы
            $tablesCount = $dayClosures->count();
            $hookahsAmount = $dayClosures->sum('hookahs_amount');
            $tipsAmount = $dayClosures->sum('tips_amount');
            $discountAmount = $dayClosures->sum('discount_amount');

            // Продажи
            $salesAtTables = $daySales->whereNotNull('table_booking_id')->sum('total');
            $salesStore = $daySales->whereNull('table_booking_id')->sum('total');

            // Средний чек
            $averageCheck = $tablesCount > 0 ? $revenue / $tablesCount : 0;

            // Расходы (пока заглушка, нужно будет создать таблицу expenses)
            $expenses = 0;

            // Долги (пока заглушка, нужно будет создать таблицу debts)
            $debts = 0;
            $returns = 0;

            // Размен (пока заглушка, нужно будет создать таблицу change)
            $changeFromYesterday = 0;
            $changeForTomorrow = 0;

            $dailyData[$dateKey] = [
                'date' => $date,
                'cash_register' => [
                    'total' => $cashRegisterTotal,
                    'cash' => $cashTotal,
                    'card' => $cardTotal,
                ],
                'revenue' => $revenue,
                'tables' => [
                    'count' => $tablesCount,
                    'hookahs' => $hookahsAmount,
                    'tips' => $tipsAmount,
                    'discount' => $discountAmount,
                ],
                'sales' => [
                    'at_tables' => $salesAtTables,
                    'store' => $salesStore,
                ],
                'average_check' => $averageCheck,
                'expenses' => $expenses,
                'debts' => [
                    'debt' => $debts,
                    'return' => $returns,
                ],
                'change' => [
                    'from_yesterday' => $changeFromYesterday,
                    'for_tomorrow' => $changeForTomorrow,
                ],
            ];
        }

        return view('accounting.index', [
            'weekDates' => $weekDates,
            'dailyData' => $dailyData,
            'weekStart' => $weekStart,
            'prevWeek' => $startDate->copy()->subWeek()->format('Y-m-d'),
            'nextWeek' => $startDate->copy()->addWeek()->format('Y-m-d'),
        ]);
    }
}
