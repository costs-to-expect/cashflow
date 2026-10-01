@props(['title', 'icon'])

{{-- One tile on the landing page: a tinted icon (Heroicons, outline path), a title and the description as the slot. --}}
<div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 sm:p-6">
    <span class="grid size-11 place-items-center rounded-xl bg-brand-tint text-brand-700">
        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" /></svg>
    </span>

    <h3 class="mt-4 text-lg font-semibold text-gray-900">{{ $title }}</h3>
    <p class="mt-1.5 text-sm leading-relaxed text-gray-600">{{ $slot }}</p>
</div>
