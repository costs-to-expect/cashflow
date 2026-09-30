<x-layouts.app title="Settings">
    @php
        // Heroicons (outline) paths.
        $tiles = [
            [
                'route' => route('settings.default-split', $currentResourceType),
                'title' => 'Default split',
                'description' => 'The percentages pre-filled whenever you split an expense.',
                'icon' => 'M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6ZM13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z',
            ],
            [
                'route' => route('settings.use-categories', $currentResourceType),
                'title' => 'Use categories',
                'badge' => $categoriesEnabled ? 'On' : 'Off',
                'description' => 'Whether expenses are tagged with a category and subcategory.',
                'icon' => 'M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75',
            ],
            // Only worth managing categories while they're turned on.
            ...($categoriesEnabled ? [[
                'route' => route('settings.categories', $currentResourceType),
                'title' => 'Categories',
                'description' => 'Add and update the categories and subcategories expenses can be tagged with.',
                'icon' => 'M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3ZM6 6h.008v.008H6V6Z',
            ]] : []),
            [
                'route' => route('settings.resource-naming', $currentResourceType),
                'title' => 'Naming',
                'description' => 'What a "'.strtolower($resourceTermSingular).'" is called throughout the app.',
                'icon' => 'm16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10',
            ],
            [
                'route' => route('settings.periods', $currentResourceType),
                'title' => 'Reporting periods',
                'description' => 'Recurring date windows (e.g. a financial year) totalled on the dashboard.',
                'icon' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5',
            ],
        ];
    @endphp

    <x-hero :eyebrow="$currentResourceType->name" title="Settings"
        :description="'Tune how '.$currentResourceType->name.' works: how expenses are split, categorised and named, and which periods get totalled.'" />

    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        @foreach ($tiles as $tile)
            <a href="{{ $tile['route'] }}" class="group flex items-start gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 transition hover:ring-2 hover:ring-brand-700">
                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-brand-tint text-brand-700">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $tile['icon'] }}" /></svg>
                </span>

                <span class="min-w-0 flex-1">
                    <span class="flex items-center gap-2">
                        <span class="text-lg font-semibold text-gray-900 group-hover:text-brand-700">{{ $tile['title'] }}</span>
                        @isset($tile['badge'])
                            <span @class([
                                'rounded-full px-2.5 py-0.5 text-xs font-medium',
                                'bg-brand-tint text-brand-700' => $tile['badge'] === 'On',
                                'bg-gray-100 text-gray-700' => $tile['badge'] !== 'On',
                            ])>{{ $tile['badge'] }}</span>
                        @endisset
                    </span>
                    <span class="mt-1 block text-sm leading-snug text-gray-600">{{ $tile['description'] }}</span>
                </span>

                <span class="mt-1 text-gray-400 transition-colors group-hover:text-brand-700" aria-hidden="true">&rarr;</span>
            </a>
        @endforeach
    </div>
</x-layouts.app>
