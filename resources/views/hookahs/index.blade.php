<x-app-layout x-data="hookahsPageData()">
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Кальяны
            </h2>
            <button type="button" @click="showCreateModal = true" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Добавить кальян
            </button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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
                                        Цена
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Себестоимость
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ставка кальянщику
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ставка администратору
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Действия
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($hookahs as $hookah)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $hookah->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ number_format($hookah->price, 2, ',', ' ') }} ₽
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ number_format($hookah->cost, 2, ',', ' ') }} ₽
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ number_format($hookah->hookah_maker_rate, 2, ',', ' ') }} ₽
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ number_format($hookah->administrator_rate, 2, ',', ' ') }} ₽
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button type="button" @click="
                                                editHookah = hookahsData.find(h => h.id === {{ $hookah->id }});
                                                showEditModal = true;
                                            " class="text-indigo-600 hover:text-indigo-900 mr-3">
                                                Изменить
                                            </button>
                                            <form action="{{ route('hookahs.destroy', $hookah) }}" method="POST" class="inline" onsubmit="return confirm('Вы уверены, что хотите удалить этот кальян?');">
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
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            Кальяны не найдены. Добавьте первый кальян.
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

    <!-- Модальное окно создания кальяна -->
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
                <h3 class="text-lg font-medium text-gray-900">Добавить кальян</h3>
                <button @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('hookahs.store') }}" class="space-y-4">
                @csrf

                <div>
                    <x-input-label for="name" value="Название" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
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

                <div>
                    <x-input-label for="hookah_maker_rate" value="Ставка кальянщику" />
                    <x-text-input id="hookah_maker_rate" name="hookah_maker_rate" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('hookah_maker_rate')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('hookah_maker_rate')" />
                </div>

                <div>
                    <x-input-label for="administrator_rate" value="Ставка администратору" />
                    <x-text-input id="administrator_rate" name="administrator_rate" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('administrator_rate')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('administrator_rate')" />
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

    <!-- Модальное окно редактирования кальяна -->
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
                <h3 class="text-lg font-medium text-gray-900">Изменить кальян</h3>
                <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <template x-if="editHookah">
                <form method="POST" :action="`{{ url('/hookahs') }}/${editHookah.id}`" class="space-y-4" x-init="
                    $watch('editHookah', value => {
                        if (value) {
                            setTimeout(() => {
                                document.getElementById('name_edit')?.value = value.name || '';
                                document.getElementById('price_edit')?.value = value.price || '';
                                document.getElementById('cost_edit')?.value = value.cost || '';
                                document.getElementById('hookah_maker_rate_edit')?.value = value.hookah_maker_rate || '';
                                document.getElementById('administrator_rate_edit')?.value = value.administrator_rate || '';
                            }, 0);
                        }
                    });
                ">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="name_edit" value="Название" />
                        <input id="name_edit" name="name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <x-input-label for="price_edit" value="Цена" />
                        <input id="price_edit" name="price" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
                        <x-input-error class="mt-2" :messages="$errors->get('price')" />
                    </div>

                    <div>
                        <x-input-label for="cost_edit" value="Себестоимость" />
                        <input id="cost_edit" name="cost" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
                        <x-input-error class="mt-2" :messages="$errors->get('cost')" />
                    </div>

                    <div>
                        <x-input-label for="hookah_maker_rate_edit" value="Ставка кальянщику" />
                        <input id="hookah_maker_rate_edit" name="hookah_maker_rate" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
                        <x-input-error class="mt-2" :messages="$errors->get('hookah_maker_rate')" />
                    </div>

                    <div>
                        <x-input-label for="administrator_rate_edit" value="Ставка администратору" />
                        <input id="administrator_rate_edit" name="administrator_rate" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
                        <x-input-error class="mt-2" :messages="$errors->get('administrator_rate')" />
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

    <script>
        window.hookahsPageData = function () {
            return {
                showCreateModal: {{ $errors->any() && !old('_method') ? 'true' : 'false' }},
                showEditModal: false,
                editHookah: null,
                hookahsData: @json($hookahsData),
            };
        };

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


