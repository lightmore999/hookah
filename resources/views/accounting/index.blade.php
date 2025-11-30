<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Бухгалтерия
            </h2>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span class="text-gray-700 font-medium">= <span id="cash-balance">0</span> руб.</span>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <!-- Навигация по неделям -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4">
                <div class="p-4 flex justify-between items-center">
                    <a href="{{ route('accounting.index', ['week_start' => $prevWeek]) }}" 
                       class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md">
                        ← Предыдущая неделя
                    </a>
                    <span class="text-gray-700 font-medium">
                        {{ $weekDates[0]->format('d.m.Y') }} - {{ $weekDates[6]->format('d.m.Y') }}
                    </span>
                    <a href="{{ route('accounting.index', ['week_start' => $nextWeek]) }}" 
                       class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md">
                        Следующая неделя →
                    </a>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th rowspan="2" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r">Дата</th>
                                <th colspan="3" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-r">
                                    <div class="flex items-center justify-center gap-1">
                                        Касса
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </th>
                                <th rowspan="2" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-r">
                                    <div class="flex items-center justify-center gap-1">
                                        Выручка
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </th>
                                <th colspan="5" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-r">Столы</th>
                                <th colspan="2" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-r">Продажи</th>
                                <th rowspan="2" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-r">
                                    <div class="flex items-center justify-center gap-1">
                                        Средний чек
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </th>
                                <th rowspan="2" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-r">Расходы</th>
                                <th colspan="2" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-r">Долги</th>
                                <th colspan="2" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Размен</th>
                            </tr>
                            <tr>
                                <th class="px-4 py-2 text-xs font-medium text-gray-500 uppercase tracking-wider border-r">всего</th>
                                <th class="px-4 py-2 text-xs font-medium text-gray-500 uppercase tracking-wider border-r">наличными</th>
                                <th class="px-4 py-2 text-xs font-medium text-gray-500 uppercase tracking-wider border-r">по карте</th>
                                <th class="px-4 py-2 text-xs font-medium text-gray-500 uppercase tracking-wider border-r">
                                    <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </th>
                                <th class="px-4 py-2 text-xs font-medium text-gray-500 uppercase tracking-wider border-r">
                                    <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </th>
                                <th class="px-4 py-2 text-xs font-medium text-gray-500 uppercase tracking-wider border-r">кальяны</th>
                                <th class="px-4 py-2 text-xs font-medium text-gray-500 uppercase tracking-wider border-r">чаевые</th>
                                <th class="px-4 py-2 text-xs font-medium text-gray-500 uppercase tracking-wider border-r">скидка</th>
                                <th class="px-4 py-2 text-xs font-medium text-gray-500 uppercase tracking-wider border-r">за столами</th>
                                <th class="px-4 py-2 text-xs font-medium text-gray-500 uppercase tracking-wider border-r">магазин</th>
                                <th class="px-4 py-2 text-xs font-medium text-gray-500 uppercase tracking-wider border-r">долг</th>
                                <th class="px-4 py-2 text-xs font-medium text-gray-500 uppercase tracking-wider border-r">возврат</th>
                                <th class="px-4 py-2 text-xs font-medium text-gray-500 uppercase tracking-wider border-r">со вчера</th>
                                <th class="px-4 py-2 text-xs font-medium text-gray-500 uppercase tracking-wider">на завтра</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($weekDates as $date)
                                @php
                                    $dateKey = $date->format('Y-m-d');
                                    $data = $dailyData[$dateKey] ?? null;
                                    $dayName = $date->locale('ru')->dayName;
                                    $dayName = ucfirst($dayName);
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 border-r">
                                        {{ $dayName }}, {{ $date->format('d') }} {{ $date->locale('ru')->monthName }} {{ $date->format('Y') }} г.
                                    </td>
                                    @if($data)
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-center border-r">
                                            {{ number_format($data['cash_register']['total'], 2, '.', ' ') }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-center border-r">
                                            {{ number_format($data['cash_register']['cash'], 2, '.', ' ') }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-center border-r">
                                            {{ number_format($data['cash_register']['card'], 2, '.', ' ') }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-center border-r">
                                            {{ number_format($data['revenue'], 2, '.', ' ') }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-center border-r">
                                            {{ $data['tables']['count'] }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-center border-r">
                                            {{ number_format($data['revenue'], 2, '.', ' ') }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-center border-r">
                                            {{ number_format($data['tables']['hookahs'], 2, '.', ' ') }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-center border-r">
                                            {{ number_format($data['tables']['tips'], 2, '.', ' ') }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-center border-r">
                                            {{ number_format($data['tables']['discount'], 2, '.', ' ') }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-center border-r">
                                            {{ number_format($data['sales']['at_tables'], 2, '.', ' ') }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-center border-r">
                                            {{ number_format($data['sales']['store'], 2, '.', ' ') }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-center border-r">
                                            {{ number_format($data['average_check'], 2, '.', ' ') }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-center border-r">
                                            {{ $data['expenses'] > 0 ? number_format($data['expenses'], 2, '.', ' ') : '-' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-center border-r">
                                            {{ $data['debts']['debt'] > 0 ? number_format($data['debts']['debt'], 2, '.', ' ') : '-' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-center border-r">
                                            {{ $data['debts']['return'] > 0 ? number_format($data['debts']['return'], 2, '.', ' ') : '-' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-center border-r">
                                            {{ $data['change']['from_yesterday'] > 0 ? number_format($data['change']['from_yesterday'], 2, '.', ' ') : '-' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-center">
                                            {{ $data['change']['for_tomorrow'] > 0 ? number_format($data['change']['for_tomorrow'], 2, '.', ' ') : '-' }}
                                        </td>
                                    @else
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 text-center border-r">0</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 text-center border-r">0</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 text-center border-r">0</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 text-center border-r">0</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 text-center border-r">-</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 text-center border-r">-</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 text-center border-r">-</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 text-center border-r">-</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 text-center border-r">-</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 text-center border-r">-</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 text-center border-r">-</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 text-center border-r">0</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 text-center border-r">-</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 text-center border-r">-</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 text-center border-r">-</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 text-center border-r">-</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 text-center">-</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
