<x-layouts.app title="Add expense">
    @if (count($resources) === 0)
        <p class="text-sm text-gray-600">You need to <a href="{{ route('resources.create', $currentResourceType) }}" class="text-brand-700 hover:underline">add a {{ strtolower($resourceTermSingular) }}</a> before you can record an expense.</p>
    @else
        <div class="mx-auto max-w-2xl">
            <form method="POST" action="{{ route('expenses.store', $currentResourceType) }}" class="space-y-6">
                @csrf

                <x-hero :back="route('dashboard', $currentResourceType)" back-label="Dashboard">
                    <h1 class="mt-4 text-sm font-medium text-white/70">Add expense &middot; how much was it?</h1>

                    <div class="mt-3"><x-expense.amount-field :currencies="$currencies" :currency-id="$defaultCurrencyId" /></div>
                    <div class="mt-5"><x-expense.date-pill :value="now()->toDateString()" /></div>
                </x-hero>

                <x-expense.details :categories-enabled="$categoriesEnabled" :categories="$categories" :subcategories-by-category="$subcategoriesByCategory" :name-suggestions="$nameSuggestions" />

                <x-expense.split :resources="$resources" :term="$resourceTermSingular" :default-split="$defaultSplit"
                    :allocations="old('allocations', $defaultAllocations ?? [['resource_id' => $preselectedResourceId ?? $resources[0]['id'], 'percentage' => 100]])" />

                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <x-button variant="secondary" :href="route('dashboard', $currentResourceType)">Cancel</x-button>
                    <x-button>Add expense</x-button>
                </div>
            </form>
        </div>

        <script src="{{ asset('js/'.$version['js'].'/format-number.js') }}" defer></script>
        <script src="{{ asset('js/'.$version['js'].'/expense-form.js') }}" defer></script>
        <script src="{{ asset('js/'.$version['js'].'/expense-split.js') }}" defer></script>
    @endif
</x-layouts.app>
