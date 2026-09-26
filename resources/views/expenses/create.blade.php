<x-layouts.app title="Add expense">
    <h1 class="mb-6 text-lg font-semibold text-gray-900">Add expense</h1>

    @if (count($children) === 0)
        <p class="text-sm text-gray-600">You need to <a href="{{ route('children.create') }}" class="text-indigo-600 hover:underline">add a child</a> before you can record an expense.</p>
    @else
        <form method="POST" action="{{ route('expenses.store') }}" class="max-w-xl space-y-4 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            @csrf

            <x-helper.form.field.text name="name" title="Name" required :value="old('name')" />
            <x-helper.form.field.textarea name="description" title="Description" :value="old('description')" />

            <div class="grid grid-cols-2 gap-4">
                <x-helper.form.field.date name="effective_date" title="Date" required :value="old('effective_date', now()->toDateString())" />
                <x-helper.form.field.select name="currency_id" title="Currency" required :value="old('currency_id', $defaultCurrencyId)"
                    :options="collect($currencies)->mapWithKeys(fn ($currency) => [$currency['id'] => $currency['code']])" />
            </div>

            <x-helper.form.field.number name="total" title="Total amount" required min="0" step="0.01" :value="old('total')" />

            @php
                $oldAllocations = old('allocations', [['resource_id' => $children[0]['id'], 'percentage' => 100]]);
                $isSplit = count($oldAllocations) > 1;
            @endphp

            <div>
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" id="split-toggle" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" @checked($isSplit)>
                    Split this expense across more than one child
                </label>
            </div>

            <div id="allocations" class="space-y-3" data-children='@json(collect($children)->map(fn ($child) => ['id' => $child['id'], 'name' => $child['name']]))'>
                @foreach ($oldAllocations as $index => $allocation)
                    <div class="allocation-row grid grid-cols-[1fr_120px_auto] items-end gap-3">
                        <div>
                            <x-helper.form.field.select :name="'allocations['.$index.'][resource_id]'" title="Child" required
                                :value="$allocation['resource_id']"
                                :options="collect($children)->mapWithKeys(fn ($child) => [$child['id'] => $child['name']])"
                                :errorKey="'allocations.'.$index.'.resource_id'" />
                        </div>
                        <div>
                            <x-helper.form.field.number :name="'allocations['.$index.'][percentage]'" title="Percentage" required min="1" max="100"
                                :value="$allocation['percentage']" :errorKey="'allocations.'.$index.'.percentage'" />
                        </div>
                        <button type="button" class="remove-allocation {{ $isSplit ? '' : 'hidden' }} pb-2 text-sm text-red-600 hover:underline">Remove</button>
                    </div>
                @endforeach
            </div>

            <button type="button" id="add-allocation" class="{{ $isSplit ? '' : 'hidden' }} text-sm text-indigo-600 hover:underline">+ Add another child</button>

            <div>
                <x-button>Add expense</x-button>
            </div>
        </form>

        <script src="{{ asset('js/expense-form.js') }}" defer></script>
    @endif
</x-layouts.app>
