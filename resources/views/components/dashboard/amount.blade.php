@props(['totals', 'number' => '', 'currency' => '', 'extra' => ''])

{{-- Headline amount for the first currency; any others follow, smaller, and are never added together. --}}
@forelse ($totals as $row)
    @if ($loop->first)
        <span class="block tabular-nums {{ $number }}"><span class="mr-1.5 {{ $currency }}">{{ $row['currency'] }}</span>{{ $row['total'] }}</span>
    @else
        <span class="block tabular-nums {{ $extra }}">+ {{ $row['currency'] }} {{ $row['total'] }}</span>
    @endif
@empty
    <span class="block tabular-nums {{ $number }}"><span class="mr-1.5 {{ $currency }}">GBP</span>0.00</span>
@endforelse
