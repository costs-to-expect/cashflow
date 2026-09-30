<x-layouts.app title="Add expense">
    @if (count($resources) === 0)
        <p class="text-sm text-gray-600">You need to <a href="{{ route('resources.create', $currentResourceType) }}" class="text-brand-700 hover:underline">add a {{ strtolower($resourceTermSingular) }}</a> before you can record an expense.</p>
    @else
        @php
            $colours = \App\Support\ResourceColours::forResources($resources);
            $term = strtolower($resourceTermSingular);

            $oldAllocations = old('allocations', $defaultAllocations ?? [['resource_id' => $preselectedResourceId ?? $resources[0]['id'], 'percentage' => 100]]);
            $isSplit = count($oldAllocations) > 1;

            // Splitting needs at least two resources to split between.
            $canSplit = count($resources) > 1;

            // The configured default split, restricted to resources that still exist - only offered if it covers two or more.
            $applicableDefault = collect($defaultSplit)->filter(fn ($allocation) => collect($resources)->contains('id', $allocation['resource_id']))->values();
            $defaultLabel = $applicableDefault->count() >= 2 ? $applicableDefault->pluck('percentage')->implode(' / ') : null;
        @endphp

        <div class="mx-auto max-w-2xl">
            <form method="POST" action="{{ route('expenses.store', $currentResourceType) }}" class="space-y-6">
                @csrf

                <section class="relative overflow-hidden rounded-3xl bg-linear-to-br from-brand-900 via-brand-700 to-fuchsia-700 p-6 text-white shadow-lg sm:p-8">
                    <div class="pointer-events-none absolute -right-16 -top-16 size-64 rounded-full bg-white/10 blur-3xl"></div>
                    <div class="pointer-events-none absolute -bottom-24 left-1/3 size-72 rounded-full bg-fuchsia-400/20 blur-3xl"></div>

                    <div class="relative">
                        <a href="{{ route('dashboard', $currentResourceType) }}" class="text-sm font-medium text-white/70 hover:text-white">&larr; Dashboard</a>
                        <h1 class="mt-4 text-sm font-medium text-white/70">Add expense &middot; how much was it?</h1>

                        <div class="mt-3 flex items-end gap-3">
                            <select id="currency_id" name="currency_id" required aria-label="Currency"
                                    class="h-12 shrink-0 cursor-pointer rounded-xl border-0 bg-white/15 pl-3 text-base font-semibold text-white ring-1 ring-white/30 focus:outline-none focus:ring-2 focus:ring-white [&>option]:text-gray-900">
                                @foreach ($currencies as $currency)
                                    <option value="{{ $currency['id'] }}" @selected((string) old('currency_id', $defaultCurrencyId) === (string) $currency['id'])>{{ $currency['code'] }}</option>
                                @endforeach
                            </select>

                            <input id="total" name="total" type="number" inputmode="decimal" step="0.01" min="0" required
                                   value="{{ old('total') }}" placeholder="0.00" aria-label="Total amount" data-format="number" data-points="2"
                                   class="w-full min-w-0 appearance-none border-0 border-b-2 {{ $errors->has('total') ? 'border-red-300' : 'border-white/30' }} bg-transparent pb-1 text-5xl font-bold tracking-tight tabular-nums text-white placeholder:text-white/30 focus:border-white focus:outline-none sm:text-6xl [&::-webkit-inner-spin-button]:appearance-none">
                        </div>

                        <label class="mt-5 inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm ring-1 ring-white/30 focus-within:ring-2 focus-within:ring-white">
                            <span class="text-white/70">Date</span>
                            <input id="effective_date" name="effective_date" type="date" required value="{{ old('effective_date', now()->toDateString()) }}"
                                   class="bg-transparent text-sm font-medium text-white [color-scheme:dark] focus:outline-none">
                        </label>

                        @foreach (['total', 'currency_id', 'effective_date'] as $heroField)
                            @error($heroField)
                                <p class="mt-2 text-sm text-red-200">{{ $message }}</p>
                            @enderror
                        @endforeach
                    </div>
                </section>

                <section class="space-y-5 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 sm:p-6">
                    <h2 class="text-base font-semibold text-gray-900">Details</h2>

                    <x-helper.form.field.text name="name" title="Name" required :value="old('name')" list="expense-names" autocomplete="off" placeholder="What was it?" hint="Suggestions from earlier expenses appear as you type." />
                    <datalist id="expense-names">
                        @foreach ($nameSuggestions as $suggestion)
                            <option value="{{ $suggestion }}"></option>
                        @endforeach
                    </datalist>

                    <x-helper.form.field.textarea name="description" title="Description" :value="old('description')" :rows="2" placeholder="Anything worth remembering (optional)" />

                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-helper.form.field.select name="category_id" title="Category" :value="old('category_id')"
                            :options="collect(['' => 'None'])->merge(collect($categories)->mapWithKeys(fn ($category) => [$category['id'] => $category['name']]))"
                            data-subcategories="{{ json_encode($subcategoriesByCategory) }}" />
                        <x-helper.form.field.select name="subcategory_id" title="Subcategory" :value="old('subcategory_id')"
                            :options="['' => 'None']" />
                    </div>
                </section>

                <section id="split-section" class="space-y-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 sm:p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-base font-semibold text-gray-900">Who is it for?</h2>
                            @if ($canSplit)
                                <p id="split-help" class="mt-0.5 text-sm text-gray-500"
                                   data-text-single="All of it goes to one {{ $term }}. Turn on split to share it by percentage."
                                   data-text-split="Shared by percentage. Each {{ $term }}'s amount updates as you go.">
                                    {{ $isSplit ? "Shared by percentage. Each {$term}'s amount updates as you go." : "All of it goes to one {$term}. Turn on split to share it by percentage." }}
                                </p>
                            @endif
                        </div>

                        @if ($canSplit)
                            <span class="flex shrink-0 items-center gap-3">
                                <label for="split-toggle" class="cursor-pointer text-sm font-medium text-gray-700">Split</label>
                                <label class="relative inline-flex cursor-pointer items-center">
                                    <input type="checkbox" id="split-toggle" class="peer sr-only" aria-label="Split this expense across more than one {{ $term }}" @checked($isSplit)>
                                    <span class="h-7 w-12 rounded-full bg-gray-300 transition-colors after:absolute after:left-0.5 after:top-0.5 after:size-6 after:rounded-full after:bg-white after:shadow after:transition-transform peer-checked:bg-brand-700 peer-checked:after:translate-x-5 peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-brand-700"></span>
                                </label>
                            </span>
                        @endif
                    </div>

                    <div id="split-rows" class="space-y-2"
                         data-resources='@json(collect($resources)->map(fn ($resource) => ['id' => (string) $resource['id'], 'name' => $resource['name'], 'dot' => $colours[$resource['id']]['bar']])->values())'
                         data-default-split='@json($applicableDefault)'>
                        @foreach ($oldAllocations as $index => $allocation)
                            <x-expense.allocation-row :index="$index" :resources="$resources" :colours="$colours" :resource-id="$allocation['resource_id']"
                                :percentage="$allocation['percentage']" :split="$isSplit" :term="$resourceTermSingular" />
                        @endforeach
                    </div>

                    @if ($canSplit)
                        <div data-split-only class="{{ $isSplit ? '' : 'hidden' }} space-y-3">
                            <div id="split-bar" class="flex h-2.5 overflow-hidden rounded-full bg-gray-200"></div>
                            <p id="split-status" role="status" class="text-sm font-medium"></p>

                            <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm font-medium text-brand-700">
                                <button type="button" id="split-even" class="cursor-pointer hover:underline">Split evenly</button>
                                @if ($defaultLabel)
                                    <button type="button" id="split-default" class="cursor-pointer hover:underline">Use default split ({{ $defaultLabel }})</button>
                                @endif
                                <button type="button" id="add-allocation" class="cursor-pointer hover:underline">+ Add another {{ $term }}</button>
                            </div>
                        </div>

                        <template id="allocation-row-template">
                            <x-expense.allocation-row index="__INDEX__" :resources="$resources" :colours="$colours" :resource-id="$resources[0]['id']"
                                :split="true" :term="$resourceTermSingular" />
                        </template>
                    @endif
                </section>

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
