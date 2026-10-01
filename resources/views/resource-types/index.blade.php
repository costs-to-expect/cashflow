<x-layouts.app title="Resource types">
    @php
        // Same icons as the new resource type form (Heroicons, outline), keyed by item type.
        $typeIcons = [
            'allocated-expense' => 'M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z',
            'allocated-transaction' => 'M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5',
        ];
    @endphp

    <x-hero title="Resource types" description="Each resource type tracks its own resources, categories, reporting periods and default split. Pick one to open its dashboard.">
        <div class="mt-6">
            <x-button variant="hero" :href="route('resource-types.create')">New resource type</x-button>
        </div>
    </x-hero>

    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        @foreach ($resourceTypes as $resourceType)
            <a href="{{ route('dashboard', $resourceType) }}" class="group flex items-start gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 transition hover:ring-2 hover:ring-brand-700">
                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-brand-tint text-brand-700">
                    @if (isset($typeIcons[$resourceType->item_type]))
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $typeIcons[$resourceType->item_type] }}" /></svg>
                    @endif
                </span>

                <span class="min-w-0 flex-1">
                    <span class="block text-lg font-semibold text-gray-900 group-hover:text-brand-700">{{ $resourceType->name }}</span>
                    <span class="mt-1 inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">{{ $resourceType->itemTypeLabel() }}</span>
                    @if (filled($resourceType->description))
                        <span class="mt-2 line-clamp-2 block text-sm leading-snug text-gray-600">{{ $resourceType->description }}</span>
                    @endif
                </span>

                <span class="mt-1 text-gray-400 transition-colors group-hover:text-brand-700" aria-hidden="true">&rarr;</span>
            </a>
        @endforeach
    </div>
</x-layouts.app>
