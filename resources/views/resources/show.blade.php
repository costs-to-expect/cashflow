<x-layouts.app :title="$resource['name']">
    <div class="mb-6 overflow-hidden rounded-lg border border-t-4 border-gray-200 border-t-brand-700 bg-white shadow-sm">
        <div class="p-4 sm:p-5">
            <h1 class="text-xl font-semibold text-brand-700">{{ $resource['name'] }}</h1>

            @if (filled($resource['description']))
                <p class="mt-2 max-w-prose text-sm leading-relaxed text-gray-600">{{ $resource['description'] }}</p>
            @endif

            {{-- Stacked full-width on mobile, side by side from sm up. --}}
            <div class="mt-4 flex flex-col gap-2 sm:flex-row">
                <x-button :href="route('expenses.create', ['resourceType' => $currentResourceType, 'resource_id' => $resource['id']])">Add Expense for {{ $resource['name'] }}</x-button>
                <x-button variant="secondary" :href="route('recurring.index', $currentResourceType)">Recurring</x-button>
            </div>
        </div>
    </div>

    @if (count($items) === 0)
        <p class="text-sm text-gray-600">No expenses recorded{{ $page > 1 ? ' on this page' : ' yet' }}.</p>
    @else
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            {{-- sm and up: table. Below sm: a stacked list, as the table's four columns don't fit a phone. --}}
            <table class="hidden min-w-full divide-y divide-gray-200 text-sm sm:table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium text-gray-500">Date</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-500">Name</th>
                        <th class="px-4 py-2 text-right font-medium text-gray-500">Amount</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($items as $item)
                        <tr>
                            <td class="px-4 py-2 text-gray-500">{{ \Illuminate\Support\Carbon::parse($item['effective_date'])->format('j M Y') }}</td>
                            <td class="px-4 py-2">
                                <p class="text-gray-900">{{ $item['name'] }}</p>
                                @if ($item['description'])
                                    <p class="text-xs text-gray-500">{{ $item['description'] }}</p>
                                @endif
                                @if (count($item['categories'] ?? []) > 0)
                                    <p class="text-xs text-gray-400">
                                        {{ $item['categories'][0]['name'] }}
                                        @if (count($item['categories'][0]['subcategories'] ?? []) > 0)
                                            &rsaquo; {{ $item['categories'][0]['subcategories'][0]['name'] }}
                                        @endif
                                    </p>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-right">
                                <p class="font-medium text-gray-900">{{ $item['currency']['code'] }} {{ $item['actualised_total'] }}</p>
                                @if ((int) $item['percentage'] !== 100)
                                    <p class="text-xs text-gray-500">{{ $item['percentage'] }}% of {{ $item['total'] }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-right whitespace-nowrap">
                                <x-expense.actions :resource="$resource" :item="$item" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <ul class="divide-y divide-gray-100 text-sm sm:hidden">
                @foreach ($items as $item)
                    <li class="px-4 py-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-medium text-gray-900">{{ $item['name'] }}</p>
                                <p class="text-xs text-gray-500">{{ \Illuminate\Support\Carbon::parse($item['effective_date'])->format('j M Y') }}</p>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="font-medium text-gray-900">{{ $item['currency']['code'] }} {{ $item['actualised_total'] }}</p>
                                @if ((int) $item['percentage'] !== 100)
                                    <p class="text-xs text-gray-500">{{ $item['percentage'] }}% of {{ $item['total'] }}</p>
                                @endif
                            </div>
                        </div>

                        @if ($item['description'])
                            <p class="mt-1 text-xs text-gray-500">{{ $item['description'] }}</p>
                        @endif

                        <div class="mt-1 flex items-center justify-between gap-3">
                            @if (count($item['categories'] ?? []) > 0)
                                <p class="text-xs text-gray-400">
                                    {{ $item['categories'][0]['name'] }}
                                    @if (count($item['categories'][0]['subcategories'] ?? []) > 0)
                                        &rsaquo; {{ $item['categories'][0]['subcategories'][0]['name'] }}
                                    @endif
                                </p>
                            @else
                                <span></span>
                            @endif

                            <div class="-mr-2 flex shrink-0 items-center text-sm">
                                <x-expense.actions :resource="$resource" :item="$item" />
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="mt-4 flex justify-between text-sm">
            @if ($page > 1)
                <a href="{{ route('resources.show', ['resourceType' => $currentResourceType, 'resource_id' => $resource['id'], 'page' => $page - 1]) }}" class="text-indigo-600 hover:underline">&larr; Newer</a>
            @else
                <span></span>
            @endif

            @if ($hasMore)
                <a href="{{ route('resources.show', ['resourceType' => $currentResourceType, 'resource_id' => $resource['id'], 'page' => $page + 1]) }}" class="text-indigo-600 hover:underline">Older &rarr;</a>
            @endif
        </div>
    @endif
</x-layouts.app>
