<x-layouts.app title="Dashboard">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-2">
        <h1 class="text-lg font-semibold text-gray-900">Dashboard</h1>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('expenses.create') }}"><x-button>Add expense</x-button></a>
            <a href="{{ route('expenses.create', ['split' => 1]) }}"><x-button variant="secondary">Add split expense</x-button></a>
            <a href="{{ route('recurring.index') }}"><x-button variant="secondary">Recurring</x-button></a>
            <a href="{{ route('resources.create') }}"><x-button variant="secondary">Add {{ strtolower($resourceTermSingular) }}</x-button></a>
        </div>
    </div>

    @if ($apiError)
        <p class="text-sm text-red-600">We couldn't reach the Costs to Expect API, please try again shortly.</p>
    @elseif (count($resources) === 0)
        <p class="text-sm text-gray-600">No {{ strtolower($resourceTermPlural) }} set up yet. <a href="{{ route('resources.create') }}" class="text-indigo-600 hover:underline">Add one</a> to get started.</p>
    @else
        <div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <p class="mb-3 text-sm font-semibold text-gray-900">
                {{ count($resources) }} {{ count($resources) === 1 ? $resourceTermSingular : $resourceTermPlural }}
            </p>

            @if (count($overallPeriodTotals) > 0)
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-dashboard.period-totals :periods="$overallPeriodTotals" />
                </div>
            @endif
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
            @foreach ($resources as $resource)
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="mb-3 flex items-center justify-between">
                        <a href="{{ route('resources.show', $resource['id']) }}" class="font-medium text-gray-900 hover:text-indigo-600">{{ $resource['name'] }}</a>
                        <a href="{{ route('resources.show', $resource['id']) }}" class="text-xs text-indigo-600 hover:underline">View all</a>
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
