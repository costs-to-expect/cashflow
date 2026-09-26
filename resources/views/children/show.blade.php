<x-layouts.app :title="$child['name']">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-lg font-semibold text-gray-900">{{ $child['name'] }}</h1>
            <p class="text-sm text-gray-500">{{ $child['description'] }}</p>
        </div>
        <a href="{{ route('expenses.create') }}"><x-button>Add expense</x-button></a>
    </div>

    @if (count($items) === 0)
        <p class="text-sm text-gray-600">No expenses recorded{{ $page > 1 ? ' on this page' : ' yet' }}.</p>
    @else
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
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
                            </td>
                            <td class="px-4 py-2 text-right">
                                <p class="font-medium text-gray-900">{{ $item['currency']['code'] }} {{ $item['actualised_total'] }}</p>
                                @if ((int) $item['percentage'] !== 100)
                                    <p class="text-xs text-gray-500">{{ $item['percentage'] }}% of {{ $item['total'] }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-right whitespace-nowrap">
                                <a href="{{ route('expenses.edit', ['resource_id' => $child['id'], 'item_id' => $item['id']]) }}" class="text-indigo-600 hover:underline">Edit</a>
                                <form method="POST" action="{{ route('expenses.delete', ['resource_id' => $child['id'], 'item_id' => $item['id']]) }}" class="inline" onsubmit="return confirm('Delete this expense?');">
                                    @csrf
                                    <button type="submit" class="ml-3 text-red-600 hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4 flex justify-between text-sm">
            @if ($page > 1)
                <a href="{{ route('children.show', ['resource_id' => $child['id'], 'page' => $page - 1]) }}" class="text-indigo-600 hover:underline">&larr; Newer</a>
            @else
                <span></span>
            @endif

            @if ($hasMore)
                <a href="{{ route('children.show', ['resource_id' => $child['id'], 'page' => $page + 1]) }}" class="text-indigo-600 hover:underline">Older &rarr;</a>
            @endif
        </div>
    @endif
</x-layouts.app>
