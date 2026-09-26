<x-layouts.app title="Edit expense">
    <h1 class="mb-6 text-lg font-semibold text-gray-900">Edit expense</h1>

    <form method="POST" action="{{ route('expenses.update', ['resource_id' => $resourceId, 'item_id' => $item['id']]) }}" class="max-w-xl space-y-4 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        @csrf

        <x-helper.form.field.text name="name" title="Name" required :value="old('name', $item['name'])" list="expense-names" autocomplete="off" />
        <datalist id="expense-names">
            @foreach ($nameSuggestions as $suggestion)
                <option value="{{ $suggestion }}"></option>
            @endforeach
        </datalist>

        <x-helper.form.field.textarea name="description" title="Description" :value="old('description', $item['description'])" />

        <div class="grid grid-cols-2 gap-4">
            <x-helper.form.field.date name="effective_date" title="Date" required :value="old('effective_date', $item['effective_date'])" />
            <x-helper.form.field.select name="currency_id" title="Currency" required :value="old('currency_id', $item['currency']['id'])"
                :options="collect($currencies)->mapWithKeys(fn ($currency) => [$currency['id'] => $currency['code']])" />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <x-helper.form.field.number name="total" title="Total amount" required min="0" step="0.01" :value="old('total', $item['total'])" data-format="number" data-points="2" />
            <x-helper.form.field.number name="percentage" :title="'Percentage for this '.strtolower($resourceTermSingular)" required min="1" max="100" :value="old('percentage', $item['percentage'])" />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <x-helper.form.field.select name="category_id" title="Category" :value="old('category_id', $currentCategoryId)"
                :options="collect(['' => 'None'])->merge(collect($categories)->mapWithKeys(fn ($category) => [$category['id'] => $category['name']]))"
                data-subcategories="{{ json_encode($subcategoriesByCategory) }}" />
            <x-helper.form.field.select name="subcategory_id" title="Subcategory" :value="old('subcategory_id', $currentSubcategoryId)"
                :options="['' => 'None']" data-initial="{{ old('subcategory_id', $currentSubcategoryId) }}" />
        </div>

        <x-button>Save changes</x-button>
    </form>

    <script src="{{ asset('js/'.$version['js'].'/format-number.js') }}" defer></script>
    <script src="{{ asset('js/'.$version['js'].'/expense-form.js') }}" defer></script>
</x-layouts.app>
