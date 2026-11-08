{{-- Also the copy form: $copyOf (the expense being copied) prefills it, see View\ExpenseController::copy(). --}}
<x-layouts.app :title="isset($copyOf) ? 'Copy expense' : 'Add expense'">
    @php
        $copy = $copyOf ?? null;
        $heading = $copy ? 'Copy expense' : 'Add expense';

        $backUrl = $copy
            ? route('resources.show', ['resourceType' => $currentResourceType, 'resource_id' => $copyOfResourceId])
            : route('dashboard', $currentResourceType);
        $backLabel = $copy ? (collect($resources)->firstWhere('id', $copyOfResourceId)['name'] ?? 'Back') : 'Dashboard';
    @endphp

    @if (count($resources) === 0)
        <p class="text-sm text-gray-600">You need to <a href="{{ route('resources.create', $currentResourceType) }}" class="text-brand-700 hover:underline">add a {{ strtolower($resourceTermSingular) }}</a> before you can record an expense.</p>
    @else
        <div class="mx-auto max-w-2xl">
            <form method="POST" action="{{ route('expenses.store', $currentResourceType) }}" class="space-y-6">
                @csrf

                <x-hero :back="$backUrl" :back-label="$backLabel">
                    <h1 class="mt-4 text-sm font-medium text-white/70">{{ $heading }} &middot; how much was it?</h1>

                    <div class="mt-3"><x-expense.amount-field :currencies="$currencies" :currency-id="$defaultCurrencyId" :total="$copy['total'] ?? null" /></div>
                    <div class="mt-5"><x-expense.date-pill :value="$copy['effective_date'] ?? now()->toDateString()" /></div>
                </x-hero>

                <x-expense.details :categories-enabled="$categoriesEnabled" :categories="$categories" :subcategories-by-category="$subcategoriesByCategory" :name-suggestions="$nameSuggestions"
                    :name="$copy['name'] ?? null" :description="$copy['description'] ?? null" :category-id="$currentCategoryId ?? null" :subcategory-id="$currentSubcategoryId ?? null" />

                <x-expense.split :resources="$resources" :term="$resourceTermSingular" :default-split="$defaultSplit" percentage-always
                    :allocations="old('allocations', $defaultAllocations ?? [['resource_id' => $preselectedResourceId ?? $resources[0]['id'], 'percentage' => 100]])" />

                <x-expense.publish-after :value="$copy['publish_after'] ?? null" />

                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <x-button variant="secondary" :href="$backUrl">Cancel</x-button>
                    <x-button>{{ $copy ? 'Add copy' : 'Add expense' }}</x-button>
                </div>
            </form>
        </div>

        <script src="{{ asset('js/'.$version['js'].'/format-number.js') }}" defer></script>
        <script src="{{ asset('js/'.$version['js'].'/expense-form.js') }}" defer></script>
        <script src="{{ asset('js/'.$version['js'].'/expense-split.js') }}" defer></script>
    @endif
</x-layouts.app>
