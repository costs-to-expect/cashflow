@props(['back' => null, 'backLabel' => 'Back', 'eyebrow' => null, 'title' => null, 'description' => null, 'compact' => false])

{{--
    The brand-gradient panel that opens every page. The optional props render the standard header
    (back link, eyebrow, title, description); the slot follows it, for anything page-specific like
    an amount field, totals or actions.
--}}
<section {{ $attributes->class(['relative overflow-hidden rounded-3xl bg-linear-to-br from-brand-900 via-brand-700 to-fuchsia-700 text-white shadow-lg', 'p-6 sm:p-8' => ! $compact, 'p-5 sm:p-6' => $compact]) }}>
    <div class="pointer-events-none absolute -right-16 -top-16 size-64 rounded-full bg-white/10 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-24 left-1/3 size-72 rounded-full bg-fuchsia-400/20 blur-3xl"></div>

    <div class="relative">
        @if ($back)
            <a href="{{ $back }}" class="text-sm font-medium text-white/70 hover:text-white">&larr; {{ $backLabel }}</a>
        @endif

        @if ($eyebrow)
            <p @class(['text-sm font-medium text-white/70', 'mt-4' => $back])>{{ $eyebrow }}</p>
        @endif

        @if ($title)
            <h1 @class(['mt-1 font-bold tracking-tight', 'text-3xl sm:text-4xl' => ! $compact, 'text-2xl sm:text-3xl' => $compact])>{{ $title }}</h1>
        @endif

        @if ($description)
            <p class="mt-3 max-w-prose text-[15px] leading-relaxed text-white/80">{{ $description }}</p>
        @endif

        {{ $slot }}
    </div>
</section>
