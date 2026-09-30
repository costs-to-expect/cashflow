<x-layouts.app title="Edit expense">
    @php
        $resourceName = collect($resources)->firstWhere('id', $resourceId)['name'] ?? null;
        $resourceUrl = route('resources.show', ['resourceType' => $currentResourceType, 'resource_id' => $resourceId]);
    @endphp

    <div class="mx-auto max-w-2xl">
        <form method="POST" action="{{ route('expenses.update', ['resourceType' => $currentResourceType, 'resource_id' => $resourceId, 'item_id' => $item['id']]) }}" class="space-y-6">
            @csrf

            <x-hero :back="$resourceUrl" :back-label="$resourceName ?? 'Back'">
                <h1 class="mt-4 text-sm font-medium text-white/70">Edit expense &middot; how much was it?</h1>

                <div class="mt-3"><x-expense.amount-field :currencies="$currencies" :currency-id="$item['currency']['id']" :total="$item['total']" /></div>
                <div class="mt-5"><x-expense.date-pill :value="$item['effective_date']" /></div>
            </x-hero>

            <x-expense.details :categories="$categories" :subcategories-by-category="$subcategoriesByCategory" :name-suggestions="$nameSuggestions"
                :name="$item['name']" :description="$item['description']" :category-id="$currentCategoryId" :subcategory-id="$currentSubcategoryId" />

            <section class="space-y-5 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 sm:p-6">
                <h2 class="text-base font-semibold text-gray-900">{{ $resourceName ? $resourceName."'s share" : 'Share' }}</h2>

                <x-helper.form.field.number name="percentage" title="Percentage" required min="1" max="100" :value="$item['percentage']"
                    :hint="'The share of the total recorded against this '.strtolower($resourceTermSingular).'.'" />
            </section>

            <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <x-button variant="secondary" :href="$resourceUrl">Cancel</x-button>
                <x-button>Save changes</x-button>
            </div>
        </form>
    </div>

    <script src="{{ asset('js/'.$version['js'].'/format-number.js') }}" defer></script>
    <script src="{{ asset('js/'.$version['js'].'/expense-form.js') }}" defer></script>
</x-layouts.app>
