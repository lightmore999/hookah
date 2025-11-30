<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Изменить кальян
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('hookahs.update', $hookah) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="name" value="Название" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $hookah->name)" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div>
                            <x-input-label for="price" value="Цена" />
                            <x-text-input id="price" name="price" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('price', $hookah->price)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('price')" />
                        </div>

                        <div>
                            <x-input-label for="cost" value="Себестоимость" />
                            <x-text-input id="cost" name="cost" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('cost', $hookah->cost)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('cost')" />
                        </div>

                        <div>
                            <x-input-label for="hookah_maker_rate" value="Ставка кальянщику" />
                            <x-text-input id="hookah_maker_rate" name="hookah_maker_rate" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('hookah_maker_rate', $hookah->hookah_maker_rate)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('hookah_maker_rate')" />
                        </div>

                        <div>
                            <x-input-label for="administrator_rate" value="Ставка администратору" />
                            <x-text-input id="administrator_rate" name="administrator_rate" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('administrator_rate', $hookah->administrator_rate)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('administrator_rate')" />
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>Сохранить</x-primary-button>
                            <a href="{{ route('hookahs.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
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


