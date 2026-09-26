@props(['variant' => 'primary'])

@php
    $classes = match ($variant) {
        'secondary' => 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50',
        'danger' => 'bg-red-600 text-white hover:bg-red-700',
        default => 'bg-indigo-600 text-white hover:bg-indigo-700',
    };
@endphp

<button {{ $attributes->merge(['type' => 'submit', 'class' => "inline-flex h-10 items-center justify-center whitespace-nowrap rounded-md px-4 text-sm font-medium shadow-sm $classes"]) }}>
    {{ $slot }}
</button>
