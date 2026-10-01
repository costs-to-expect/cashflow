@props(['name', 'title', 'required' => false, 'value' => null, 'rows' => 3, 'placeholder' => null, 'hint' => null, 'errorKey' => null])

@php($errorKey ??= $name)

<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">
        {{ $title }}
        @if ($required)<span class="text-red-500">*</span>@endif
    </label>
    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        @if ($required) required @endif
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge(['class' => 'form-control mt-1.5 '.($errors->has($errorKey) ? 'form-control-error' : '')]) }}
    >{{ old($errorKey, $value) }}</textarea>
    @if ($hint)<p class="mt-1.5 text-xs text-gray-500">{{ $hint }}</p>@endif
</div>
