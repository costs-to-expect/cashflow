<x-layouts.app title="Dashboard">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-lg font-semibold text-gray-900">Dashboard</h1>
        <div class="flex gap-2">
            <a href="{{ route('expenses.create') }}"><x-button>Add expense</x-button></a>
            <a href="{{ route('children.create') }}"><x-button variant="secondary">Add child</x-button></a>
        </div>
    </div>

    @if ($apiError)
        <p class="text-sm text-red-600">We couldn't reach the Costs to Expect API, please try again shortly.</p>
    @elseif (count($children) === 0)
        <p class="text-sm text-gray-600">No children set up yet. <a href="{{ route('children.create') }}" class="text-indigo-600 hover:underline">Add one</a> to get started.</p>
    @else
        <div class="grid gap-6 sm:grid-cols-2">
            @foreach ($children as $child)
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="mb-3 flex items-center justify-between">
                        <a href="{{ route('children.show', $child['id']) }}" class="font-medium text-gray-900 hover:text-indigo-600">{{ $child['name'] }}</a>
                        <a href="{{ route('children.show', $child['id']) }}" class="text-xs text-indigo-600 hover:underline">View all</a>
                    </div>

                    @php($recent = $recentByChild[$child['id']] ?? [])

                    @if (count($recent) === 0)
                        <p class="text-sm text-gray-500">No expenses recorded yet.</p>
                    @else
                        <ul class="divide-y divide-gray-100">
                            @foreach ($recent as $expense)
                                <li class="flex items-center justify-between py-2 text-sm">
                                    <div>
                                        <p class="text-gray-900">{{ $expense['name'] }}</p>
                                        <p class="text-gray-500">{{ \Illuminate\Support\Carbon::parse($expense['effective_date'])->format('j M Y') }}</p>
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
