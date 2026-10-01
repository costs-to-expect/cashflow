{{--
    A static, made-up version of the dashboard for the landing page. It's assembled from the same
    components the real dashboard uses, so it can't drift from the app's look. It's decoration only:
    inert (nothing focusable or clickable) and hidden from assistive tech, with the caption below it
    saying what it is.
--}}
@php
    $periods = [
        ['key' => 'year', 'name' => 'Year'],
        ['key' => 'quarter', 'name' => 'Quarter'],
        ['key' => 'all', 'name' => 'All time'],
    ];

    $caption = [
        'name' => 'Year',
        'starts_on' => now()->startOfYear(),
        'ends_on' => now()->endOfYear(),
    ];

    $combined = [
        ['currency' => 'GBP', 'total' => '6,418.50'],
        ['currency' => 'USD', 'total' => '312.00'],
    ];

    // Shares and totals add up to the combined figures above.
    $resources = [
        [
            'id' => 'atlas',
            'name' => 'Atlas',
            'share' => 60,
            'totals' => [['currency' => 'GBP', 'total' => '3,851.10'], ['currency' => 'USD', 'total' => '187.20']],
            'recent' => [
                ['name' => 'Cloud hosting', 'days' => 1, 'category' => 'Infrastructure', 'amount' => '72.00', 'split' => '60% of 120.00'],
                ['name' => 'Design tools', 'days' => 3, 'category' => 'Software', 'amount' => '38.00'],
                ['name' => 'Contractor invoice', 'days' => 9, 'category' => 'Services', 'amount' => '240.00'],
            ],
        ],
        [
            'id' => 'beacon',
            'name' => 'Beacon',
            'share' => 40,
            'totals' => [['currency' => 'GBP', 'total' => '2,567.40'], ['currency' => 'USD', 'total' => '124.80']],
            'recent' => [
                ['name' => 'Cloud hosting', 'days' => 1, 'category' => 'Infrastructure', 'amount' => '48.00', 'split' => '40% of 120.00'],
                ['name' => 'Test devices', 'days' => 5, 'category' => 'Equipment', 'amount' => '145.00'],
                ['name' => 'Team lunch', 'days' => 12, 'category' => 'Meals', 'amount' => '62.00'],
            ],
        ],
    ];

    $colours = \App\Support\ResourceColours::forResources($resources);
@endphp

<figure {{ $attributes->class('relative') }}>
    <div aria-hidden="true" inert class="overflow-hidden rounded-2xl bg-gray-50 shadow-2xl ring-1 ring-gray-900/10 sm:rounded-b-none">
        {{-- Window chrome. --}}
        <div class="flex items-center gap-1.5 border-b border-gray-200 bg-white px-4 py-3">
            <span class="size-2.5 rounded-full bg-gray-200"></span>
            <span class="size-2.5 rounded-full bg-gray-200"></span>
            <span class="size-2.5 rounded-full bg-gray-200"></span>
        </div>

        {{-- The app's own nav. --}}
        <div class="flex items-center justify-between gap-4 bg-brand-700 px-4 py-3 sm:px-5">
            <div class="flex items-center gap-5">
                <span class="text-sm font-semibold text-white sm:text-base">{{ config('app.name') }}</span>

                <div class="hidden gap-4 text-sm text-white/70 md:flex">
                    @foreach ($resources as $resource)
                        <span>{{ $resource['name'] }}</span>
                    @endforeach
                    <span>Recurring</span>
                    <span>Settings</span>
                </div>
            </div>

            <div class="hidden items-center gap-3 sm:flex">
                <span class="inline-flex items-center gap-5 rounded-md bg-brand-600 py-1 pl-2 pr-1.5 text-sm text-white">
                    Work
                    <svg class="size-4 text-white/60" fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 8l4 4 4-4" /></svg>
                </span>
                <span class="rounded-xl border border-white/30 px-3 py-1.5 text-sm font-medium text-white">+ New</span>
            </div>
        </div>

        <div class="overflow-hidden p-3 sm:max-h-[50rem] sm:p-6">
            <x-hero>
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <p class="text-sm font-medium text-white/70">{{ count($resources) }} Projects · combined</p>

                    <x-dashboard.period-switcher :periods="$periods" class="w-full sm:w-auto" />
                </div>

                <div class="mt-6">
                    <x-dashboard.amount :totals="$combined" number="text-4xl font-bold tracking-tight sm:text-6xl" currency="text-xl font-semibold text-white/70 sm:text-3xl" extra="mt-1 text-lg font-medium text-white/80" />

                    <p class="mt-2 text-sm text-white/70"><x-dashboard.period-caption :entry="$caption" /></p>

                    <div class="mt-6 flex h-2.5 overflow-hidden rounded-full bg-white/20">
                        @foreach ($resources as $resource)
                            <span style="width: {{ $resource['share'] }}%; background: {{ $colours[$resource['id']]['hex'] }}"></span>
                        @endforeach
                    </div>

                    <div class="mt-3 flex flex-wrap gap-x-5 gap-y-1 text-sm text-white/80">
                        @foreach ($resources as $resource)
                            <span class="inline-flex items-center gap-2">
                                <span class="size-2.5 rounded-full" style="background: {{ $colours[$resource['id']]['hex'] }}"></span>
                                {{ $resource['name'] }}
                                <strong class="font-semibold text-white">{{ $resource['share'] }}%</strong>
                            </span>
                        @endforeach
                    </div>
                </div>

                <div class="mt-7 flex flex-wrap gap-2">
                    <x-button variant="hero">Add expense</x-button>
                    <x-button variant="ghost">Add split expense</x-button>
                    <span class="hidden sm:contents"><x-button variant="ghost">Recurring</x-button></span>
                </div>
            </x-hero>

            <div class="mt-6 hidden gap-6 sm:grid md:grid-cols-2">
                @foreach ($resources as $resource)
                    <article @class(['rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200', 'hidden md:block' => ! $loop->first])>
                        <header class="flex items-center justify-between gap-3">
                            <h3 class="flex items-center gap-2.5 text-lg font-semibold text-gray-900">
                                <span class="size-3 shrink-0 rounded-full {{ $colours[$resource['id']]['bar'] }}"></span>
                                {{ $resource['name'] }}
                            </h3>
                            <span class="text-sm font-medium text-brand-700">View all &rarr;</span>
                        </header>

                        <div class="mt-4">
                            <x-dashboard.amount :totals="$resource['totals']" number="text-3xl font-bold tracking-tight text-gray-900" currency="text-base font-semibold text-gray-400" extra="text-sm font-medium text-gray-500" />

                            <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full {{ $colours[$resource['id']]['bar'] }}" style="width: {{ $resource['share'] }}%"></div>
                            </div>
                            <p class="mt-1.5 text-xs text-gray-500"><strong class="font-semibold text-gray-700">{{ $resource['share'] }}%</strong> of the combined total</p>
                        </div>

                        <h4 class="mb-1 mt-5 text-xs font-semibold uppercase tracking-wide text-gray-500">Recent</h4>

                        <ul class="divide-y divide-gray-100">
                            @foreach ($resource['recent'] as $expense)
                                <li class="flex items-start justify-between gap-4 py-2.5">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-gray-900">{{ $expense['name'] }}</p>
                                        <p class="text-xs text-gray-500">{{ now()->subDays($expense['days'])->format('j M') }} · {{ $expense['category'] }}</p>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <span class="block text-sm font-semibold tabular-nums text-gray-900">GBP {{ $expense['amount'] }}</span>
                                        @isset($expense['split'])
                                            <span class="block text-xs text-gray-500">{{ $expense['split'] }}</span>
                                        @endisset
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </article>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Fades the cropped dashboard into the page. It runs past the bottom edge so the window's shadow is covered too. --}}
    <div class="pointer-events-none absolute -bottom-16 -inset-x-3 hidden h-56 sm:block bg-linear-to-t from-gray-50 from-25% via-gray-50/90 to-transparent"></div>

    <figcaption class="sr-only">An example Cashflow dashboard, using made-up figures: a combined total for two projects, a bar showing each project's share, and their most recent expenses.</figcaption>
</figure>
