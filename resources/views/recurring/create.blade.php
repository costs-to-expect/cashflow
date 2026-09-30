<x-layouts.app title="Add monthly recurring expense">
    @if (count($resources) === 0)
        <p class="text-sm text-gray-600">You need to <a href="{{ route('resources.create', $currentResourceType) }}" class="text-brand-700 hover:underline">add a {{ strtolower($resourceTermSingular) }}</a> first.</p>
    @else
        <div class="mx-auto max-w-2xl">
            <form method="POST" action="{{ route('recurring.store', $currentResourceType) }}" class="space-y-6">
                @csrf

                <x-hero :back="route('recurring.index', $currentResourceType)" back-label="Recurring expenses" eyebrow="Add monthly recurring expense"
                    description="This expense is created automatically every month, between the start and (optional) end date below.">
                    <div class="mt-5"><x-expense.amount-field :currencies="$currencies" :currency-id="$defaultCurrencyId" /></div>
                </x-hero>

                <section class="space-y-5 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 sm:p-6">
                    <h2 class="text-base font-semibold text-gray-900">Schedule</h2>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <x-helper.form.field.number name="day_of_month" title="Day of month" required min="1" max="31" :value="1" />
                        <x-helper.form.field.date name="starts_on" title="Starts on" required :value="now()->toDateString()" />
                        <x-helper.form.field.date name="ends_on" title="Ends on" hint="Optional. Leave blank to keep going." />
                    </div>
                </section>

                <x-expense.details :categories="$categories" :subcategories-by-category="$subcategoriesByCategory" />

                <x-expense.split :resources="$resources" :term="$resourceTermSingular" :default-split="$defaultSplit"
                    :allocations="old('allocations', [['resource_id' => $resources[0]['id'], 'percentage' => 100]])" />

                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <x-button variant="secondary" :href="route('recurring.index', $currentResourceType)">Cancel</x-button>
                    <x-button>Add recurring expense</x-button>
                </div>
            </form>
        </div>

        <script src="{{ asset('js/'.$version['js'].'/format-number.js') }}" defer></script>
        <script src="{{ asset('js/'.$version['js'].'/expense-form.js') }}" defer></script>
        <script src="{{ asset('js/'.$version['js'].'/expense-split.js') }}" defer></script>
    @endif
</x-layouts.app>
