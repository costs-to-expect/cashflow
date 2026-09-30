@props(['categories', 'subcategoriesByCategory', 'nameSuggestions' => null, 'name' => null, 'description' => null, 'categoryId' => null, 'subcategoryId' => null])

{{--
    The "Details" card shared by the expense and recurring-expense forms: name (with suggestions
    from earlier expenses, when given), description and the category -> subcategory pair, which
    public/js/*/expense-form.js links up. The field components apply old() input themselves.
--}}
<section class="space-y-5 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 sm:p-6">
    <h2 class="text-base font-semibold text-gray-900">Details</h2>

    <x-helper.form.field.text name="name" title="Name" required :value="$name" list="expense-names" autocomplete="off" placeholder="What was it?"
        :hint="$nameSuggestions !== null ? 'Suggestions from earlier expenses appear as you type.' : null" />

    @if ($nameSuggestions !== null)
        <datalist id="expense-names">
            @foreach ($nameSuggestions as $suggestion)
                <option value="{{ $suggestion }}"></option>
            @endforeach
        </datalist>
    @endif

    <x-helper.form.field.textarea name="description" title="Description" :value="$description" :rows="2" placeholder="Anything worth remembering (optional)" />

    <div class="grid gap-4 sm:grid-cols-2">
        <x-helper.form.field.select name="category_id" title="Category" :value="$categoryId"
            :options="collect(['' => 'None'])->merge(collect($categories)->mapWithKeys(fn ($category) => [$category['id'] => $category['name']]))"
            data-subcategories="{{ json_encode($subcategoriesByCategory) }}" />
        <x-helper.form.field.select name="subcategory_id" title="Subcategory" :value="$subcategoryId"
            :options="['' => 'None']" data-initial="{{ old('subcategory_id', $subcategoryId) }}" />
    </div>
</section>
