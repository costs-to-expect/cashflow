<x-layouts.app title="Edit recurring expense">
    <div class="mx-auto max-w-2xl">
        <form method="POST" action="{{ route('recurring.update', [$currentResourceType, $recurringExpense]) }}" class="space-y-6">
            @csrf

            <x-hero :back="route('recurring.index', $currentResourceType)" back-label="Recurring expenses" eyebrow="Edit monthly recurring expense"
                description="This expense is created automatically every month, between the start and (optional) end date below.">
                <div class="mt-5"><x-expense.amount-field :currencies="$currencies" :currency-id="$recurringExpense->currency_id" :total="$recurringExpense->total" /></div>
            </x-hero>

            <section class="space-y-5 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 sm:p-6">
                <h2 class="text-base font-semibold text-gray-900">Schedule</h2>

                <div class="grid gap-4 sm:grid-cols-3">
                    <x-helper.form.field.number name="day_of_month" title="Day of month" required min="1" max="31" :value="$recurringExpense->day_of_month" />
                    <x-helper.form.field.date name="starts_on" title="Starts on" required :value="$recurringExpense->starts_on->toDateString()" />
                    <x-helper.form.field.date name="ends_on" title="Ends on" :value="$recurringExpense->ends_on?->toDateString()" hint="Optional. Leave blank to keep going." />
                </div>
            </section>

            <x-expense.details :categories-enabled="$categoriesEnabled" :categories="$categories" :subcategories-by-category="$subcategoriesByCategory"
                :name="$recurringExpense->name" :description="$recurringExpense->description"
                :category-id="$recurringExpense->category_id" :subcategory-id="$recurringExpense->subcategory_id" />

            <x-expense.split :resources="$resources" :term="$resourceTermSingular"
                :allocations="old('allocations', $recurringExpense->allocations->map(fn ($a) => ['resource_id' => $a->resource_id, 'percentage' => $a->percentage])->all())" />

            <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <x-button variant="secondary" :href="route('recurring.index', $currentResourceType)">Cancel</x-button>
                <x-button>Save changes</x-button>
            </div>
        </form>
    </div>

    <script src="{{ asset('js/'.$version['js'].'/format-number.js') }}" defer></script>
    <script src="{{ asset('js/'.$version['js'].'/expense-form.js') }}" defer></script>
    <script src="{{ asset('js/'.$version['js'].'/expense-split.js') }}" defer></script>
</x-layouts.app>
