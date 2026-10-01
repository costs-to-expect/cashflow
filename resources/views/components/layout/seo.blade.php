@props([
    'title' => config('app.name'),
    'description' => null,
    'index' => false,
])

{{-- Page-level SEO tags shared by every layout. Pages are noindex unless they opt in. --}}
<title>{{ $title }}</title>
@if ($description)
    <meta name="description" content="{{ $description }}">
@endif
<meta name="robots" content="{{ $index ? 'index, follow' : 'noindex, nofollow' }}">
@if ($index)
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Costs to Expect">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $title }}">
    @if ($description)
        <meta property="og:description" content="{{ $description }}">
        <meta name="twitter:description" content="{{ $description }}">
    @endif
@endif
