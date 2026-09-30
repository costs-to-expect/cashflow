@props(['variant' => 'primary', 'href' => null])

@php
    // Tailwind v4 no longer gives <button> a pointer cursor, so it's set
    // explicitly here, along with visible hover/focus states.
    $classes = match ($variant) {
        'secondary' => 'border border-gray-300 bg-white text-gray-700 hover:border-brand-700 hover:bg-gray-50 hover:text-brand-700',
        'danger' => 'bg-red-600 text-white hover:bg-red-700',
        // For use on the dark brand-gradient hero panels.
        'hero' => 'bg-white text-brand-800 hover:bg-brand-tint',
        'ghost' => 'bg-white/10 text-white ring-1 ring-white/30 hover:bg-white/20',
        default => 'bg-brand-700 text-white hover:bg-brand-900',
    };

    $classes = "inline-flex h-10 cursor-pointer items-center justify-center whitespace-nowrap rounded-md px-4 text-sm font-medium shadow-sm transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700 $classes";
@endphp

{{-- With an href it renders as a link styled like a button - a <button> nested inside an <a> is invalid HTML. --}}
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['type' => 'submit', 'class' => $classes]) }}>{{ $slot }}</button>
@endif
