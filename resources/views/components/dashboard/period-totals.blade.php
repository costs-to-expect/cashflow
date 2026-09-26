@props(['periods'])

@foreach ($periods as $entry)
    <div>
        <div class="flex items-start justify-between">
            <span class="text-sm font-medium text-gray-700">{{ $entry['name'] }}</span>
            <span class="text-right">
                @forelse ($entry['totals'] as $currencyTotal)
                    <span class="block text-base font-semibold text-gray-900">{{ $currencyTotal['currency'] }} {{ $currencyTotal['total'] }}</span>
                @empty
                    <span class="block text-base font-semibold text-gray-900">GBP 0.00</span>
                @endforelse
            </span>
        </div>
        <p class="text-xs text-gray-400">
            {{ $entry['starts_on']->format('F jS Y') }} &rarr; {{ $entry['ends_on']->format('F jS Y') }} (current period)
        </p>
    </div>
@endforeach
