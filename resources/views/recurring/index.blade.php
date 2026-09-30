<x-layouts.app title="Recurring expenses">
    <x-hero :back="route('dashboard', $currentResourceType)" back-label="Dashboard" title="Recurring expenses"
        description="Costs that are added automatically every month, split the same way each time.">
        <div class="mt-6">
            <x-button variant="hero" :href="route('recurring.create', $currentResourceType)">Add recurring expense</x-button>
        </div>
    </x-hero>

    @if ($recurringExpenses->isEmpty())
        <p class="mt-6 text-sm text-gray-600">No recurring expenses set up yet.</p>
    @else
        <ul class="mt-6 divide-y divide-gray-100 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
            @foreach ($recurringExpenses as $recurringExpense)
                <li class="px-4 py-4 sm:px-5">
                    <div @class(['flex flex-wrap items-start justify-between gap-x-4 gap-y-1', 'opacity-60' => ! $recurringExpense->active])>
                        <div class="min-w-0">
                            <p class="font-medium text-gray-900">{{ $recurringExpense->name }}</p>
                            <p class="mt-0.5 text-xs text-gray-500">
                                {{ $recurringExpense->allocations->map(fn ($a) => ($resourcesById[$a->resource_id]['name'] ?? '?').' ('.$a->percentage.'%)')->implode(', ') }}
                            </p>
                            <p class="mt-1 text-xs text-gray-500">
                                @if ($recurringExpense->active)
                                    Next run <span class="font-medium text-gray-700">{{ $recurringExpense->next_run_date->format('D j M Y') }}</span>
                                @elseif ($recurringExpense->ends_on && $recurringExpense->ends_on->isPast())
                                    Ended {{ $recurringExpense->ends_on->format('j M Y') }}
                                @else
                                    Paused
                                @endif
                            </p>
                        </div>

                        <p class="shrink-0 font-semibold tabular-nums text-gray-900">{{ $currenciesById[$recurringExpense->currency_id]['code'] ?? '' }} {{ number_format((float) $recurringExpense->total, 2) }}</p>
                    </div>

                    <div class="-mb-2 -ml-2 mt-2 flex items-center text-sm">
                        <a href="{{ route('recurring.edit', [$currentResourceType, $recurringExpense]) }}" class="rounded-md px-2 py-1.5 font-medium text-brand-700 hover:bg-gray-100 hover:underline">Edit</a>

                        <form method="POST" action="{{ route('recurring.toggle', [$currentResourceType, $recurringExpense]) }}" class="inline">
                            @csrf
                            <button type="submit" class="cursor-pointer rounded-md px-2 py-1.5 font-medium text-gray-600 hover:bg-gray-100 hover:underline">{{ $recurringExpense->active ? 'Pause' : 'Resume' }}</button>
                        </form>

                        <form method="POST" action="{{ route('recurring.delete', [$currentResourceType, $recurringExpense]) }}" class="inline" onsubmit="return confirm('Delete this recurring expense?');">
                            @csrf
                            <button type="submit" class="cursor-pointer rounded-md px-2 py-1.5 font-medium text-red-600 hover:bg-red-50 hover:underline">Delete</button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</x-layouts.app>
