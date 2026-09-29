<x-layouts.app title="Categories">
    <h1 class="mb-2 text-lg font-semibold text-gray-900">Categories</h1>
    <p class="mb-6 text-sm text-gray-600">Categories and subcategories expenses can be tagged with.</p>

    <div class="mb-8 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-sm font-semibold text-gray-900">Add category</h2>
        <form method="POST" action="{{ route('settings.categories.store', $currentResourceType) }}" class="grid items-end gap-4 sm:grid-cols-[1fr_2fr_auto]">
            @csrf
            <x-helper.form.field.text name="name" title="Name" required />
            <x-helper.form.field.text name="description" title="Description" required />
            <x-button>Add category</x-button>
        </form>
    </div>

    @if (count($categories) === 0)
        <p class="text-sm text-gray-600">No categories yet - add one above.</p>
    @else
        <div class="space-y-6">
            @foreach ($categories as $category)
                @php($categoryId = $category['id'])
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                    <form method="POST" action="{{ route('settings.categories.update', [$currentResourceType, $categoryId]) }}" class="grid items-end gap-4 sm:grid-cols-[1fr_2fr_auto]">
                        @csrf
                        <x-helper.form.field.text :name="'categories['.$categoryId.'][name]'" title="Name" required
                            :value="$category['name']" :errorKey="'categories.'.$categoryId.'.name'" />
                        <x-helper.form.field.text :name="'categories['.$categoryId.'][description]'" title="Description" required
                            :value="$category['description']" :errorKey="'categories.'.$categoryId.'.description'" />
                        <x-button variant="secondary">Save</x-button>
                    </form>

                    <div class="mt-6 border-t border-gray-100 pt-4">
                        <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Subcategories</h3>

                        <div class="space-y-3">
                            @foreach ($category['subcategories'] as $subcategory)
                                @php($subcategoryId = $subcategory['id'])
                                <form method="POST" action="{{ route('settings.categories.subcategories.update', ['resourceType' => $currentResourceType, 'category_id' => $categoryId, 'subcategory_id' => $subcategoryId]) }}" class="grid items-end gap-4 sm:grid-cols-[1fr_2fr_auto]">
                                    @csrf
                                    <x-helper.form.field.text :name="'subcategories['.$subcategoryId.'][name]'" title="Name" required
                                        :value="$subcategory['name']" :errorKey="'subcategories.'.$subcategoryId.'.name'" />
                                    <x-helper.form.field.text :name="'subcategories['.$subcategoryId.'][description]'" title="Description" required
                                        :value="$subcategory['description']" :errorKey="'subcategories.'.$subcategoryId.'.description'" />
                                    <x-button variant="secondary">Save</x-button>
                                </form>
                            @endforeach
                        </div>

                        <form method="POST" action="{{ route('settings.categories.subcategories.store', [$currentResourceType, $categoryId]) }}" class="mt-4 grid items-end gap-4 sm:grid-cols-[1fr_2fr_auto]">
                            @csrf
                            <x-helper.form.field.text :name="'new_subcategories['.$categoryId.'][name]'" title="New subcategory name" required
                                :errorKey="'new_subcategories.'.$categoryId.'.name'" />
                            <x-helper.form.field.text :name="'new_subcategories['.$categoryId.'][description]'" title="Description" required
                                :errorKey="'new_subcategories.'.$categoryId.'.description'" />
                            <x-button variant="secondary">Add subcategory</x-button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.app>
