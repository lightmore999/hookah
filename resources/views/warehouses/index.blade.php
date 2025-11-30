<x-app-layout x-data="warehousesPageData()">
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Склады
            </h2>
            <button type="button" @click="showCreateWarehouseModal = true" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Добавить склад
            </button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Зона складов -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Склады</h3>
                    <div class="flex flex-wrap gap-4 justify-start">
                        @forelse ($warehouses as $warehouse)
                            <div class="relative bg-gray-100 border-2 border-gray-300 rounded-lg p-6 w-64 h-48 hover:border-blue-500 transition cursor-pointer" onclick="window.location.href='{{ route('warehouses.show', $warehouse) }}'">
                                <div class="absolute top-2 left-2 flex gap-2">
                                    <button type="button" @click.stop="
                                        editWarehouse = warehousesData.find(w => w.id === {{ $warehouse->id }});
                                        showEditWarehouseModal = true;
                                    " class="bg-yellow-500 hover:bg-yellow-700 text-white text-xs font-bold py-1 px-2 rounded">
                                        Изменить
                                    </button>
                                    <form action="{{ route('warehouses.destroy', $warehouse) }}" method="POST" onclick="event.stopPropagation();" onsubmit="return confirm('Вы уверены, что хотите удалить этот склад?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-500 hover:bg-red-700 text-white text-xs font-bold py-1 px-2 rounded">
                                            Удалить
                                        </button>
                                    </form>
                                </div>
                                <div class="h-full flex items-center justify-center">
                                    <h4 class="text-xl font-bold text-gray-800 text-center">{{ $warehouse->name }}</h4>
                                </div>
                            </div>
                        @empty
                            <div class="w-full text-center text-gray-500 py-8">
                                Склады не найдены. Добавьте первый склад.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Зона закупок -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Закупки</h3>
                        <button type="button" @click="showCreatePurchaseModal = true" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Добавить закупку
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Товар
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Склад
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Количество
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Стоимость за штуку
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Стоимость закупки
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Дата
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Действия
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($purchases as $purchase)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $purchase->product->name ?? 'Товар удален' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $purchase->warehouse->name ?? 'Склад удален' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $purchase->quantity }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ number_format($purchase->unit_price, 2, ',', ' ') }} ₽
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ number_format($purchase->total_cost, 2, ',', ' ') }} ₽
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $purchase->purchase_date->format('d.m.Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <form action="{{ route('purchases.destroy', $purchase) }}" method="POST" class="inline" onsubmit="return confirm('Вы уверены, что хотите удалить эту закупку?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">
                                                    Удалить
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            Закупки не найдены. Добавьте первую закупку.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно создания склада -->
    <div x-show="showCreateWarehouseModal" 
         x-cloak 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" 
         style="z-index: 1000;"
         @click.away="showCreateWarehouseModal = false"
         @keydown.escape.window="showCreateWarehouseModal = false">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white" style="z-index: 1001;">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Добавить склад</h3>
                <button @click="showCreateWarehouseModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form method="POST" action="{{ route('warehouses.store') }}" class="space-y-4">
                @csrf

                <div>
                    <x-input-label for="name" value="Название" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>

                <div class="flex items-center gap-4">
                    <x-primary-button>Сохранить</x-primary-button>
                    <button type="button" @click="showCreateWarehouseModal = false" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400">
                        Отмена
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Модальное окно редактирования склада -->
    <div x-show="showEditWarehouseModal" 
         x-cloak 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" 
         style="z-index: 1000;"
         @click.away="showEditWarehouseModal = false"
         @keydown.escape.window="showEditWarehouseModal = false">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white" style="z-index: 1001;">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Изменить склад</h3>
                <button @click="showEditWarehouseModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <template x-if="editWarehouse">
                <form method="POST" :action="`{{ url('/warehouses') }}/${editWarehouse.id}`" class="space-y-4" x-init="
                    setTimeout(() => {
                        if (editWarehouse) {
                            document.getElementById('name_edit').value = editWarehouse.name || '';
                        }
                    }, 100);
                ">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="name_edit" value="Название" />
                        <input id="name_edit" name="name" type="text" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>Сохранить</x-primary-button>
                        <button type="button" @click="showEditWarehouseModal = false" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400">
                            Отмена
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    <!-- Модальное окно создания закупки -->
    <div x-show="showCreatePurchaseModal" 
         x-cloak 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" 
         style="z-index: 1000;"
         @click.away="showCreatePurchaseModal = false"
         @keydown.escape.window="showCreatePurchaseModal = false">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white" style="z-index: 1001;">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Добавить закупку</h3>
                <button @click="showCreatePurchaseModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form method="POST" action="{{ route('purchases.store') }}" class="space-y-4">
                @csrf

                <div>
                    <x-input-label for="product_id" value="Товар" />
                    <select id="product_id" name="product_id" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none" required>
                        <option value="">Выберите товар</option>
                        <template x-for="product in products" :key="product.id">
                            <option :value="product.id" x-text="product.name"></option>
                        </template>
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('product_id')" />
                </div>

                <div>
                    <x-input-label for="warehouse_id" value="Склад" />
                    <select id="warehouse_id" name="warehouse_id" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none" required>
                        <option value="">Выберите склад</option>
                        <template x-for="warehouse in warehouses" :key="warehouse.id">
                            <option :value="warehouse.id" x-text="warehouse.name"></option>
                        </template>
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('warehouse_id')" />
                </div>

                <div>
                    <x-input-label for="quantity" value="Количество" />
                    <x-text-input id="quantity" name="quantity" type="number" min="1" class="mt-1 block w-full" :value="old('quantity')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('quantity')" />
                </div>

                <div>
                    <x-input-label for="unit_price" value="Стоимость товара за штуку" />
                    <x-text-input id="unit_price" name="unit_price" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('unit_price')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('unit_price')" />
                </div>

                <div class="flex items-center gap-4">
                    <x-primary-button>Сохранить</x-primary-button>
                    <button type="button" @click="showCreatePurchaseModal = false" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400">
                        Отмена
                    </button>
                </div>
            </form>
        </div>
    </div>
    </div>

    <script>
        window.warehousesPageData = function () {
            return {
                showCreateWarehouseModal: {{ $errors->any() && !old('_method') ? 'true' : 'false' }},
                showEditWarehouseModal: false,
                showCreatePurchaseModal: false,
                editWarehouse: null,
                warehousesData: @json($warehousesData),
                products: @json($productsData),
                warehouses: @json($warehousesData),
            };
        };

        document.addEventListener('DOMContentLoaded', function() {
            @if (session('success'))
                toastr.success(@json(session('success')));
                setTimeout(() => window.location.reload(), 1000);
            @endif
            
            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    toastr.error(@json($error));
                @endforeach
            @endif
        });
    </script>
</x-app-layout>

