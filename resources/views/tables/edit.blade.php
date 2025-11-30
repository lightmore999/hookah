<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Изменить бронирование стола
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('tables.update', $table) }}" class="space-y-6" x-data="{ selectedClient: {{ $table->client_id && $table->client ? json_encode(['id' => $table->client_id, 'name' => $table->client->name, 'phone' => $table->client->phone]) : 'null' }}, clientSearch: '{{ $table->client ? addslashes($table->client->name . ' - ' . $table->client->phone) : '' }}', showClientResults: false, clients: [] }">
                        @csrf
                        @method('PUT')

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
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                <input type="hidden" name="client_id" x-model="selectedClient?.id">
                                <div x-show="selectedClient" class="mt-2 flex items-center gap-2 p-2 bg-blue-50 rounded">
                                    <span x-text="selectedClient?.name + ' - ' + selectedClient?.phone"></span>
                                    <button type="button" @click="selectedClient = null; clientSearch = ''" class="text-red-600 hover:text-red-800">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                                <div x-show="showClientResults && clients.length > 0" 
                                     class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                    <template x-for="client in clients" :key="client.id">
                                        <div @click="selectedClient = client; clientSearch = client.name + ' - ' + client.phone; showClientResults = false" 
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
                            <select id="table_number" name="table_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">Выберите стол</option>
                                @foreach($tableNumbers as $tableNum)
                                    <option value="{{ $tableNum }}" {{ old('table_number', $table->table_number) == $tableNum ? 'selected' : '' }}>
                                        {{ $tableNum }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('table_number')" />
                        </div>

                        <div>
                            <x-input-label for="booking_date" value="Дата бронирования" />
                            <x-text-input id="booking_date" name="booking_date" type="date" class="mt-1 block w-full" :value="old('booking_date', $table->booking_date->format('Y-m-d'))" required />
                            <x-input-error class="mt-2" :messages="$errors->get('booking_date')" />
                        </div>

                        <div>
                            <x-input-label for="booking_time" value="Время бронирования" />
                            <x-text-input id="booking_time" name="booking_time" type="time" class="mt-1 block w-full" :value="old('booking_time', $table->booking_time)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('booking_time')" />
                        </div>

                        <div>
                            <x-input-label for="duration" value="Длительность (минуты)" />
                            <x-text-input id="duration" name="duration" type="number" min="30" step="30" class="mt-1 block w-full" :value="old('duration', $table->duration)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('duration')" />
                        </div>

                        <div>
                            <x-input-label for="guests_count" value="Количество гостей" />
                            <x-text-input id="guests_count" name="guests_count" type="number" min="1" class="mt-1 block w-full" :value="old('guests_count', $table->guests_count)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('guests_count')" />
                        </div>

                        <div x-show="!selectedClient">
                            <x-input-label for="guest_name" value="Имя гостя" />
                            <x-text-input id="guest_name" name="guest_name" type="text" class="mt-1 block w-full" :value="old('guest_name', $table->guest_name)" />
                            <x-input-error class="mt-2" :messages="$errors->get('guest_name')" />
                        </div>

                        <div x-show="!selectedClient">
                            <x-input-label for="phone" value="Номер телефона" />
                            <x-text-input id="phone" name="phone" type="tel" class="mt-1 block w-full" :value="old('phone', $table->phone)" />
                            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                        </div>

                        <div>
                            <x-input-label for="comment" value="Комментарий" />
                            <textarea id="comment" name="comment" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('comment', $table->comment) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('comment')" />
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>Сохранить</x-primary-button>
                            <a href="{{ route('tables.index', ['date' => $table->booking_date->format('Y-m-d')]) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Отмена
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
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


