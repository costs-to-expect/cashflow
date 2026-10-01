<x-layouts.app :title="$resource['name']">
    <div data-period-switcher>
        <x-hero :back="route('dashboard', $currentResourceType)" back-label="Dashboard">
            <div class="mt-3 flex items-center gap-3">
                <span class="grid size-12 shrink-0 place-items-center rounded-full bg-white/15 text-xl font-bold ring-1 ring-white/30">{{ mb_strtoupper(mb_substr($resource['name'], 0, 1)) }}</span>
                <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">{{ $resource['name'] }}</h1>
            </div>

            @if (filled($resource['description']))
                <p class="mt-4 max-w-prose text-[15px] leading-relaxed text-white/80 sm:text-base">{{ $resource['description'] }}</p>
            @endif

            <div class="mt-6 flex flex-wrap items-end justify-between gap-4">
                <div>
                    @foreach ($periodTotals as $entry)
                        <div data-period="{{ $entry['key'] }}" @class(['hidden' => ! $loop->first])>
                            <x-dashboard.amount :totals="$entry['totals']" number="text-4xl font-bold tracking-tight sm:text-5xl" currency="text-xl font-semibold text-white/70" extra="mt-1 text-base font-medium text-white/80" />
                            <p class="mt-1 text-sm text-white/70"><x-dashboard.period-caption :entry="$entry" /></p>
                        </div>
                    @endforeach
                </div>

                <x-dashboard.period-switcher :periods="$periodTotals" class="w-full sm:w-auto" />
            </div>

            {{-- Stacked full-width on mobile, side by side from sm up. --}}
            <div class="mt-6 flex flex-col gap-2 sm:flex-row">
                <x-button variant="hero" :href="route('expenses.create', ['resourceType' => $currentResourceType, 'resource_id' => $resource['id']])">Add Expense for {{ $resource['name'] }}</x-button>
                <x-button variant="ghost" :href="route('recurring.index', $currentResourceType)">Recurring</x-button>
            </div>
        </x-hero>
    </div>

    @if (count($items) === 0)
        <p class="mt-6 text-sm text-gray-600">No expenses recorded{{ $page > 1 ? ' on this page' : ' yet' }}.</p>
    @else
        {{-- Items arrive newest first, so grouping by month keeps them in order. --}}
        @foreach (collect($items)->groupBy(fn ($item) => \Illuminate\Support\Carbon::parse($item['effective_date'])->format('F Y')) as $month => $monthItems)
            <h2 class="mb-2 mt-8 flex items-baseline justify-between text-sm font-semibold uppercase tracking-wide text-gray-500">
                {{ $month }}
                <span class="text-xs font-normal normal-case">{{ $monthItems->count() }} {{ $monthItems->count() === 1 ? 'expense' : 'expenses' }}</span>
            </h2>

            <ul class="divide-y divide-gray-100 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
                @foreach ($monthItems as $item)
                    <li class="px-4 py-3.5 sm:flex sm:items-center sm:gap-4 sm:px-5">
                        <div class="flex flex-1 items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="font-medium text-gray-900">{{ $item['name'] }}</p>
                                @if ($item['description'])
                                    <p class="mt-0.5 text-sm text-gray-600">{{ $item['description'] }}</p>
                                @endif
                                <p class="mt-0.5 text-xs text-gray-500">
                                    {{ \Illuminate\Support\Carbon::parse($item['effective_date'])->format('D j M') }}
                                    @if ($categoriesEnabled && count($item['categories'] ?? []) > 0)
                                        · {{ $item['categories'][0]['name'] }}
                                        @if (count($item['categories'][0]['subcategories'] ?? []) > 0)
                                            &rsaquo; {{ $item['categories'][0]['subcategories'][0]['name'] }}
                                        @endif
                                    @endif
                                </p>
                            </div>

                            <div class="shrink-0 text-right">
                                <span class="block font-semibold tabular-nums text-gray-900">{{ $item['currency']['code'] }} {{ number_format((float) $item['actualised_total'], 2) }}</span>
                                @if ((int) $item['percentage'] !== 100)
                                    <span class="block text-xs text-gray-500">{{ $item['percentage'] }}% of {{ number_format((float) $item['total'], 2) }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="-mb-2 -ml-2 mt-1 flex text-sm sm:m-0 sm:shrink-0">
                            <x-expense.actions :resource="$resource" :item="$item" />
                        </div>
                    </li>
                @endforeach
            </ul>
        @endforeach

        <div class="mt-5 flex justify-between text-sm font-medium">
            @if ($page > 1)
                <a href="{{ route('resources.show', ['resourceType' => $currentResourceType, 'resource_id' => $resource['id'], 'page' => $page - 1]) }}" class="text-brand-700 hover:underline">&larr; Newer</a>
            @else
                <span></span>
            @endif

            @if ($hasMore)
                <a href="{{ route('resources.show', ['resourceType' => $currentResourceType, 'resource_id' => $resource['id'], 'page' => $page + 1]) }}" class="text-brand-700 hover:underline">Older &rarr;</a>
            @endif
        </div>
    @endif

    <script src="{{ asset('js/'.$version['js'].'/period-switcher.js') }}" defer></script>
</x-layouts.app>
