@props(['name', 'title', 'required' => false, 'value' => null, 'rows' => 3, 'errorKey' => null])

@php($errorKey ??= $name)

<label for="{{ $name }}" class="block text-sm font-medium text-gray-700">
    {{ $title }}
    @if ($required)<span class="text-red-500">*</span>@endif
</label>
<textarea
    id="{{ $name }}"
    name="{{ $name }}"
    rows="{{ $rows }}"
    @if ($required) required @endif
    {{ $attributes->merge(['class' => 'mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm '.($errors->has($errorKey) ? 'border-red-500 ring-1 ring-red-500' : '')]) }}
>{{ old($errorKey, $value) }}</textarea>
<x-helper.form.error :name="$errorKey" />
