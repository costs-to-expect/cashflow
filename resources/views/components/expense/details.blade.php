@props(['categoriesEnabled', 'categories' => [], 'subcategoriesByCategory' => [], 'nameSuggestions' => null, 'name' => null, 'description' => null, 'categoryId' => null, 'subcategoryId' => null])

{{--
    The "Details" card shared by the expense and recurring-expense forms: name (with suggestions
    from earlier expenses, when given), description and - when the resource type has categories
    turned on - the category -> subcategory pair, which public/js/*/expense-form.js links up.
    Both are required and have no "None" option, so the first category/subcategory is preselected.
    The field components apply old() input themselves.
--}}
@php
    $categoryOptions = collect($categories)->mapWithKeys(fn ($category) => [$category['id'] => $category['name']]);

    // Rendered server-side for whichever category starts selected (the JS rebuilds the list whenever
    // the category changes), so the select is right even before the script has run.
    $selectedCategoryId = old('category_id', $categoryId) ?? $categoryOptions->keys()->first();
    $subcategoryOptions = collect($subcategoriesByCategory[$selectedCategoryId] ?? [])->mapWithKeys(fn ($subcategory) => [$subcategory['id'] => $subcategory['name']]);
@endphp

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

    @if ($categoriesEnabled)
        @if ($categoryOptions->isEmpty())
            <p class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                Categories are turned on, so this needs a category and subcategory.
                <a href="{{ route('settings.categories', $currentResourceType) }}" class="font-medium underline">Add one in Settings</a> first.
            </p>
        @else
            <div class="grid gap-4 sm:grid-cols-2">
                <x-helper.form.field.select name="category_id" title="Category" required :value="$categoryId"
                    :options="$categoryOptions"
                    data-subcategories="{{ json_encode($subcategoriesByCategory) }}" />
                <x-helper.form.field.select name="subcategory_id" title="Subcategory" required :value="$subcategoryId"
                    :options="$subcategoryOptions->isNotEmpty() ? $subcategoryOptions : ['' => 'No subcategories yet']"
                    data-initial="{{ old('subcategory_id', $subcategoryId) }}" />
            </div>
        @endif
    @endif
</section>
