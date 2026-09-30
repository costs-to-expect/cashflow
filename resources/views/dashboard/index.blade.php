<x-layouts.app title="Dashboard">
    <h1 class="sr-only">Dashboard</h1>

    @if ($apiError)
        <p class="text-sm text-red-600">We couldn't reach the Costs to Expect API, please try again shortly.</p>
    @elseif (count($resources) === 0)
        <p class="text-sm text-gray-600">No {{ strtolower($resourceTermPlural) }} set up yet. <a href="{{ route('resources.create', $currentResourceType) }}" class="text-brand-700 hover:underline">Add one</a> to get started.</p>
    @else
        @php
            // Colour identity per resource, reused in the hero's share bar and on each card. Full class
            // strings and hexes (not built up from names) so Tailwind can see them; "hex" is the lighter
            // shade that stays legible on the dark hero.
            $palette = [
                ['bar' => 'bg-indigo-500', 'hex' => '#a5b4fc'],
                ['bar' => 'bg-rose-500', 'hex' => '#fda4af'],
                ['bar' => 'bg-emerald-500', 'hex' => '#6ee7b7'],
                ['bar' => 'bg-amber-500', 'hex' => '#fcd34d'],
                ['bar' => 'bg-sky-500', 'hex' => '#7dd3fc'],
                ['bar' => 'bg-orange-500', 'hex' => '#fdba74'],
            ];

            $colours = [];
            foreach (array_values($resources) as $index => $resource) {
                $colours[$resource['id']] = $palette[$index % count($palette)];
            }

            $showShares = count($resources) > 1;
        @endphp

        <div data-period-switcher>
            <section class="relative overflow-hidden rounded-3xl bg-linear-to-br from-brand-900 via-brand-700 to-fuchsia-700 p-5 text-white shadow-lg sm:p-8">
                <div class="pointer-events-none absolute -right-16 -top-16 size-64 rounded-full bg-white/10 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-24 left-1/3 size-72 rounded-full bg-fuchsia-400/20 blur-3xl"></div>

                <div class="relative">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <p class="text-sm font-medium text-white/70">
                            {{ count($resources) }} {{ count($resources) === 1 ? $resourceTermSingular : $resourceTermPlural }}@if ($showShares) · combined @endif
                        </p>

                        <x-dashboard.period-switcher :periods="$overallPeriodTotals" class="w-full sm:w-auto" />
                    </div>

                    <div class="mt-6">
                        @foreach ($overallPeriodTotals as $entry)
                            @php($periodIndex = $loop->index)

                            <div data-period="{{ $entry['key'] }}" @class(['hidden' => ! $loop->first])>
                                <x-dashboard.amount :totals="$entry['totals']" number="text-5xl font-bold tracking-tight sm:text-6xl" currency="text-2xl font-semibold text-white/70 sm:text-3xl" extra="mt-1 text-lg font-medium text-white/80" />

                                <p class="mt-2 text-sm text-white/70"><x-dashboard.period-caption :entry="$entry" /></p>

                                @if ($showShares && collect($resources)->sum(fn ($resource) => $periodTotalsByResource[$resource['id']][$periodIndex]['share'] ?? 0) > 0)
                                    <div class="mt-6 flex h-2.5 overflow-hidden rounded-full bg-white/20">
                                        @foreach ($resources as $resource)
                                            <span style="width: {{ $periodTotalsByResource[$resource['id']][$periodIndex]['share'] ?? 0 }}%; background: {{ $colours[$resource['id']]['hex'] }}"></span>
                                        @endforeach
                                    </div>

                                    <div class="mt-3 flex flex-wrap gap-x-5 gap-y-1 text-sm text-white/80">
                                        @foreach ($resources as $resource)
                                            <span class="inline-flex items-center gap-2">
                                                <span class="size-2.5 rounded-full" style="background: {{ $colours[$resource['id']]['hex'] }}"></span>
                                                {{ $resource['name'] }}
                                                <strong class="font-semibold text-white">{{ $periodTotalsByResource[$resource['id']][$periodIndex]['share'] ?? 0 }}%</strong>
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-7 flex flex-wrap gap-2">
                        <x-button variant="hero" :href="route('expenses.create', $currentResourceType)">Add expense</x-button>
                        <x-button variant="ghost" :href="route('expenses.create', ['resourceType' => $currentResourceType, 'split' => 1])">Add split expense</x-button>
                        <x-button variant="ghost" :href="route('recurring.index', $currentResourceType)">Recurring</x-button>
                        <x-button variant="ghost" :href="route('resources.create', $currentResourceType)">Add {{ strtolower($resourceTermSingular) }}</x-button>
                    </div>
                </div>
            </section>

            <div class="mt-6 grid gap-6 md:grid-cols-2">
                @foreach ($resources as $resource)
                    @php($resourcePeriods = $periodTotalsByResource[$resource['id']] ?? [])
                    @php($recent = $recentByResource[$resource['id']] ?? [])

                    <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                        <header class="flex items-center justify-between gap-3">
                            <h2 class="flex items-center gap-2.5 text-lg font-semibold text-gray-900">
                                <span class="size-3 shrink-0 rounded-full {{ $colours[$resource['id']]['bar'] }}"></span>
                                <a href="{{ route('resources.show', [$currentResourceType, $resource['id']]) }}" class="hover:text-brand-700">{{ $resource['name'] }}</a>
                            </h2>
                            <a href="{{ route('resources.show', [$currentResourceType, $resource['id']]) }}" class="text-sm font-medium text-brand-700 hover:underline">View all &rarr;</a>
                        </header>

                        @foreach ($resourcePeriods as $entry)
                            <div data-period="{{ $entry['key'] }}" @class(['mt-4', 'hidden' => ! $loop->first])>
                                <x-dashboard.amount :totals="$entry['totals']" number="text-3xl font-bold tracking-tight text-gray-900" currency="text-base font-semibold text-gray-400" extra="text-sm font-medium text-gray-500" />

                                @if ($showShares)
                                    <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-gray-100">
                                        <div class="h-full rounded-full {{ $colours[$resource['id']]['bar'] }}" style="width: {{ $entry['share'] ?? 0 }}%"></div>
                                    </div>
                                    <p class="mt-1.5 text-xs text-gray-500"><strong class="font-semibold text-gray-700">{{ $entry['share'] ?? 0 }}%</strong> of the combined total</p>
                                @endif
                            </div>
                        @endforeach

                        <h3 class="mb-1 mt-5 text-xs font-semibold uppercase tracking-wide text-gray-500">Recent</h3>

                        @if (count($recent) === 0)
                            <p class="text-sm text-gray-500">No expenses recorded yet.</p>
                        @else
                            <ul class="divide-y divide-gray-100">
                                @foreach ($recent as $expense)
                                    <li class="flex items-start justify-between gap-4 py-2.5">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-medium text-gray-900">{{ $expense['name'] }}</p>
                                            <p class="text-xs text-gray-500">
                                                {{ \Illuminate\Support\Carbon::parse($expense['effective_date'])->format('j M') }}
                                                @if (count($expense['categories'] ?? []) > 0)
                                                    · {{ $expense['categories'][0]['subcategories'][0]['name'] ?? $expense['categories'][0]['name'] }}
                                                @endif
                                            </p>
                                        </div>
                                        <div class="shrink-0 text-right">
                                            <span class="block text-sm font-semibold tabular-nums text-gray-900">{{ $expense['currency']['code'] }} {{ number_format((float) $expense['actualised_total'], 2) }}</span>
                                            @if ((int) $expense['percentage'] !== 100)
                                                <span class="block text-xs text-gray-500">{{ $expense['percentage'] }}% of {{ number_format((float) $expense['total'], 2) }}</span>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </article>
                @endforeach
            </div>
        </div>

        <script src="{{ asset('js/'.$version['js'].'/period-switcher.js') }}" defer></script>
    @endif
</x-layouts.app>
