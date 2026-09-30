<x-layouts.app title="Dashboard">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-2">
        <h1 class="text-lg font-semibold text-gray-900">Dashboard</h1>
        <div class="flex flex-wrap gap-2">
            <x-button :href="route('expenses.create', $currentResourceType)">Add expense</x-button>
            <x-button variant="secondary" :href="route('expenses.create', ['resourceType' => $currentResourceType, 'split' => 1])">Add split expense</x-button>
            <x-button variant="secondary" :href="route('recurring.index', $currentResourceType)">Recurring</x-button>
            <x-button variant="secondary" :href="route('resources.create', $currentResourceType)">Add {{ strtolower($resourceTermSingular) }}</x-button>
        </div>
    </div>

    @if ($apiError)
        <p class="text-sm text-red-600">We couldn't reach the Costs to Expect API, please try again shortly.</p>
    @elseif (count($resources) === 0)
        <p class="text-sm text-gray-600">No {{ strtolower($resourceTermPlural) }} set up yet. <a href="{{ route('resources.create', $currentResourceType) }}" class="text-indigo-600 hover:underline">Add one</a> to get started.</p>
    @else
        <div class="mb-6 rounded-lg border border-t-4 border-gray-200 border-t-brand-700 bg-white p-4 shadow-sm">
            <h2 class="mb-3 text-lg font-semibold text-brand-700">
                {{ count($resources) }} {{ count($resources) === 1 ? $resourceTermSingular : $resourceTermPlural }}
            </h2>

            @if (count($overallPeriodTotals) > 0)
                {{-- Tiles grow to share the row, wrapping to a new one when they'd drop below 16rem. --}}
                <div class="flex flex-wrap gap-3 *:min-w-64 *:flex-1">
                    <x-dashboard.period-totals :periods="$overallPeriodTotals" />
                </div>
            @endif
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
            @foreach ($resources as $resource)
                <div class="rounded-lg border border-t-4 border-gray-200 border-t-brand-700 bg-white p-4 shadow-sm">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <h2 class="text-lg font-semibold text-brand-700">
                            <a href="{{ route('resources.show', [$currentResourceType, $resource['id']]) }}" class="hover:text-brand-900 hover:underline">{{ $resource['name'] }}</a>
                        </h2>
                        <a href="{{ route('resources.show', [$currentResourceType, $resource['id']]) }}" class="text-xs font-medium text-brand-700 hover:underline">View all</a>
                    </div>

                    @php($periodTotals = $periodTotalsByResource[$resource['id']] ?? [])

                    @if (count($periodTotals) > 0)
                        <div class="mb-4 space-y-3 border-b border-gray-100 pb-4">
                            <x-dashboard.period-totals :periods="$periodTotals" />
                        </div>
                    @endif

                    @php($recent = $recentByResource[$resource['id']] ?? [])

                    @if (count($recent) === 0)
                        <p class="text-sm text-gray-500">No expenses recorded yet.</p>
                    @else
                        <ul class="divide-y divide-gray-100">
                            @foreach ($recent as $expense)
                                <li class="flex items-center justify-between py-2 text-sm">
                                    <div>
                                        <p class="text-gray-900">{{ $expense['name'] }}</p>
                                        <p class="text-gray-500">{{ \Illuminate\Support\Carbon::parse($expense['effective_date'])->format('j M Y') }}</p>
                                        @if (count($expense['categories'] ?? []) > 0)
                                            <p class="text-xs text-gray-400">
                                                {{ $expense['categories'][0]['name'] }}
                                                @if (count($expense['categories'][0]['subcategories'] ?? []) > 0)
                                                    &rsaquo; {{ $expense['categories'][0]['subcategories'][0]['name'] }}
                                                @endif
                                            </p>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <p class="font-medium text-gray-900">{{ $expense['currency']['code'] }} {{ $expense['actualised_total'] }}</p>
                                        @if ((int) $expense['percentage'] !== 100)
                                            <p class="text-xs text-gray-500">{{ $expense['percentage'] }}% of {{ $expense['total'] }}</p>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.app>
