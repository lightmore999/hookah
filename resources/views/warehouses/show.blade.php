<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Склад: {{ $warehouse->name }}
            </h2>
            <a href="{{ route('warehouses.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Назад к складам
            </a>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ 
        openModal: null,
        stockLevelId: null,
        operation: null,
        quantity: '',
        targetWarehouseId: ''
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Товары на складе</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Товар
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Количество
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Дата последнего обновления
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Действия
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($stockLevels as $stockLevel)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $stockLevel->product->name ?? 'Товар удален' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $stockLevel->quantity }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $stockLevel->last_updated ? $stockLevel->last_updated->format('d.m.Y H:i') : 'Не обновлялось' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex gap-2">
                                                <button @click="openModal = 'move'; stockLevelId = {{ $stockLevel->id }}; operation = 'move'; quantity = ''; targetWarehouseId = ''" 
                                                    class="text-blue-600 hover:text-blue-900">
                                                    Переместить
                                                </button>
                                                <button @click="openModal = 'writeoff_work'; stockLevelId = {{ $stockLevel->id }}; operation = 'writeoff_work'; quantity = ''" 
                                                    class="text-orange-600 hover:text-orange-900">
                                                    Списать в работу
                                                </button>
                                                <button @click="openModal = 'writeoff'; stockLevelId = {{ $stockLevel->id }}; operation = 'writeoff'; quantity = ''" 
                                                    class="text-red-600 hover:text-red-900">
                                                    Списать
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            На этом складе нет товаров.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модальное окно для перемещения -->
        <div x-show="openModal === 'move'" x-cloak class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.away="openModal = null">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Переместить товар</h3>
                    <form method="POST" :action="`/warehouses/{{ $warehouse->id }}/stock-levels/${stockLevelId}/move`">
                        @csrf
                        <div class="mb-4">
                            <x-input-label for="quantity_move" value="Количество" />
                            <x-text-input id="quantity_move" name="quantity" type="number" min="1" x-model="quantity" class="mt-1 block w-full" required />
                        </div>
                        <div class="mb-4">
                            <x-input-label for="target_warehouse_id" value="Склад назначения" />
                            <select id="target_warehouse_id" name="target_warehouse_id" x-model="targetWarehouseId" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none" required>
                                <option value="">Выберите склад</option>
                                @foreach($otherWarehouses as $otherWarehouse)
                                    <option value="{{ $otherWarehouse->id }}">{{ $otherWarehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <x-primary-button>Переместить</x-primary-button>
                            <button type="button" @click="openModal = null" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400">
                                Отмена
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Модальное окно для списания в работу -->
        <div x-show="openModal === 'writeoff_work'" x-cloak class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.away="openModal = null">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Списать в работу</h3>
                    <form method="POST" :action="`/warehouses/{{ $warehouse->id }}/stock-levels/${stockLevelId}/writeoff-work`">
                        @csrf
                        <div class="mb-4">
                            <x-input-label for="quantity_writeoff_work" value="Количество" />
                            <x-text-input id="quantity_writeoff_work" name="quantity" type="number" min="1" x-model="quantity" class="mt-1 block w-full" required />
                        </div>
                        <div class="flex gap-2">
                            <x-primary-button>Списать</x-primary-button>
                            <button type="button" @click="openModal = null" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400">
                                Отмена
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Модальное окно для списания -->
        <div x-show="openModal === 'writeoff'" x-cloak class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.away="openModal = null">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Списать товар</h3>
                    <form method="POST" :action="`/warehouses/{{ $warehouse->id }}/stock-levels/${stockLevelId}/writeoff`">
                        @csrf
                        <div class="mb-4">
                            <x-input-label for="quantity_writeoff" value="Количество" />
                            <x-text-input id="quantity_writeoff" name="quantity" type="number" min="1" x-model="quantity" class="mt-1 block w-full" required />
                        </div>
                        <div class="flex gap-2">
                            <x-primary-button>Списать</x-primary-button>
                            <button type="button" @click="openModal = null" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400">
                                Отмена
                            </button>
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

