@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'px-3 py-2 border border-gray-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-300 focus:outline-none rounded-md shadow-sm']) !!}>
