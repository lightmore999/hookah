<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Главная страница
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold mb-4">Добро пожаловать в CRM кальянной Hookah</h1>
                    <p class="mb-4">Выберите раздел в меню для работы с системой:</p>
                    <ul class="list-disc list-inside space-y-2">
                        <li><strong>Столы</strong> - управление столами кальянной</li>
                        <li><strong>Клиенты</strong> - база клиентов</li>
                        <li><strong>Продажи</strong> - учет продаж</li>
                        <li><strong>Товары/Кальяны</strong> - каталог товаров и кальянов</li>
                        <li><strong>Склад</strong> - управление складом</li>
                        <li><strong>Бухгалтерия</strong> - финансовый учет</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>








