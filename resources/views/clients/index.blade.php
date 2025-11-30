<x-app-layout x-data="clientsPageData()">
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Клиенты
            </h2>
            <button type="button" @click="showCreateModal = true" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Добавить клиента
            </button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Поиск -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4">
                <div class="p-6">
                    <form method="GET" action="{{ route('clients.index') }}" class="space-y-4">
                        <div>
                            <x-input-label for="search" value="Поиск по имени или номеру телефона" />
                            <div class="flex gap-2 mt-1">
                                <x-text-input id="search" name="search" type="text" class="block w-full" :value="request('search')" placeholder="Введите имя или номер телефона..." />
                                <button type="submit" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded whitespace-nowrap">
                                    Поиск
                                </button>
                            </div>
                        </div>
                        @if(request('search'))
                            <div>
                                <a href="{{ route('clients.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                                    Сбросить поиск
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
                                        Имя
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Номер телефона
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Дата рождения
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Комментарий
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Действия
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($clients as $client)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $client->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $client->phone }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $client->birth_date ? $client->birth_date->format('d.m.Y') : '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            <div class="max-w-xs truncate" title="{{ $client->comment }}">
                                                {{ $client->comment ?: '-' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button type="button" @click="
                                                editClient = clientsData.find(c => c.id === {{ $client->id }});
                                                showEditModal = true;
                                            " class="text-indigo-600 hover:text-indigo-900 mr-3">
                                                Изменить
                                            </button>
                                            <form action="{{ route('clients.destroy', $client) }}" method="POST" class="inline" onsubmit="return confirm('Вы уверены, что хотите удалить этого клиента?');">
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
                                            Клиенты не найдены. Добавьте первого клиента.
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

    <!-- Модальное окно создания клиента -->
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
                <h3 class="text-lg font-medium text-gray-900">Добавить клиента</h3>
                <button @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form method="POST" action="{{ route('clients.store') }}" class="space-y-4">
                @csrf

                <div>
                    <x-input-label for="name" value="Имя" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>

                <div>
                    <x-input-label for="phone" value="Номер телефона" />
                    <x-text-input id="phone" name="phone" type="tel" class="mt-1 block w-full" :value="old('phone')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                </div>

                <div>
                    <x-input-label for="birth_date" value="Дата рождения" />
                    <x-text-input id="birth_date" name="birth_date" type="date" class="mt-1 block w-full" :value="old('birth_date')" />
                    <x-input-error class="mt-2" :messages="$errors->get('birth_date')" />
                </div>

                <div>
                    <x-input-label for="comment" value="Комментарий" />
                    <textarea id="comment" name="comment" rows="3" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none">{{ old('comment') }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('comment')" />
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

    <!-- Модальное окно редактирования клиента -->
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
                <h3 class="text-lg font-medium text-gray-900">Изменить клиента</h3>
                <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <template x-if="editClient">
                <form method="POST" :action="`{{ url('/clients') }}/${editClient.id}`" class="space-y-4" x-init="
                    setTimeout(() => {
                        if (editClient) {
                            document.getElementById('name_edit').value = editClient.name || '';
                            document.getElementById('phone_edit').value = editClient.phone || '';
                            document.getElementById('birth_date_edit').value = editClient.birth_date || '';
                            document.getElementById('comment_edit').value = editClient.comment || '';
                        }
                    }, 100);
                ">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="name_edit" value="Имя" />
                        <input id="name_edit" name="name" type="text" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <x-input-label for="phone_edit" value="Номер телефона" />
                        <input id="phone_edit" name="phone" type="tel" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none" required />
                        <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                    </div>

                    <div>
                        <x-input-label for="birth_date_edit" value="Дата рождения" />
                        <input id="birth_date_edit" name="birth_date" type="date" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none" />
                        <x-input-error class="mt-2" :messages="$errors->get('birth_date')" />
                    </div>

                    <div>
                        <x-input-label for="comment_edit" value="Комментарий" />
                        <textarea id="comment_edit" name="comment" rows="3" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none"></textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('comment')" />
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
        window.clientsPageData = function () {
            return {
                showCreateModal: {{ $errors->any() && !old('_method') ? 'true' : 'false' }},
                showEditModal: false,
                editClient: null,
                clientsData: @json($clientsData),
            };
        };

        document.addEventListener('DOMContentLoaded', function() {
            @if (session('success'))
                toastr.success(@json(session('success')));
                // Reload page after success to refresh the list
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
