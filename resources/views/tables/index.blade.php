<x-app-layout x-data="tablesPageData()">
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Столы
            </h2>
        </div>
    </x-slot>

    <div class="min-h-screen">
    <div class="py-6">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8" style="position: relative;">
            <!-- Кнопки фильтрации и добавления -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4">
                <div class="p-4 flex justify-between items-center">
                    <div class="flex gap-2">
                        <a href="{{ route('tables.index', ['date' => \Carbon\Carbon::yesterday()->format('Y-m-d')]) }}" 
                           class="px-4 py-2 rounded {{ $activeFilter === 'yesterday' ? 'bg-gray-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            Вчера
                        </a>
                        <a href="{{ route('tables.index', ['date' => today()->format('Y-m-d')]) }}" 
                           class="px-4 py-2 rounded {{ $activeFilter === 'today' ? 'bg-gray-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            Сегодня
                        </a>
                        <a href="{{ route('tables.index', ['date' => \Carbon\Carbon::tomorrow()->format('Y-m-d')]) }}" 
                           class="px-4 py-2 rounded {{ $activeFilter === 'tomorrow' ? 'bg-gray-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            Завтра
                        </a>
                        <div class="relative">
                            <input type="date" 
                                   value="{{ $selectedDate }}" 
                                   onchange="window.location.href='{{ route('tables.index') }}?date=' + this.value"
                                   class="px-4 py-2 rounded border border-gray-300">
                        </div>
                        @if($activeFilter === 'custom')
                            <span class="px-4 py-2 text-gray-700">{{ $date->format('d.m.Y') }}</span>
                        @endif
                    </div>
                    <button type="button" @click="showModal = true" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Добавить стол
                    </button>
                </div>
            </div>

            <!-- Сетка столов -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse">
                        <thead>
                            <tr>
                                <th class="border border-gray-300 bg-gray-100 p-2 sticky left-0 z-10 min-w-[100px]">Время</th>
                                @foreach($tableNumbers as $tableNum)
                                    <th class="border border-gray-300 bg-gray-100 p-2 min-w-[150px]">{{ $tableNum }}</th>
                                @endforeach
                                <th class="border border-gray-300 bg-gray-100 p-2 sticky right-0 z-10 min-w-[100px]">Время</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                // Генерируем временные слоты с 14:00 до 03:30 (следующий день)
                                $timeSlots = [];
                                $startTime = \Carbon\Carbon::parse('14:00');
                                $endTime = \Carbon\Carbon::parse('03:30')->addDay();
                                
                                $current = $startTime->copy();
                                while ($current <= $endTime) {
                                    $timeSlots[] = $current->copy();
                                    $current->addMinutes(30);
                                }
                            @endphp
                            @php
                                // Предварительно вычисляем, какие бронирования начинаются в каждом слоте
                                $bookingStarts = [];
                                $bookingSpans = [];
                                foreach ($bookings as $booking) {
                                    // booking_time уже является строкой времени (H:i:s), не нужно парсить как datetime
                                    $startSlotKey = \Carbon\Carbon::parse($booking->booking_time)->format('H:i');
                                    
                                    // Находим индекс слота, где начинается бронирование
                                    $startSlotIndex = null;
                                    foreach ($timeSlots as $idx => $slot) {
                                        if ($slot->format('H:i') === $startSlotKey) {
                                            $startSlotIndex = $idx;
                                            break;
                                        }
                                    }
                                    
                                    if ($startSlotIndex !== null) {
                                        if (!isset($bookingStarts[$booking->table_number])) {
                                            $bookingStarts[$booking->table_number] = [];
                                        }
                                        $bookingStarts[$booking->table_number][$startSlotIndex] = $booking;
                                        $bookingSpans[$booking->id] = max(1, ceil($booking->duration / 30));
                                    }
                                }
                                
                                // Отслеживаем активные rowspan для каждого стола
                                $activeRowspans = [];
                                foreach ($tableNumbers as $tableNum) {
                                    $activeRowspans[$tableNum] = [];
                                }
                            @endphp
                            @foreach($timeSlots as $index => $timeSlot)
                                @php
                                    $isHourMark = $timeSlot->format('i') === '00';
                                @endphp
                                <tr class="{{ $isHourMark ? 'bg-gray-50' : '' }}">
                                    <td class="border border-gray-300 p-1 text-xs text-center sticky left-0 bg-white z-10 {{ $isHourMark ? 'bg-gray-50' : '' }}">
                                        {{ $timeSlot->format('H:i') }}
                                    </td>
                                    @foreach($tableNumbers as $tableNum)
                                        @php
                                            // Проверяем, не занята ли ячейка активным rowspan
                                            $isOccupied = false;
                                            if (isset($activeRowspans[$tableNum][$index])) {
                                                $isOccupied = true;
                                            }
                                            
                                            // Проверяем, начинается ли новое бронирование в этом слоте
                                            $cellBooking = $bookingStarts[$tableNum][$index] ?? null;
                                            $rowspan = 1;
                                            if ($cellBooking) {
                                                $rowspan = $bookingSpans[$cellBooking->id] ?? 1;
                                                // Помечаем следующие ячейки как занятые
                                                for ($i = 1; $i < $rowspan; $i++) {
                                                    $activeRowspans[$tableNum][$index + $i] = true;
                                                }
                                            }
                                        @endphp
                                        @if($isOccupied && !$cellBooking)
                                            {{-- Пропускаем ячейку, так как она занята rowspan --}}
                                        @else
                                            <td class="border border-gray-300 p-1 min-h-[30px] relative {{ $isHourMark ? 'bg-gray-50' : '' }}" 
                                                @if($cellBooking && $rowspan > 1) rowspan="{{ $rowspan }}" @endif>
                                                @if($cellBooking)
                                                    <div class="bg-blue-100 border border-blue-300 rounded p-1 text-xs h-full flex flex-col relative">
                                                        <div class="absolute top-1 right-1">
                                                            <div class="relative inline-block" @click.away="tableMenuOpen = null">
                                                                <button type="button" 
                                                                        @click.stop="tableMenuOpen = tableMenuOpen === {{ $cellBooking->id }} ? null : {{ $cellBooking->id }}"
                                                                        class="text-[12px] text-gray-600 hover:text-gray-900 font-bold cursor-pointer leading-none">
                                                                    ⋮
                                                                </button>
                                                                <div x-show="tableMenuOpen === {{ $cellBooking->id }}"
                                                                     x-cloak
                                                                     @click.stop
                                                                     x-transition:enter="transition ease-out duration-100"
                                                                     x-transition:enter-start="opacity-0 scale-95"
                                                                     x-transition:enter-end="opacity-100 scale-100"
                                                                     x-transition:leave="transition ease-in duration-75"
                                                                     x-transition:leave-start="opacity-100 scale-100"
                                                                     x-transition:leave-end="opacity-0 scale-95"
                                                                     class="absolute right-0 top-full mt-1 w-32 bg-white border border-gray-300 rounded-md shadow-lg z-[10001] py-1">
                                                                    <button type="button" 
                                                                            @click="
                                                                                editTable = bookingsData.find(b => b.id === {{ $cellBooking->id }});
                                                                                showEditModal = true;
                                                                                tableMenuOpen = null;
                                                                            " 
                                                                            class="w-full text-left px-3 py-1 text-[10px] text-blue-600 hover:bg-blue-50">
                                                                        Изменить
                                                                    </button>
                                                                    <form action="{{ route('tables.destroy', $cellBooking) }}" 
                                                                          method="POST" 
                                                                          class="inline w-full"
                                                                          onsubmit="return confirm('Удалить бронирование?');">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" 
                                                                                class="w-full text-left px-3 py-1 text-[10px] text-red-600 hover:bg-red-50">
                                                                            Удалить
                                                                        </button>
                                                                    </form>
                                                                    <button type="button"
                                                                            @click="
                                                                                openHookahsForBooking({{ $cellBooking->id }});
                                                                                tableMenuOpen = null;
                                                                            "
                                                                            class="w-full text-left px-3 py-1 text-[10px] text-gray-600 hover:bg-gray-50">
                                                                        Кальяны
                                                                    </button>
                                                                    <button type="button"
                                                                            @click="
                                                                                openSalesForBooking({{ $cellBooking->id }});
                                                                                tableMenuOpen = null;
                                                                            "
                                                                            class="w-full text-left px-3 py-1 text-[10px] text-green-600 hover:bg-green-50">
                                                                        Продажи
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="flex-1"></div>
                                                        <div class="text-center mt-auto mb-1">
                                                            @if($cellBooking->guest_name)
                                                                <div class="text-[10px] font-medium">{{ $cellBooking->guest_name }}</div>
                                                            @endif
                                                            @if($cellBooking->phone)
                                                                <div class="text-[10px] text-gray-600">{{ $cellBooking->phone }}</div>
                                                            @endif
                                                        </div>
                                                        @if($cellBooking->comment)
                                                            <div class="mt-1 relative inline-block group">
                                                                <span class="text-blue-600 cursor-help underline text-[10px]">💬</span>
                                                                <div class="hidden group-hover:block absolute bottom-full left-0 mb-2 p-2 bg-gray-800 text-white text-xs rounded shadow-lg z-[10000] max-w-xs whitespace-normal break-words">
                                                                    {{ $cellBooking->comment }}
                                                                    <div class="absolute top-full left-4 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-800"></div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                        @if($cellBooking->status === 'not_opened')
                                                            <form action="{{ route('tables.update.status', $cellBooking) }}" method="POST" class="mt-1">
                                                                @csrf
                                                                @method('POST')
                                                                <input type="hidden" name="status" value="opened">
                                                                <button type="submit" class="w-full text-[10px] bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded">Открыть стол</button>
                                                            </form>
                                                        @elseif($cellBooking->status === 'opened')
                                                            <button type="button" 
                                                                    @click="openCloseTableModal({{ $cellBooking->id }})"
                                                                    class="w-full text-[10px] bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded">
                                                                Закрыть стол
                                                            </button>
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
                                        @endif
                                    @endforeach
                                    <td class="border border-gray-300 p-1 text-xs text-center sticky right-0 bg-white z-10 {{ $isHourMark ? 'bg-gray-50' : '' }}">
                                        {{ $timeSlot->format('H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th class="border border-gray-300 bg-gray-100 p-2 sticky left-0 z-10">Время</th>
                                @foreach($tableNumbers as $tableNum)
                                    <th class="border border-gray-300 bg-gray-100 p-2">{{ $tableNum }}</th>
                                @endforeach
                                <th class="border border-gray-300 bg-gray-100 p-2 sticky right-0 z-10">Время</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно создания бронирования -->
    <div x-show="showModal" 
         x-cloak 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" 
         style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999;" 
         @click.away="showModal = false"
         @keydown.escape.window="showModal = false">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white" style="position: relative; z-index: 10000; margin: 5rem auto;">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Добавить бронирование стола</h3>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form method="POST" action="{{ route('tables.store') }}" class="space-y-4" x-data="{ selectedClient: null, selectedClientId: null, clientSearch: '', showClientResults: false, clients: [] }">
                @csrf

                <div>
                    <x-input-label for="client_search" value="Выбрать клиента" />
                    <div class="relative">
                        <input type="text" 
                               x-model="clientSearch"
                               @input.debounce.300ms="
                                   if (clientSearch.length >= 2) {
                                       fetch('{{ route('tables.search.clients') }}?q=' + encodeURIComponent(clientSearch))
                                           .then(response => response.json())
                                           .then(data => { clients = data; showClientResults = true; });
                                   } else {
                                       clients = [];
                                       showClientResults = false;
                                   }
                               "
                               @click.away="showClientResults = false"
                               placeholder="Поиск по имени или телефону..."
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none" />
                        <input type="hidden" name="client_id" x-model="selectedClientId">
                        <div x-show="selectedClient" class="mt-2 flex items-center gap-2 p-2 bg-blue-50 rounded">
                            <span x-text="(selectedClient && (selectedClient.name + ' - ' + selectedClient.phone)) || ''"></span>
                            <button type="button" @click="selectedClient = null; clientSearch = ''" class="text-red-600 hover:text-red-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <div x-show="showClientResults && clients.length > 0" 
                             class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                            <template x-for="client in clients" :key="client.id">
                                <div @click="selectedClient = client; selectedClientId = client.id; clientSearch = client.name + ' - ' + client.phone; showClientResults = false" 
                                     class="p-2 hover:bg-gray-100 cursor-pointer">
                                    <div x-text="client.name" class="font-semibold"></div>
                                    <div x-text="client.phone" class="text-sm text-gray-600"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div>
                    <x-input-label for="table_number" value="Стол" />
                    <select id="table_number" name="table_number" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none" required>
                        <option value="">Выберите стол</option>
                        @foreach($tableNumbers as $tableNum)
                            <option value="{{ $tableNum }}" {{ old('table_number') == $tableNum ? 'selected' : '' }}>{{ $tableNum }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('table_number')" />
                </div>

                <div>
                    <x-input-label for="booking_date" value="Дата бронирования" />
                    <x-text-input id="booking_date" name="booking_date" type="date" class="mt-1 block w-full" value="{{ old('booking_date', $selectedDate) }}" required />
                    <x-input-error class="mt-2" :messages="$errors->get('booking_date')" />
                </div>

                <div>
                    <x-input-label for="booking_time" value="Время бронирования" />
                    <select id="booking_time" name="booking_time" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none" required>
                        <option value="">Выберите время</option>
                        @php
                            $startTime = \Carbon\Carbon::parse('14:00');
                            $endTime = \Carbon\Carbon::parse('03:30')->addDay();
                            $current = $startTime->copy();
                            while ($current <= $endTime) {
                                $timeValue = $current->format('H:i');
                                $selected = old('booking_time') == $timeValue ? 'selected' : '';
                                echo "<option value=\"{$timeValue}\" {$selected}>{$timeValue}</option>";
                                $current->addMinutes(30);
                            }
                        @endphp
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('booking_time')" />
                </div>

                <div>
                    <x-input-label for="duration" value="Длительность" />
                    <select id="duration" name="duration" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none" required>
                        @php
                            $durations = [
                                60 => '1 час',
                                90 => '1.5 часа',
                                120 => '2 часа',
                                180 => '3 часа',
                                240 => '4 часа',
                                300 => '5 часов',
                                360 => '6 часов'
                            ];
                            $selectedDuration = old('duration', 60);
                        @endphp
                        @foreach($durations as $minutes => $label)
                            <option value="{{ $minutes }}" {{ $selectedDuration == $minutes ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('duration')" />
                </div>

                <div>
                    <x-input-label for="guests_count" value="Количество гостей" />
                    <x-text-input id="guests_count" name="guests_count" type="number" min="1" class="mt-1 block w-full" :value="old('guests_count')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('guests_count')" />
                </div>

                <div x-show="!selectedClient">
                    <x-input-label for="guest_name" value="Имя гостя" />
                    <x-text-input id="guest_name" name="guest_name" type="text" class="mt-1 block w-full" :value="old('guest_name')" />
                    <x-input-error class="mt-2" :messages="$errors->get('guest_name')" />
                </div>

                <div x-show="!selectedClient">
                    <x-input-label for="phone" value="Номер телефона" />
                    <x-text-input id="phone" name="phone" type="tel" class="mt-1 block w-full" :value="old('phone')" />
                    <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                </div>

                <div>
                    <x-input-label for="comment" value="Комментарий" />
                    <textarea id="comment" name="comment" rows="3" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none">{{ old('comment') }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('comment')" />
                </div>

                <div class="flex items-center gap-4">
                    <x-primary-button>Сохранить</x-primary-button>
                    <button type="button" @click="showModal = false" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400">
                        Отмена
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Модальное окно редактирования бронирования -->
    <div x-show="showEditModal" 
         x-cloak 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" 
         style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999;" 
         @click.away="showEditModal = false"
         @keydown.escape.window="showEditModal = false">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white" style="position: relative; z-index: 10000; margin: 5rem auto;">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Изменить бронирование стола</h3>
                <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <template x-if="editTable">
                <div>
                    <form method="POST" :action="`{{ url('/tables') }}/${editTable.id}`" class="space-y-4" x-data="{ 
                        selectedClient: editTable ? (editTable.client || null) : null, 
                        selectedClientId: editTable && editTable.client ? editTable.client.id : null,
                        clientSearch: editTable && editTable.client ? editTable.client.name + ' - ' + editTable.client.phone : '', 
                        showClientResults: false, 
                        clients: [],
                        formData: editTable || {}
                    }" x-init="
                        $watch('editTable', value => {
                            if (value) {
                                selectedClient = value.client || null;
                                clientSearch = value.client ? value.client.name + ' - ' + value.client.phone : '';
                                formData = value;
                                // Set form field values
                                setTimeout(() => {
                                    if (document.getElementById('table_number_edit')) {
                                        document.getElementById('table_number_edit').value = value.table_number;
                                    }
                                    if (document.getElementById('booking_date_edit')) {
                                        document.getElementById('booking_date_edit').value = value.booking_date;
                                    }
                                    if (document.getElementById('booking_time_edit')) {
                                        document.getElementById('booking_time_edit').value = value.booking_time;
                                    }
                                    if (document.getElementById('duration_edit')) {
                                        document.getElementById('duration_edit').value = value.duration;
                                    }
                                    if (document.getElementById('guests_count_edit')) {
                                        document.getElementById('guests_count_edit').value = value.guests_count;
                                    }
                                    if (document.getElementById('guest_name_edit')) {
                                        document.getElementById('guest_name_edit').value = value.guest_name || '';
                                    }
                                    if (document.getElementById('phone_edit')) {
                                        document.getElementById('phone_edit').value = value.phone || '';
                                    }
                                    if (document.getElementById('comment_edit')) {
                                        document.getElementById('comment_edit').value = value.comment || '';
                                    }
                                }, 100);
                            }
                        })
                    ">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="client_search_edit" value="Выбрать клиента" />
                            <div class="relative">
                                <input type="text" 
                                       x-model="clientSearch"
                                       @input.debounce.300ms="
                                           if (clientSearch.length >= 2) {
                                               fetch('{{ route('tables.search.clients') }}?q=' + encodeURIComponent(clientSearch))
                                                   .then(response => response.json())
                                                   .then(data => { clients = data; showClientResults = true; });
                                           } else {
                                               clients = [];
                                               showClientResults = false;
                                           }
                                       "
                                       @click.away="showClientResults = false"
                                       placeholder="Поиск по имени или телефону..."
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none" />
                                <input type="hidden" name="client_id" x-model="selectedClientId">
                                <div x-show="selectedClient" class="mt-2 flex items-center gap-2 p-2 bg-blue-50 rounded">
                                    <span x-text="(selectedClient && (selectedClient.name + ' - ' + selectedClient.phone)) || ''"></span>
                                    <button type="button" @click="selectedClient = null; clientSearch = ''" class="text-red-600 hover:text-red-800">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                                <div x-show="showClientResults && clients.length > 0" 
                                     class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                    <template x-for="client in clients" :key="client.id">
                                        <div @click="selectedClient = client; selectedClientId = client.id; clientSearch = client.name + ' - ' + client.phone; showClientResults = false" 
                                             class="p-2 hover:bg-gray-100 cursor-pointer">
                                            <div x-text="client.name" class="font-semibold"></div>
                                            <div x-text="client.phone" class="text-sm text-gray-600"></div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div>
                            <x-input-label for="table_number_edit" value="Стол" />
                            <select id="table_number_edit" name="table_number" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none" required>
                                <option value="">Выберите стол</option>
                                <template x-for="tableNum in tableNumbers" :key="tableNum">
                                    <option :value="tableNum" x-text="tableNum"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <x-input-label for="booking_date_edit" value="Дата бронирования" />
                            <input id="booking_date_edit" name="booking_date" type="date" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none" required />
                        </div>

                        <div>
                            <x-input-label for="booking_time_edit" value="Время бронирования" />
                            <select id="booking_time_edit" name="booking_time" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none" required>
                                <option value="">Выберите время</option>
                                @php
                                    $startTime = \Carbon\Carbon::parse('14:00');
                                    $endTime = \Carbon\Carbon::parse('03:30')->addDay();
                                    $current = $startTime->copy();
                                    while ($current <= $endTime) {
                                        $timeValue = $current->format('H:i');
                                        echo "<option value=\"{$timeValue}\">{$timeValue}</option>";
                                        $current->addMinutes(30);
                                    }
                                @endphp
                            </select>
                        </div>

                        <div>
                            <x-input-label for="duration_edit" value="Длительность" />
                            <select id="duration_edit" name="duration" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none" required>
                                @php
                                    $durations = [
                                        60 => '1 час',
                                        90 => '1.5 часа',
                                        120 => '2 часа',
                                        180 => '3 часа',
                                        240 => '4 часа',
                                        300 => '5 часов',
                                        360 => '6 часов'
                                    ];
                                @endphp
                                @foreach($durations as $minutes => $label)
                                    <option value="{{ $minutes }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="guests_count_edit" value="Количество гостей" />
                            <input id="guests_count_edit" name="guests_count" type="number" min="1" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none" required />
                        </div>

                        <div x-show="!selectedClient">
                            <x-input-label for="guest_name_edit" value="Имя гостя" />
                            <input id="guest_name_edit" name="guest_name" type="text" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none" />
                        </div>

                        <div x-show="!selectedClient">
                            <x-input-label for="phone_edit" value="Номер телефона" />
                            <input id="phone_edit" name="phone" type="tel" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none" />
                        </div>

                        <div>
                            <x-input-label for="comment_edit" value="Комментарий" />
                            <textarea id="comment_edit" name="comment" rows="3" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none"></textarea>
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>Сохранить</x-primary-button>
                            <button type="button" @click="showEditModal = false" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400">
                                Отмена
                            </button>
                        </div>
                    </form>
                </div>
            </template>
        </div>
    </div>

    <!-- Модальное окно продаж для стола -->
    <div x-show="showSalesModal"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full"
         style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999;"
         @keydown.escape.window="showSalesModal = false"
         @click.away="showSalesModal = false">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-3xl shadow-lg rounded-md bg-white"
             style="position: relative; z-index: 10000; margin: 5rem auto;">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">
                    Продажи для стола
                    <span x-text="currentBooking && currentBooking.table_number ? '(' + currentBooking.table_number + ')' : ''"></span>
                </h3>
                <button @click="showSalesModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Существующие продажи по этому бронированию -->
            <div class="mb-4">
                <h4 class="text-sm font-semibold text-gray-700 mb-2">Товары этого стола</h4>
                <template x-if="currentSales.length > 0">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-xs">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Товар</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Кол-во</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Итог</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="sale in currentSales" :key="sale.id">
                                    <tr>
                                        <td class="px-4 py-2 whitespace-nowrap" x-text="sale.product_name"></td>
                                        <td class="px-4 py-2 whitespace-nowrap" x-text="sale.quantity"></td>
                                        <td class="px-4 py-2 whitespace-nowrap" x-text="formatMoney(sale.total)"></td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            <form :action="`{{ url('/sales') }}/${sale.id}`"
                                                  method="POST"
                                                  onsubmit="return confirm('Удалить продажу?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 text-[10px]">
                                                    Удалить
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td class="px-4 py-2 font-semibold text-right" colspan="2">Итого по столу:</td>
                                    <td class="px-4 py-2 font-semibold" x-text="formatMoney(currentSalesTotal)"></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </template>
                <template x-if="currentSales.length === 0">
                    <p class="text-xs text-gray-500">Для этого стола пока нет продаж.</p>
                </template>
            </div>

            <!-- Форма добавления продажи для стола -->
            <div class="border-t pt-4 mt-4">
                <h4 class="text-sm font-semibold text-gray-700 mb-2">Добавить продажу для этого стола</h4>
                <form method="POST"
                      action="{{ route('sales.store') }}"
                      class="space-y-3"
                      x-data="saleFormForTable(products, categories, warehouses, () => currentBookingId)">
                    @csrf
                    <input type="hidden" name="table_booking_id" :value="currentBookingId">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <x-input-label for="table_category_filter" value="Категория" />
                            <select id="table_category_filter"
                                    x-model="selectedCategoryId"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none text-xs">
                                <option value="">Все категории</option>
                                <template x-for="category in categories" :key="category.id">
                                    <option :value="category.id" x-text="category.name"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="table_product_id" value="Товар" />
                            <select id="table_product_id"
                                    name="product_id"
                                    x-model="selectedProductId"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none text-xs"
                                    required>
                                <option value="">Выберите товар</option>
                                <template x-for="product in filteredProducts" :key="product.id">
                                    <option :value="product.id" x-text="product.name"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <x-input-label for="table_warehouse_id" value="Склад" />
                            <select id="table_warehouse_id"
                                    name="warehouse_id"
                                    x-model="selectedWarehouseId"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none text-xs"
                                    required>
                                <option value="">Выберите склад</option>
                                <template x-for="warehouse in warehouses" :key="warehouse.id">
                                    <option :value="warehouse.id" x-text="warehouse.name"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="table_quantity" value="Количество" />
                            <x-text-input id="table_quantity"
                                          name="quantity"
                                          type="number"
                                          min="1"
                                          class="mt-1 block w-full text-xs"
                                          x-model.number="quantity"
                                          required />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <x-input-label for="table_sold_at" value="Дата и время продажи" />
                            <input id="table_sold_at"
                                   name="sold_at"
                                   type="datetime-local"
                                   class="mt-1 block w-full text-xs px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none"
                                   x-model="soldAt"
                                   required />
                        </div>
                        <div>
                            <x-input-label value="Способ оплаты" />
                            <div class="mt-2 flex items-center gap-3 text-xs">
                                <label class="inline-flex items-center">
                                    <input type="radio"
                                           name="payment_method"
                                           value="cash"
                                           class="form-radio"
                                           x-model="paymentMethod">
                                    <span class="ml-1">Наличные</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio"
                                           name="payment_method"
                                           value="card"
                                           class="form-radio"
                                           x-model="paymentMethod">
                                    <span class="ml-1">Карта</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div>
                        <x-input-label value="Итоговая сумма" />
                        <div class="mt-1 text-sm font-semibold">
                            <span x-text="formattedTotal"></span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <x-primary-button>Сохранить</x-primary-button>
                        <button type="button"
                                @click="showSalesModal = false"
                                class="inline-flex items-center px-3 py-1.5 bg-gray-300 border border-transparent rounded-md font-semibold text-[10px] text-gray-700 uppercase tracking-widest hover:bg-gray-400">
                            Закрыть
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Модальное окно кальянов за столом -->
    <div x-show="showHookahsModal" 
         x-cloak 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" 
         style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999;" 
         @click.away="showHookahsModal = false"
         @keydown.escape.window="showHookahsModal = false">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white" style="position: relative; z-index: 10000; margin: 5rem auto;">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Кальяны за столом</h3>
                <button @click="showHookahsModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Список существующих кальянов -->
            <div x-show="currentHookahs.length > 0" class="mb-4">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Кальяны за столом:</h4>
                <div class="space-y-2">
                    <template x-for="hookah in currentHookahs" :key="hookah.id + '-' + hookah.quantity">
                        <div class="flex items-center justify-between p-2 bg-gray-50 border border-gray-200 rounded-md">
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-700" x-text="hookah.name"></span>
                                <span x-show="hookah.quantity > 1" class="text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full" x-text="'×' + hookah.quantity"></span>
                            </div>
                            <span class="text-sm font-medium text-gray-900" x-text="formatMoney(hookah.price * (hookah.quantity || 1))"></span>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Предупреждение, если кальянов нет -->
            <div x-show="currentHookahs.length === 0" class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md flex items-start">
                <svg class="w-5 h-5 text-yellow-600 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <span class="text-sm text-gray-700">За этим столом еще не было кальянов</span>
            </div>

            <form method="POST" action="{{ route('tables.add-hookah') }}" class="space-y-4" @submit="selectedHookahId = null">
                @csrf
                <input type="hidden" name="table_booking_id" x-model="currentHookahBookingId">

                <div>
                    <x-input-label for="hookah_id" value="Добавить кальян" />
                    <select id="hookah_id" 
                            name="hookah_id" 
                            x-model="selectedHookahId"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none"
                            required>
                        @if(count($hookahs) === 0)
                            <option value="">Список кальянов еще не добавлен директором...</option>
                        @else
                            <option value="" disabled>Выберите кальян</option>
                            @foreach($hookahs as $hookah)
                                <option value="{{ $hookah->id }}">{{ $hookah->name }}</option>
                            @endforeach
                        @endif
                    </select>
                    @error('hookah_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3">
                    <x-primary-button>Добавить</x-primary-button>
                    <button type="button"
                            @click="showHookahsModal = false; selectedHookahId = null"
                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Закрыть
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Модальное окно закрытия стола -->
    <div x-show="showCloseTableModal" 
         x-cloak 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" 
         style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999;" 
         @click.away="showCloseTableModal = false"
         @keydown.escape.window="showCloseTableModal = false">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white" style="position: relative; z-index: 10000; margin: 5rem auto;">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Закрыть стол</h3>
                <button @click="showCloseTableModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('tables.close') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="table_booking_id" x-model="closeTableBookingId">

                <div class="grid grid-cols-2 gap-6">
                    <!-- Левая колонка - СЧЕТ -->
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-3">СЧЕТ</h4>
                        <div class="space-y-3">
                            <!-- Кальяны -->
                            <div>
                                <label class="block text-sm text-gray-700 mb-1">Кальяны</label>
                                <div class="flex items-center gap-2">
                                    <input type="number" 
                                           step="0.01" 
                                           min="0"
                                           x-model.number="hookahsAmount"
                                           name="hookahs_amount"
                                           readonly
                                           class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-700">
                                    <span class="text-sm text-gray-600">руб.</span>
                                </div>
                            </div>

                            <!-- Чаевые -->
                            <div>
                                <label class="block text-sm text-gray-700 mb-1">Чаевые</label>
                                <div class="flex items-center gap-2">
                                    <input type="number" 
                                           step="0.01" 
                                           min="0"
                                           x-model.number="tipsAmount"
                                           name="tips_amount"
                                           class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none">
                                    <span class="text-sm text-gray-600">руб.</span>
                                </div>
                            </div>

                            <!-- Продажи -->
                            <div>
                                <label class="block text-sm text-gray-700 mb-1">Продажи</label>
                                <div class="flex items-center gap-2">
                                    <input type="number" 
                                           step="0.01" 
                                           min="0"
                                           x-model.number="salesAmount"
                                           name="sales_amount"
                                           readonly
                                           class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-700">
                                    <span class="text-sm text-gray-600">руб.</span>
                                </div>
                            </div>

                            <!-- Скидка -->
                            <div>
                                <label class="block text-sm text-gray-700 mb-1">Скидка</label>
                                <div class="flex items-center gap-2">
                                    <input type="number" 
                                           step="0.01" 
                                           min="0"
                                           :max="discountType === 'percent' ? 100 : null"
                                           x-model.number="discountAmount"
                                           name="discount_amount"
                                           class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none">
                                    <span class="text-sm text-gray-600" x-text="discountType === 'rub' ? 'руб.' : '%'"></span>
                                    <button type="button" 
                                            @click="discountType = discountType === 'rub' ? 'percent' : 'rub'; discountAmount = 0"
                                            class="text-sm text-blue-600 hover:text-blue-800 underline">
                                        <span x-show="discountType === 'rub'">руб. / %</span>
                                        <span x-show="discountType === 'percent'">% / руб.</span>
                                    </button>
                                </div>
                                <input type="hidden" name="discount_type" x-model="discountType">
                            </div>

                            <!-- ИТОГО -->
                            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3">
                                <label class="block text-sm font-semibold text-gray-700 mb-1">ИТОГО</label>
                                <div class="text-lg font-bold text-gray-900">
                                    <span x-text="formatMoney(closeTableTotal)"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Правая колонка - Детали заказа -->
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-3">КАЛЬЯНЫ</h4>
                        <div class="mb-4">
                            <template x-if="closeTableHookahs.length === 0">
                                <p class="text-sm text-gray-500">За столом не было кальянов...</p>
                            </template>
                            <template x-if="closeTableHookahs.length > 0">
                                <div class="space-y-1">
                                    <template x-for="hookah in closeTableHookahs" :key="hookah.id">
                                        <div class="text-sm text-gray-700">
                                            <span x-text="hookah.name"></span>
                                            <span x-show="hookah.quantity > 1" x-text="' × ' + hookah.quantity"></span>
                                            <span x-text="' = ' + formatMoney(hookah.price * (hookah.quantity || 1))"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>

                        <h4 class="text-sm font-semibold text-gray-700 mb-3">ПРОДАЖИ</h4>
                        <div class="mb-4">
                            <template x-if="closeTableSales.length === 0">
                                <p class="text-sm text-gray-500">Продаж не было...</p>
                            </template>
                            <template x-if="closeTableSales.length > 0">
                                <div class="space-y-1">
                                    <template x-for="sale in closeTableSales" :key="sale.id">
                                        <div class="text-sm text-gray-700">
                                            <span x-text="sale.product_name"></span>
                                            <span x-text="' × ' + sale.quantity + ' = ' + formatMoney(sale.total)"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Способ оплаты -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Способ оплаты</label>
                    <div class="flex items-center gap-4">
                        <label class="inline-flex items-center">
                            <input type="radio" 
                                   name="payment_method" 
                                   value="cash"
                                   x-model="paymentMethod"
                                   class="form-radio text-indigo-600">
                            <span class="ml-2 text-sm text-gray-700">наличными</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" 
                                   name="payment_method" 
                                   value="card"
                                   x-model="paymentMethod"
                                   class="form-radio text-indigo-600">
                            <span class="ml-2 text-sm text-gray-700">по карте</span>
                        </label>
                        <button type="button" class="text-sm text-blue-600 hover:text-blue-800 underline">
                            Разделить
                        </button>
                    </div>
                </div>

                <!-- Сотрудники -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Сотрудники</label>
                    <select name="employee_id" 
                            x-model="selectedEmployeeId"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none">
                        <option value="">Выбрать...</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Комментарий -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Комментарий</label>
                    <textarea name="comment" 
                              x-model="closeTableComment"
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none"></textarea>
                </div>

                <!-- Кнопки -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t">
                    <x-primary-button>Закрыть стол</x-primary-button>
                    <button type="button"
                            @click="showCloseTableModal = false"
                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Отмена
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        window.tablesPageData = function () {
            const openSalesBookingId = @json(session('open_sales_booking_id'));
            const openHookahsBookingId = @json(request('open_hookahs'));

            return {
                showModal: {{ $errors->any() ? 'true' : 'false' }},
                showEditModal: false,
                editTable: null,
                tableMenuOpen: null,
                tableNumbers: @json($tableNumbers),
                selectedDate: @json($selectedDate),
                bookingsData: @json($bookingsData),
                products: @json($products),
                categories: @json($categories),
                warehouses: @json($warehouses),
                hookahs: @json($hookahs),
                hookahsByBooking: @json($hookahsByBooking),
                salesByBooking: @json($salesByBooking),
                users: @json($users),
                showSalesModal: !!openSalesBookingId,
                currentBookingId: openSalesBookingId || null,
                showHookahsModal: !!openHookahsBookingId,
                currentHookahBookingId: openHookahsBookingId || null,
                selectedHookahId: null,
                showCloseTableModal: false,
                closeTableBookingId: null,
                hookahsAmount: 0,
                tipsAmount: 0,
                salesAmount: 0,
                discountAmount: 0,
                discountType: 'rub',
                paymentMethod: 'cash',
                selectedEmployeeId: null,
                closeTableComment: '',

                get currentBooking() {
                    if (!this.currentBookingId) return null;
                    return this.bookingsData.find(b => b.id === this.currentBookingId) || null;
                },

                get currentSales() {
                    if (!this.currentBookingId) return [];
                    return this.salesByBooking[this.currentBookingId] || [];
                },

                get currentSalesTotal() {
                    return this.currentSales.reduce((sum, s) => {
                        return sum + parseFloat(s.total || 0);
                    }, 0);
                },

                openSalesForBooking(id) {
                    this.currentBookingId = id;
                    this.showSalesModal = true;
                },

                openHookahsForBooking(id) {
                    this.currentHookahBookingId = id;
                    this.showHookahsModal = true;
                },

                openCloseTableModal(id) {
                    this.closeTableBookingId = id;
                    const booking = this.bookingsData.find(b => b.id === id);
                    if (booking) {
                        // Подсчитываем суммы кальянов
                        const hookahs = this.hookahsByBooking[id] || [];
                        this.hookahsAmount = hookahs.reduce((sum, h) => sum + (parseFloat(h.price) * (h.quantity || 1)), 0);
                        
                        // Подсчитываем суммы продаж
                        const sales = this.salesByBooking[id] || [];
                        this.salesAmount = sales.reduce((sum, s) => sum + parseFloat(s.total || 0), 0);
                        
                        // Сбрасываем остальные поля
                        this.tipsAmount = 0;
                        this.discountAmount = 0;
                        this.discountType = 'rub';
                        this.paymentMethod = 'cash';
                        this.selectedEmployeeId = null;
                        this.closeTableComment = '';
                    }
                    this.showCloseTableModal = true;
                },

                get currentHookahs() {
                    if (!this.currentHookahBookingId) return [];
                    return this.hookahsByBooking[this.currentHookahBookingId] || [];
                },

                get closeTableHookahs() {
                    if (!this.closeTableBookingId) return [];
                    return this.hookahsByBooking[this.closeTableBookingId] || [];
                },

                get closeTableSales() {
                    if (!this.closeTableBookingId) return [];
                    return this.salesByBooking[this.closeTableBookingId] || [];
                },

                get closeTableTotal() {
                    let total = this.hookahsAmount + this.tipsAmount + this.salesAmount;
                    if (this.discountType === 'rub') {
                        total -= this.discountAmount;
                    } else {
                        total = total * (1 - this.discountAmount / 100);
                    }
                    return Math.max(0, total);
                },

                formatMoney(value) {
                    const num = parseFloat(value || 0);
                    return num.toLocaleString('ru-RU', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }) + ' ₽';
                },
            };
        };

        window.saleFormForTable = function (products, categories, warehouses, getBookingId) {
            return {
                products,
                categories,
                warehouses,
                selectedCategoryId: '',
                selectedProductId: '',
                selectedWarehouseId: '',
                quantity: 1,
                paymentMethod: 'cash',
                soldAt: '{{ now()->format('Y-m-d\TH:i') }}',

                get filteredProducts() {
                    if (!this.selectedCategoryId) {
                        return this.products;
                    }
                    return this.products.filter(p => String(p.category_id) === String(this.selectedCategoryId));
                },

                get currentProduct() {
                    if (!this.selectedProductId) return null;
                    return this.products.find(p => String(p.id) === String(this.selectedProductId)) || null;
                },

                get total() {
                    if (!this.currentProduct) return 0;
                    const price = parseFloat(this.currentProduct.price || 0);
                    const qty = parseInt(this.quantity || 0, 10);
                    return price * qty;
                },

                get formattedTotal() {
                    return this.total.toLocaleString('ru-RU', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }) + ' ₽';
                },
            };
        };

        // Показываем toast-уведомления после загрузки страницы
        document.addEventListener('DOMContentLoaded', function() {
            @if (session('success'))
                toastr.success(@json(session('success')));
            @endif
            
            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    toastr.error(@json($error));
                @endforeach
            @endif
        });
    </script>
</x-app-layout>
