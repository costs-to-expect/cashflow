@props(['entry'])

@php
    $start = $entry['starts_on'];
    $end = $entry['ends_on'];

    // "1 Jan → 31 Dec 2026" when the window sits in one year, otherwise both years are spelled out.
    $range = $start && $end
        ? $start->format($start->year === $end->year ? 'j M' : 'j M Y').' → '.$end->format('j M Y')
        : 'Everything recorded to date';
@endphp

{{ $entry['name'] }} · {{ $range }}
