<x-layouts.app title="Add monthly recurring expense">
    <h1 class="mb-2 text-lg font-semibold text-gray-900">Add monthly recurring expense</h1>
    <p class="mb-6 text-sm text-gray-600">This expense is created automatically every month, between the start and (optional) end date below.</p>

    @if (count($children) === 0)
        <p class="text-sm text-gray-600">You need to <a href="{{ route('children.create') }}" class="text-indigo-600 hover:underline">add a {{ strtolower($resourceTermSingular) }}</a> first.</p>
    @else
        <form method="POST" action="{{ route('recurring.store') }}" class="max-w-xl space-y-4 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            @csrf

            <x-helper.form.field.text name="name" title="Name" required :value="old('name')" />
            <x-helper.form.field.textarea name="description" title="Description" :value="old('description')" />

            <div class="grid grid-cols-2 gap-4">
                <x-helper.form.field.number name="day_of_month" title="Day of month" required min="1" max="31" :value="old('day_of_month', 1)" />
                <x-helper.form.field.select name="currency_id" title="Currency" required :value="old('currency_id', $defaultCurrencyId)"
                    :options="collect($currencies)->mapWithKeys(fn ($currency) => [$currency['id'] => $currency['code']])" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <x-helper.form.field.date name="starts_on" title="Starts on" required :value="old('starts_on', now()->toDateString())" />
                <x-helper.form.field.date name="ends_on" title="Ends on" :value="old('ends_on')" />
            </div>

            <x-helper.form.field.number name="total" title="Total amount" required min="0" step="0.01" :value="old('total')" data-format="number" data-points="2" />

            <div class="grid grid-cols-2 gap-4">
                <x-helper.form.field.select name="category_id" title="Category" :value="old('category_id')"
                    :options="collect(['' => 'None'])->merge(collect($categories)->mapWithKeys(fn ($category) => [$category['id'] => $category['name']]))"
                    data-subcategories="{{ json_encode($subcategoriesByCategory) }}" />
                <x-helper.form.field.select name="subcategory_id" title="Subcategory" :value="old('subcategory_id')"
                    :options="['' => 'None']" />
            </div>

            <div>
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" id="split-toggle" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    Split this expense across more than one {{ strtolower($resourceTermSingular) }}
                </label>
            </div>

            <div id="allocations" class="space-y-3"
                 data-children='@json(collect($children)->map(fn ($child) => ['id' => $child['id'], 'name' => $child['name']]))'
                 data-default-split='@json($defaultSplit)'
                 data-term-singular="{{ $resourceTermSingular }}">
                <div class="allocation-row grid grid-cols-[1fr_120px_auto] items-end gap-3">
                    <x-helper.form.field.select name="allocations[0][resource_id]" :title="$resourceTermSingular" required
                        :value="old('allocations.0.resource_id', $children[0]['id'])"
                        :options="collect($children)->mapWithKeys(fn ($child) => [$child['id'] => $child['name']])"
                        errorKey="allocations.0.resource_id" />
                    <x-helper.form.field.number name="allocations[0][percentage]" title="Percentage" required min="1" max="100"
                        :value="old('allocations.0.percentage', 100)" errorKey="allocations.0.percentage" />
                    <button type="button" class="remove-allocation hidden pb-2 text-sm text-red-600 hover:underline">Remove</button>
                </div>
            </div>

            <button type="button" id="add-allocation" class="hidden text-sm text-indigo-600 hover:underline">+ Add another {{ strtolower($resourceTermSingular) }}</button>

            <div>
                <x-button>Add recurring expense</x-button>
            </div>
        </form>

        <script src="{{ asset('js/format-number.js') }}" defer></script>
        <script src="{{ asset('js/expense-form.js') }}" defer></script>
    @endif
</x-layouts.app>
