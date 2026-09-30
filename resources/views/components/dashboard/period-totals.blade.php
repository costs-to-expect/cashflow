@props(['periods'])

@php
    // Full class strings, not built up from a colour name - Tailwind only
    // generates classes it can see written out in full.
    $palette = [
        ['tile' => 'border-purple-200 border-l-purple-500 bg-purple-50', 'name' => 'text-purple-800'],
        ['tile' => 'border-sky-200 border-l-sky-500 bg-sky-50', 'name' => 'text-sky-800'],
        ['tile' => 'border-emerald-200 border-l-emerald-500 bg-emerald-50', 'name' => 'text-emerald-800'],
        ['tile' => 'border-amber-200 border-l-amber-500 bg-amber-50', 'name' => 'text-amber-800'],
    ];

    $allTime = ['tile' => 'border-slate-300 border-l-slate-600 bg-slate-100', 'name' => 'text-slate-800'];
@endphp

@foreach ($periods as $entry)
    @php($colours = ($entry['all_time'] ?? false) ? $allTime : $palette[$loop->index % count($palette)])

    <div class="rounded-md border border-l-4 p-3 {{ $colours['tile'] }}">
        <div class="flex items-start justify-between gap-3">
            <span class="text-sm font-semibold {{ $colours['name'] }}">{{ $entry['name'] }}</span>
            <span class="text-right">
                @forelse ($entry['totals'] as $currencyTotal)
                    <span class="block text-base font-semibold text-gray-900">{{ $currencyTotal['currency'] }} {{ $currencyTotal['total'] }}</span>
                @empty
                    <span class="block text-base font-semibold text-gray-900">GBP 0.00</span>
                @endforelse
            </span>
        </div>
        <p class="mt-1 text-xs text-gray-600">
            @if ($entry['starts_on'] && $entry['ends_on'])
                {{ $entry['starts_on']->format('F jS Y') }} &rarr; {{ $entry['ends_on']->format('F jS Y') }} (current period)
            @else
                Everything recorded to date
            @endif
        </p>
    </div>
@endforeach
