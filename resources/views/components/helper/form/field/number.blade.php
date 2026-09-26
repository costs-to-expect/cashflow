@props(['name', 'title', 'required' => false, 'value' => null, 'step' => 'any', 'min' => null, 'max' => null, 'errorKey' => null])

@php($errorKey ??= $name)

<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">
        {{ $title }}
        @if ($required)<span class="text-red-500">*</span>@endif
    </label>
    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="number"
        step="{{ $step }}"
        @if ($min !== null) min="{{ $min }}" @endif
        @if ($max !== null) max="{{ $max }}" @endif
        @if ($required) required @endif
        value="{{ old($errorKey, $value) }}"
        {{ $attributes->merge(['class' => 'mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm '.($errors->has($errorKey) ? 'border-red-500 ring-1 ring-red-500' : '')]) }}
    />
</div>
