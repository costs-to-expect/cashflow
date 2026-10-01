@props(['periods'])

{{-- Buttons for the period switcher - see public/js/*/period-switcher.js. The first period is selected by default. --}}
@if (count($periods) > 1)
    <div role="group" aria-label="Reporting period" {{ $attributes->merge(['class' => 'inline-flex rounded-full bg-black/25 p-1']) }}>
        @foreach ($periods as $period)
            <button type="button" data-period-tab="{{ $period['key'] }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}" class="flex-1 cursor-pointer whitespace-nowrap rounded-full px-3 py-1.5 text-sm font-medium text-white/80 transition-colors hover:text-white aria-pressed:bg-white aria-pressed:text-brand-800 aria-pressed:shadow sm:px-4">{{ $period['name'] }}</button>
        @endforeach
    </div>
@endif
