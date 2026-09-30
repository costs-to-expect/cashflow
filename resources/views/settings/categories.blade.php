<x-layouts.app title="Categories">
    <div class="mx-auto max-w-3xl">
        <x-hero :compact="true" :back="route('settings.index', $currentResourceType)" back-label="Settings" eyebrow="Settings" title="Categories"
            description="Categories and subcategories expenses can be tagged with." />

        <section class="mt-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 sm:p-6">
            <h2 class="mb-4 text-base font-semibold text-gray-900">Add category</h2>

            <form method="POST" action="{{ route('settings.categories.store', $currentResourceType) }}" class="grid items-end gap-4 sm:grid-cols-[1fr_2fr_auto]">
                @csrf
                <x-helper.form.field.text name="name" title="Name" required />
                <x-helper.form.field.text name="description" title="Description" required />
                <x-button>Add category</x-button>
            </form>
        </section>

        @if (count($categories) === 0)
            <p class="mt-6 text-sm text-gray-600">No categories yet - add one above.</p>
        @else
            <div class="mt-6 space-y-6">
                @foreach ($categories as $category)
                    @php($categoryId = $category['id'])

                    <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 sm:p-6">
                        <header class="mb-4 flex items-center justify-between gap-3">
                            <h2 class="text-lg font-semibold text-gray-900">{{ $category['name'] }}</h2>
                            <span class="shrink-0 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">{{ count($category['subcategories']) }} {{ count($category['subcategories']) === 1 ? 'subcategory' : 'subcategories' }}</span>
                        </header>

                        <form method="POST" action="{{ route('settings.categories.update', [$currentResourceType, $categoryId]) }}" class="grid items-end gap-4 sm:grid-cols-[1fr_2fr_auto]">
                            @csrf
                            <x-helper.form.field.text :name="'categories['.$categoryId.'][name]'" title="Name" required
                                :value="$category['name']" :errorKey="'categories.'.$categoryId.'.name'" />
                            <x-helper.form.field.text :name="'categories['.$categoryId.'][description]'" title="Description" required
                                :value="$category['description']" :errorKey="'categories.'.$categoryId.'.description'" />
                            <x-button variant="secondary">Save</x-button>
                        </form>

                        <div class="mt-6 border-t border-gray-100 pt-5">
                            <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Subcategories</h3>

                            <div class="space-y-3">
                                @foreach ($category['subcategories'] as $subcategory)
                                    @php($subcategoryId = $subcategory['id'])

                                    <form method="POST" action="{{ route('settings.categories.subcategories.update', ['resourceType' => $currentResourceType, 'category_id' => $categoryId, 'subcategory_id' => $subcategoryId]) }}" class="grid items-end gap-4 rounded-xl bg-gray-50 p-3 sm:grid-cols-[1fr_2fr_auto]">
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
                    </section>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app>
