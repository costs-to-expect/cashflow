@props(['name', 'title', 'options', 'required' => false, 'value' => null, 'hint' => null, 'errorKey' => null])

@php($errorKey ??= $name)

<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">
        {{ $title }}
        @if ($required)<span class="text-red-500">*</span>@endif
    </label>
    <select
        id="{{ $name }}"
        name="{{ $name }}"
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'form-control mt-1.5 pr-10 '.($errors->has($errorKey) ? 'form-control-error' : '')]) }}
    >
        @foreach ($options as $optionValue => $label)
            <option value="{{ $optionValue }}" @selected((string) old($errorKey, $value) === (string) $optionValue)>{{ $label }}</option>
        @endforeach
    </select>
    @if ($hint)<p class="mt-1.5 text-xs text-gray-500">{{ $hint }}</p>@endif
</div>
