<x-app-layout x-data="salesPageData()">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Продажи
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Кнопка добавления продажи -->
            <div class="mb-4 flex justify-end">
                <button type="button"
                        @click="showCreateModal = true"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Добавить продажу
                </button>
            </div>

            <!-- Таблица продаж -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Товар</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Категория</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Склад</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Клиент</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Кол-во</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Цена</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Итог</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Оплата</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($sales as $sale)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $sale->sold_at?->format('d.m.Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $sale->product->name ?? 'Товар удален' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $sale->product->category->name ?? 'Без категории' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $sale->warehouse->name ?? 'Склад удален' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $sale->client ? ($sale->client->name . ' - ' . $sale->client->phone) : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $sale->quantity }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $sale->product ? number_format($sale->product->price, 2, ',', ' ') . ' ₽' : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ number_format($sale->total, 2, ',', ' ') }} ₽
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $sale->payment_method === 'cash' ? 'Наличные' : 'Карта' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button type="button"
                                                    @click="
                                                        editSale = salesData.find(s => s.id === {{ $sale->id }});
                                                        showEditModal = true;
                                                    "
                                                    class="text-indigo-600 hover:text-indigo-900 mr-3">
                                                Изменить
                                            </button>
                                            <form action="{{ route('sales.destroy', $sale) }}"
                                                  method="POST"
                                                  class="inline"
                                                  onsubmit="return confirm('Удалить продажу?');">
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
                                        <td colspan="10" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            Продажи не найдены. Добавьте первую продажу.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <div class="mt-4">
                            {{ $sales->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно создания продажи -->
    <div x-show="showCreateModal"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
         @keydown.escape.window="showCreateModal = false"
         @click.away="showCreateModal = false">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Добавить продажу</h3>
                <button @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form method="POST"
                  action="{{ route('sales.store') }}"
                  class="space-y-4"
                  x-data="saleForm(products, categories, warehouses)">
                @csrf

                <!-- Выбор категории и товара -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="category_filter" value="Категория" />
                        <select id="category_filter"
                                x-model="selectedCategoryId"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none">
                            <option value="">Все категории</option>
                            <template x-for="category in categories" :key="category.id">
                                <option :value="category.id" x-text="category.name"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <x-input-label for="product_id" value="Товар" />
                        <select id="product_id"
                                name="product_id"
                                x-model="selectedProductId"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none"
                                required>
                            <option value="">Выберите товар</option>
                            <template x-for="product in filteredProducts" :key="product.id">
                                <option :value="product.id" x-text="product.name"></option>
                            </template>
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('product_id')" />
                    </div>
                </div>

                <!-- Склад и количество -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="warehouse_id" value="Склад" />
                        <select id="warehouse_id"
                                name="warehouse_id"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none"
                                required>
                            <option value="">Выберите склад</option>
                            <template x-for="warehouse in warehouses" :key="warehouse.id">
                                <option :value="warehouse.id" x-text="warehouse.name"></option>
                            </template>
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('warehouse_id')" />
                    </div>

                    <div>
                        <x-input-label for="quantity" value="Количество" />
                        <x-text-input id="quantity"
                                      name="quantity"
                                      type="number"
                                      min="1"
                                      class="mt-1 block w-full"
                                      x-model.number="quantity"
                                      required />
                        <x-input-error class="mt-2" :messages="$errors->get('quantity')" />
                    </div>
                </div>

                <!-- Клиент (поиск как в столах) -->
                <div>
                    <x-input-label for="client_search" value="Клиент (необязательно)" />
                    <div class="relative">
                        <input type="text"
                               x-model="clientSearch"
                               @input.debounce.300ms="searchClients"
                               @click.away="showClientResults = false"
                               placeholder="Поиск по имени или телефону..."
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none" />
                        <input type="hidden" name="client_id" :value="selectedClientId">

                        <div x-show="selectedClient"
                             class="mt-2 flex items-center gap-2 p-2 bg-blue-50 rounded">
                            <span x-text="selectedClient.name + ' - ' + selectedClient.phone"></span>
                            <button type="button"
                                    @click="clearClient"
                                    class="text-red-600 hover:text-red-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div x-show="showClientResults && clients.length > 0"
                             class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                            <template x-for="client in clients" :key="client.id">
                                <div @click="selectClient(client)"
                                     class="p-2 hover:bg-gray-100 cursor-pointer">
                                    <div x-text="client.name" class="font-semibold"></div>
                                    <div x-text="client.phone" class="text-sm text-gray-600"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Дата/время и способ оплаты -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="sold_at" value="Дата и время продажи" />
                        <x-text-input id="sold_at"
                                      name="sold_at"
                                      type="datetime-local"
                                      class="mt-1 block w-full"
                                      value="{{ now()->format('Y-m-d\TH:i') }}"
                                      required />
                        <x-input-error class="mt-2" :messages="$errors->get('sold_at')" />
                    </div>

                    <div>
                        <x-input-label value="Способ оплаты" />
                        <div class="mt-2 flex items-center gap-4">
                            <label class="inline-flex items-center">
                                <input type="radio"
                                       name="payment_method"
                                       value="cash"
                                       class="form-radio"
                                       checked>
                                <span class="ml-2">Наличные</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio"
                                       name="payment_method"
                                       value="card"
                                       class="form-radio">
                                <span class="ml-2">Карта</span>
                            </label>
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('payment_method')" />
                    </div>
                </div>

                <!-- Итоговая сумма (расчёт на клиенте) -->
                <div>
                    <x-input-label value="Итоговая сумма" />
                    <div class="mt-1 text-lg font-semibold">
                        <span x-text="formattedTotal"></span>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <x-primary-button>Сохранить</x-primary-button>
                    <button type="button"
                            @click="showCreateModal = false"
                            class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400">
                        Отмена
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Модальное окно редактирования продажи -->
    <div x-show="showEditModal"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
         @keydown.escape.window="showEditModal = false"
         @click.away="showEditModal = false">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Изменить продажу</h3>
                <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <template x-if="editSale">
                <form :action="`{{ url('/sales') }}/${editSale.id}`"
                      method="POST"
                      class="space-y-4"
                      x-data="saleForm(products, categories, warehouses, editSale)"
                      x-init="initFromSale(editSale)">
                    @csrf
                    @method('PUT')

                    <!-- те же поля, что и в форме создания, с привязкой к текущей продаже -->
                    <!-- Категория + товар -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="category_filter_edit" value="Категория" />
                            <select id="category_filter_edit"
                                    x-model="selectedCategoryId"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none">
                                <option value="">Все категории</option>
                                <template x-for="category in categories" :key="category.id">
                                    <option :value="category.id" x-text="category.name"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <x-input-label for="product_id_edit" value="Товар" />
                            <select id="product_id_edit"
                                    name="product_id"
                                    x-model="selectedProductId"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none"
                                    required>
                                <option value="">Выберите товар</option>
                                <template x-for="product in filteredProducts" :key="product.id">
                                    <option :value="product.id" x-text="product.name"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <!-- Склад + количество -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="warehouse_id_edit" value="Склад" />
                            <select id="warehouse_id_edit"
                                    name="warehouse_id"
                                    x-model="selectedWarehouseId"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none"
                                    required>
                                <option value="">Выберите склад</option>
                                <template x-for="warehouse in warehouses" :key="warehouse.id">
                                    <option :value="warehouse.id" x-text="warehouse.name"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <x-input-label for="quantity_edit" value="Количество" />
                            <x-text-input id="quantity_edit"
                                          name="quantity"
                                          type="number"
                                          min="1"
                                          class="mt-1 block w-full"
                                          x-model.number="quantity"
                                          required />
                        </div>
                    </div>

                    <!-- Клиент -->
                    <div>
                        <x-input-label for="client_search_edit" value="Клиент (необязательно)" />
                        <div class="relative">
                            <input type="text"
                                   x-model="clientSearch"
                                   @input.debounce.300ms="searchClients"
                                   @click.away="showClientResults = false"
                                   placeholder="Поиск по имени или телефону..."
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none" />
                            <input type="hidden" name="client_id" :value="selectedClientId">

                            <div x-show="selectedClient"
                                 class="mt-2 flex items-center gap-2 p-2 bg-blue-50 rounded">
                                <span x-text="selectedClient.name + ' - ' + selectedClient.phone"></span>
                                <button type="button"
                                        @click="clearClient"
                                        class="text-red-600 hover:text-red-800">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>

                            <div x-show="showClientResults && clients.length > 0"
                                 class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                <template x-for="client in clients" :key="client.id">
                                    <div @click="selectClient(client)"
                                         class="p-2 hover:bg-gray-100 cursor-pointer">
                                        <div x-text="client.name" class="font-semibold"></div>
                                        <div x-text="client.phone" class="text-sm text-gray-600"></div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Дата/время и способ оплаты -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="sold_at_edit" value="Дата и время продажи" />
                            <x-text-input id="sold_at_edit"
                                          name="sold_at"
                                          type="datetime-local"
                                          class="mt-1 block w-full"
                                          x-model="soldAt"
                                          required />
                        </div>

                        <div>
                            <x-input-label value="Способ оплаты" />
                            <div class="mt-2 flex items-center gap-4">
                                <label class="inline-flex items-center">
                                    <input type="radio"
                                           name="payment_method"
                                           value="cash"
                                           class="form-radio"
                                           x-model="paymentMethod">
                                    <span class="ml-2">Наличные</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio"
                                           name="payment_method"
                                           value="card"
                                           class="form-radio"
                                           x-model="paymentMethod">
                                    <span class="ml-2">Карта</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Итог -->
                    <div>
                        <x-input-label value="Итоговая сумма" />
                        <div class="mt-1 text-lg font-semibold">
                            <span x-text="formattedTotal"></span>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>Сохранить</x-primary-button>
                        <button type="button"
                                @click="showEditModal = false"
                                class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400">
                            Отмена
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    <script>
        window.salesPageData = function () {
            return {
                showCreateModal: {{ $errors->any() && !old('_method') ? 'true' : 'false' }},
                showEditModal: false,
                editSale: null,
                salesData: @json($salesData),
                products: @json($products),
                categories: @json($categories),
                warehouses: @json($warehouses),
            };
        };

        window.saleForm = function (products, categories, warehouses, existingSale = null) {
            return {
                products,
                categories,
                warehouses,
                selectedCategoryId: '',
                selectedProductId: existingSale ? existingSale.product_id : '',
                selectedWarehouseId: existingSale ? existingSale.warehouse_id : '',
                quantity: existingSale ? existingSale.quantity : 1,
                paymentMethod: existingSale ? existingSale.payment_method : 'cash',
                soldAt: existingSale ? existingSale.sold_at : '{{ now()->format('Y-m-d\TH:i') }}',

                clientSearch: '',
                selectedClient: null,
                selectedClientId: existingSale ? existingSale.client_id : null,
                showClientResults: false,
                clients: [],

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

                initFromSale(sale) {
                    this.selectedProductId = sale.product_id;
                    this.selectedWarehouseId = sale.warehouse_id;
                    this.selectedClientId = sale.client_id;
                    this.quantity = sale.quantity;
                    this.paymentMethod = sale.payment_method;
                    this.soldAt = sale.sold_at;
                },

                async searchClients() {
                    if (!this.clientSearch || this.clientSearch.length < 2) {
                        this.clients = [];
                        this.showClientResults = false;
                        return;
                    }

                    try {
                        const response = await fetch('{{ route('sales.search.clients') }}?q=' + encodeURIComponent(this.clientSearch));
                        this.clients = await response.json();
                        this.showClientResults = true;
                    } catch (e) {
                        console.error(e);
                    }
                },

                selectClient(client) {
                    this.selectedClient = client;
                    this.selectedClientId = client.id;
                    this.clientSearch = client.name + ' - ' + client.phone;
                    this.showClientResults = false;
                },

                clearClient() {
                    this.selectedClient = null;
                    this.selectedClientId = null;
                    this.clientSearch = '';
                },
            };
        };

        document.addEventListener('DOMContentLoaded', function () {
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



