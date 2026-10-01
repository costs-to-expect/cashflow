<x-layouts.app title="Use categories">
    @php
        // Heroicons (outline) paths.
        $options = [
            '1' => [
                'title' => 'On',
                'description' => 'Every expense is tagged with a category and a subcategory - both are required.',
                'icon' => 'M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3ZM6 6h.008v.008H6V6Z',
            ],
            '0' => [
                'title' => 'Off',
                'description' => 'No category fields on the expense forms and no category labels in the lists. Anything already tagged is kept.',
                'icon' => 'M9.75 9.75l4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
            ],
        ];

        $selected = (string) old('enabled', $enabled ? '1' : '0');
    @endphp

    <div class="mx-auto max-w-2xl">
        <x-hero :compact="true" :back="route('settings.index', $currentResourceType)" back-label="Settings" eyebrow="Settings" title="Use categories"
            :description="'Whether expenses in '.$currentResourceType->name.' are tagged with a category and subcategory.'" />

        <form method="POST" action="{{ route('settings.use-categories.action', $currentResourceType) }}" class="mt-6 space-y-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 sm:p-6">
            @csrf

            <fieldset>
                <legend class="text-sm font-medium text-gray-700">Categories</legend>

                <div class="mt-1.5 grid gap-3 sm:grid-cols-2">
                    @foreach ($options as $value => $option)
                        <label class="group relative flex cursor-pointer items-start gap-3 rounded-2xl bg-white p-4 ring-1 ring-gray-300 transition hover:ring-brand-700 has-checked:bg-brand-tint has-checked:ring-2 has-checked:ring-brand-700 has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-brand-700">
                            <input type="radio" name="enabled" value="{{ $value }}" class="sr-only" @checked($selected === (string) $value)>

                            <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-gray-100 text-gray-500 transition-colors group-has-checked:bg-brand-700 group-has-checked:text-white">
                                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $option['icon'] }}" /></svg>
                            </span>

                            <span class="min-w-0 flex-1">
                                <span class="block font-semibold text-gray-900">{{ $option['title'] }}</span>
                                <span class="mt-0.5 block text-sm leading-snug text-gray-600">{{ $option['description'] }}</span>
                            </span>

                            <span class="mt-0.5 grid size-5 shrink-0 place-items-center rounded-full text-white ring-1 ring-gray-300 transition-colors group-has-checked:bg-brand-700 group-has-checked:ring-brand-700" aria-hidden="true">
                                <svg class="size-3 opacity-0 group-has-checked:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            </span>
                        </label>
                    @endforeach
                </div>

                @error('enabled')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror

                @if ($enabled)
                    <p class="mt-3 text-xs text-gray-500">Add and update the categories themselves under <a href="{{ route('settings.categories', $currentResourceType) }}" class="text-brand-700 hover:underline">Categories</a>.</p>
                @endif
            </fieldset>

            <div class="flex flex-col-reverse gap-2 border-t border-gray-100 pt-5 sm:flex-row sm:justify-end">
                <x-button variant="secondary" :href="route('settings.index', $currentResourceType)">Cancel</x-button>
                <x-button>Save</x-button>
            </div>
        </form>
    </div>
</x-layouts.app>
