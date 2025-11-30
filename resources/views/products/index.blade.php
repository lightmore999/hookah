<x-app-layout x-data="productsPageData()">
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Товары
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('categories.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Категории
                </a>
                <button type="button" @click="showCreateModal = true" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Добавить товар
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Фильтры и поиск -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4">
                <div class="p-6">
                    <form method="GET" action="{{ route('products.index') }}" id="filterForm" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="search" value="Поиск по названию" />
                                <div class="flex gap-2 mt-1">
                                    <x-text-input id="search" name="search" type="text" class="block w-full" :value="request('search')" placeholder="Введите название..." />
                                    <button type="submit" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded whitespace-nowrap">
                                        Поиск
                                    </button>
                                </div>
                            </div>
                            <div>
                                <x-input-label for="category_id" value="Фильтр по категории" />
                                <select id="category_id" name="category_id" onchange="document.getElementById('filterForm').submit();" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none">
                                    <option value="">Все категории</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @if(request('search') || request('category_id'))
                            <div>
                                <a href="{{ route('products.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                                    Сбросить фильтры
                                </a>
                            </div>
                        @endif
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Название
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Категория
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Цена
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Себестоимость
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Действия
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($products as $product)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $product->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $product->category->name ?? 'Без категории' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ number_format($product->price, 2, ',', ' ') }} ₽
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ number_format($product->cost, 2, ',', ' ') }} ₽
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button type="button" @click="
                                                editProduct = productsData.find(p => p.id === {{ $product->id }});
                                                showEditModal = true;
                                            " class="text-indigo-600 hover:text-indigo-900 mr-3">
                                                Изменить
                                            </button>
                                            <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('Вы уверены, что хотите удалить этот товар?');">
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
                                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            Товары не найдены. Добавьте первый товар.
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

    <!-- Модальное окно создания товара -->
    <div x-show="showCreateModal" 
         x-cloak 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" 
         style="z-index: 1000;"
         @click.away="showCreateModal = false"
         @keydown.escape.window="showCreateModal = false">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white" style="z-index: 1001;">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Добавить товар</h3>
                <button @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form method="POST" action="{{ route('products.store') }}" class="space-y-4">
                @csrf

                <div>
                    <x-input-label for="name" value="Название" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>

                <div>
                    <x-input-label for="category_id" value="Категория" />
                    <select id="category_id" name="category_id" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none" required>
                        <option value="">Выберите категорию</option>
                        <template x-for="category in categories" :key="category.id">
                            <option :value="category.id" x-text="category.name"></option>
                        </template>
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('category_id')" />
                </div>

                <div>
                    <x-input-label for="price" value="Цена" />
                    <x-text-input id="price" name="price" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('price')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('price')" />
                </div>

                <div>
                    <x-input-label for="cost" value="Себестоимость" />
                    <x-text-input id="cost" name="cost" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('cost')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('cost')" />
                </div>

                <div class="flex items-center gap-4">
                    <x-primary-button>Сохранить</x-primary-button>
                    <button type="button" @click="showCreateModal = false" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400">
                        Отмена
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Модальное окно редактирования товара -->
    <div x-show="showEditModal" 
         x-cloak 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" 
         style="z-index: 1000;"
         @click.away="showEditModal = false"
         @keydown.escape.window="showEditModal = false">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white" style="z-index: 1001;">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Изменить товар</h3>
                <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <template x-if="editProduct">
                <form method="POST" :action="`{{ url('/products') }}/${editProduct.id}`" class="space-y-4" x-init="
                    setTimeout(() => {
                        if (editProduct) {
                            document.getElementById('name_edit').value = editProduct.name || '';
                            document.getElementById('category_id_edit').value = editProduct.category_id || '';
                            document.getElementById('price_edit').value = editProduct.price || '';
                            document.getElementById('cost_edit').value = editProduct.cost || '';
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

                    <div>
                        <x-input-label for="category_id_edit" value="Категория" />
                        <select id="category_id_edit" name="category_id" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none" required>
                            <option value="">Выберите категорию</option>
                            <template x-for="category in categories" :key="category.id">
                                <option :value="category.id" x-text="category.name"></option>
                            </template>
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('category_id')" />
                    </div>

                    <div>
                        <x-input-label for="price_edit" value="Цена" />
                        <input id="price_edit" name="price" type="number" step="0.01" min="0" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none" required />
                        <x-input-error class="mt-2" :messages="$errors->get('price')" />
                    </div>

                    <div>
                        <x-input-label for="cost_edit" value="Себестоимость" />
                        <input id="cost_edit" name="cost" type="number" step="0.01" min="0" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none" required />
                        <x-input-error class="mt-2" :messages="$errors->get('cost')" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>Сохранить</x-primary-button>
                        <button type="button" @click="showEditModal = false" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400">
                            Отмена
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>
    </div>

    <script>
        window.productsPageData = function () {
            return {
                showCreateModal: {{ $errors->any() && !old('_method') ? 'true' : 'false' }},
                showEditModal: false,
                editProduct: null,
                productsData: @json($productsData),
                categories: @json($categoriesData),
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
