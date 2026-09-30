<x-layouts.app title="New resource type">
    @php
        // Per item type: a short explanation and an icon path (Heroicons, outline). Only the two
        // types the controller offers are described - anything else falls back to just its label.
        $typeMeta = [
            'allocated-expense' => [
                'description' => 'Record costs and share them across your resources by percentage.',
                'icon' => 'M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z',
            ],
            'allocated-transaction' => [
                'description' => 'Record general transactions against each of your resources.',
                'icon' => 'M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5',
            ],
        ];

        $selectedType = old('item_type', 'allocated-expense');
    @endphp

    <div class="mx-auto max-w-2xl">
        <section class="relative overflow-hidden rounded-3xl bg-linear-to-br from-brand-900 via-brand-700 to-fuchsia-700 p-6 text-white shadow-lg sm:p-8">
            <div class="pointer-events-none absolute -right-16 -top-16 size-64 rounded-full bg-white/10 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-24 left-1/3 size-72 rounded-full bg-fuchsia-400/20 blur-3xl"></div>

            <div class="relative">
                <a href="{{ route('resource-types.index') }}" class="text-sm font-medium text-white/70 hover:text-white">&larr; Resource types</a>
                <p class="mt-4 text-sm font-medium text-white/70">New resource type</p>
                <h1 class="mt-1 text-3xl font-bold tracking-tight sm:text-4xl">What are you tracking?</h1>
                <p class="mt-3 max-w-prose text-[15px] leading-relaxed text-white/80">A resource type groups the things you track costs for, like <strong class="font-semibold text-white">Kids</strong> or <strong class="font-semibold text-white">Household</strong>. Each one gets its own resources, categories, reporting periods and default split.</p>
            </div>
        </section>

        <form method="POST" action="{{ route('resource-types.store') }}" class="mt-6 space-y-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 sm:p-6">
            @csrf

            <x-helper.form.field.text name="name" title="Name" required :value="old('name')" placeholder="e.g. Kids, Household, Side business" />
            <x-helper.form.field.textarea name="description" title="Description" required :value="old('description')" placeholder="What is this for?" hint="A sentence or two to help you remember what this resource type is for." />

            <fieldset>
                <legend class="text-sm font-medium text-gray-700">Type <span class="text-red-500">*</span></legend>

                <div class="mt-1.5 grid gap-3 sm:grid-cols-2">
                    @foreach ($itemTypeOptions as $value => $label)
                        <label class="group relative flex cursor-pointer items-start gap-3 rounded-2xl bg-white p-4 ring-1 ring-gray-300 transition hover:ring-brand-700 has-checked:bg-brand-tint has-checked:ring-2 has-checked:ring-brand-700 has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-brand-700">
                            <input type="radio" name="item_type" value="{{ $value }}" class="sr-only" @checked($selectedType === $value)>

                            <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-gray-100 text-gray-500 transition-colors group-has-checked:bg-brand-700 group-has-checked:text-white">
                                @if (isset($typeMeta[$value]))
                                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $typeMeta[$value]['icon'] }}" /></svg>
                                @endif
                            </span>

                            <span class="min-w-0 flex-1">
                                <span class="block font-semibold text-gray-900">{{ $label }}</span>
                                @if (isset($typeMeta[$value]))
                                    <span class="mt-0.5 block text-sm leading-snug text-gray-600">{{ $typeMeta[$value]['description'] }}</span>
                                @endif
                            </span>

                            <span class="mt-0.5 grid size-5 shrink-0 place-items-center rounded-full text-white ring-1 ring-gray-300 transition-colors group-has-checked:bg-brand-700 group-has-checked:ring-brand-700" aria-hidden="true">
                                <svg class="size-3 opacity-0 group-has-checked:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            </span>
                        </label>
                    @endforeach
                </div>

                @error('item_type')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-xs text-gray-500">The app has no way to change this later, so pick the one that fits.</p>
            </fieldset>

            <div class="flex flex-col-reverse gap-2 border-t border-gray-100 pt-5 sm:flex-row sm:justify-end">
                <x-button variant="secondary" :href="route('resource-types.index')">Cancel</x-button>
                <x-button>Create resource type</x-button>
            </div>
        </form>
    </div>
</x-layouts.app>
