<x-layouts.app title="Default split">
    <div class="mx-auto max-w-2xl">
        <x-hero :compact="true" :back="route('settings.index', $currentResourceType)" back-label="Settings" eyebrow="Settings" title="Default split"
            :description="'The percentages pre-filled whenever you split an expense across more than one '.strtolower($resourceTermSingular).'.'" />

        @if (count($resources) === 0)
            <p class="mt-6 text-sm text-gray-600">You need to <a href="{{ route('resources.create', $currentResourceType) }}" class="text-brand-700 hover:underline">add a {{ strtolower($resourceTermSingular) }}</a> first.</p>
        @else
            <form method="POST" action="{{ route('settings.default-split.action', $currentResourceType) }}" class="mt-6 space-y-6">
                @csrf

                <x-expense.split :always="true" :amounts="false" :resources="$resources" :term="$resourceTermSingular"
                    heading="Percentages" :description="'Each '.strtolower($resourceTermSingular).' you include gets a share; together they should add up to 100%.'"
                    :allocations="old('allocations', count($allocations) > 0 ? $allocations : [['resource_id' => $resources[0]['id'], 'percentage' => 100]])" />

                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <x-button variant="secondary" :href="route('settings.index', $currentResourceType)">Cancel</x-button>
                    <x-button>Save default split</x-button>
                </div>
            </form>

            <script src="{{ asset('js/'.$version['js'].'/expense-split.js') }}" defer></script>
        @endif
    </div>
</x-layouts.app>
