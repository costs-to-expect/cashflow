<x-layouts.app title="Recurring expenses">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-lg font-semibold text-gray-900">Recurring expenses</h1>
        <a href="{{ route('recurring.create') }}"><x-button>Add recurring expense</x-button></a>
    </div>

    @if ($recurringExpenses->isEmpty())
        <p class="text-sm text-gray-600">No recurring expenses set up yet.</p>
    @else
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium text-gray-500">Name</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-500">Split</th>
                        <th class="px-4 py-2 text-right font-medium text-gray-500">Total</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-500">Next run</th>
                        <th class="px-4 py-2"></th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($recurringExpenses as $recurringExpense)
                        <tr class="{{ $recurringExpense->active ? '' : 'opacity-50' }}">
                            <td class="px-4 py-2 text-gray-900">{{ $recurringExpense->name }}</td>
                            <td class="px-4 py-2 text-gray-600">
                                {{ $recurringExpense->allocations->map(fn ($a) => ($childrenById[$a->resource_id]['name'] ?? '?').' ('.$a->percentage.'%)')->implode(', ') }}
                            </td>
                            <td class="px-4 py-2 text-right text-gray-900">{{ $currenciesById[$recurringExpense->currency_id]['code'] ?? '' }} {{ $recurringExpense->total }}</td>
                            <td class="px-4 py-2 text-gray-600">{{ $recurringExpense->active ? $recurringExpense->next_run_date->format('j M Y') : 'Paused' }}</td>
                            <td class="px-4 py-2 text-right whitespace-nowrap">
                                <a href="{{ route('recurring.edit', $recurringExpense) }}" class="text-indigo-600 hover:underline">Edit</a>
                                <form method="POST" action="{{ route('recurring.toggle', $recurringExpense) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="ml-3 text-gray-600 hover:underline">{{ $recurringExpense->active ? 'Pause' : 'Resume' }}</button>
                                </form>
                            </td>
                            <td class="px-4 py-2 text-right whitespace-nowrap">
                                <form method="POST" action="{{ route('recurring.delete', $recurringExpense) }}" onsubmit="return confirm('Delete this recurring expense?');">
                                    @csrf
                                    <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-layouts.app>
