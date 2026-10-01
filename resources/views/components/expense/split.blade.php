@props([
    'resources',
    'allocations',
    'defaultSplit' => [],
    'term',
    'always' => false,
    'amounts' => true,
    'heading' => 'Who is it for?',
    'description' => null,
])

{{--
    The "who is it for?" card: one row per resource with its percentage (and, when there's a total on
    the page, its share of it), plus an allocation bar and status. Behaviour lives in
    public/js/*/expense-split.js.

    resources    list of {id, name}
    allocations  list of {resource_id, percentage} to render (the caller resolves old() input)
    defaultSplit list of {resource_id, percentage}; offered as "Use default split" if it covers 2+ resources
    always       no on/off toggle - it is always a split (the default split settings page)
    amounts      show each row's share of #total (needs #total and #currency_id on the page)
--}}
@php
    $colours = \App\Support\ResourceColours::forResources($resources);
    $termLower = strtolower($term);

    $isSplit = $always || count($allocations) > 1;

    // Splitting needs at least two resources to split between.
    $canToggle = ! $always && count($resources) > 1;

    $applicableDefault = collect($defaultSplit)->filter(fn ($allocation) => collect($resources)->contains('id', $allocation['resource_id']))->values();
    $defaultLabel = $applicableDefault->count() >= 2 ? $applicableDefault->pluck('percentage')->implode(' / ') : null;

    $helpSingle = "All of it goes to one {$termLower}. Turn on split to share it by percentage.";
    $helpSplit = $amounts ? "Shared by percentage. Each {$termLower}'s amount updates as you go." : 'Shared by percentage.';
@endphp

<section id="split-section" data-always-split="{{ $always ? 'true' : 'false' }}" class="space-y-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 sm:p-6">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h2 class="text-base font-semibold text-gray-900">{{ $heading }}</h2>

            @if ($canToggle)
                <p id="split-help" class="mt-0.5 text-sm text-gray-500" data-text-single="{{ $helpSingle }}" data-text-split="{{ $helpSplit }}">{{ $isSplit ? $helpSplit : $helpSingle }}</p>
            @elseif ($description)
                <p class="mt-0.5 text-sm text-gray-500">{{ $description }}</p>
            @endif
        </div>

        @if ($canToggle)
            <span class="flex shrink-0 items-center gap-3">
                <label for="split-toggle" class="cursor-pointer text-sm font-medium text-gray-700">Split</label>
                <label class="relative inline-flex cursor-pointer items-center">
                    <input type="checkbox" id="split-toggle" class="peer sr-only" aria-label="Split this expense across more than one {{ $termLower }}" @checked($isSplit)>
                    <span class="h-7 w-12 rounded-full bg-gray-300 transition-colors after:absolute after:left-0.5 after:top-0.5 after:size-6 after:rounded-full after:bg-white after:shadow after:transition-transform peer-checked:bg-brand-700 peer-checked:after:translate-x-5 peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-brand-700"></span>
                </label>
            </span>
        @endif
    </div>

    <div id="split-rows" class="space-y-2"
         data-resources='@json(collect($resources)->map(fn ($resource) => ['id' => (string) $resource['id'], 'name' => $resource['name'], 'dot' => $colours[$resource['id']]['bar']])->values())'
         data-default-split='@json($applicableDefault)'>
        @foreach ($allocations as $index => $allocation)
            <x-expense.allocation-row :index="$index" :resources="$resources" :colours="$colours" :resource-id="$allocation['resource_id']"
                :percentage="$allocation['percentage']" :split="$isSplit" :share="$amounts" :term="$term" />
        @endforeach
    </div>

    @if ($canToggle || $always)
        <div data-split-only class="{{ $isSplit ? '' : 'hidden' }} space-y-3">
            <div id="split-bar" class="flex h-2.5 overflow-hidden rounded-full bg-gray-200"></div>
            <p id="split-status" role="status" class="text-sm font-medium"></p>

            <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm font-medium text-brand-700">
                <button type="button" id="split-even" class="cursor-pointer hover:underline">Split evenly</button>
                @if ($defaultLabel)
                    <button type="button" id="split-default" class="cursor-pointer hover:underline">Use default split ({{ $defaultLabel }})</button>
                @endif
                <button type="button" id="add-allocation" class="cursor-pointer hover:underline">+ Add another {{ $termLower }}</button>
            </div>
        </div>

        <template id="allocation-row-template">
            <x-expense.allocation-row index="__INDEX__" :resources="$resources" :colours="$colours" :resource-id="$resources[0]['id']"
                :split="true" :share="$amounts" :term="$term" />
        </template>
    @endif
</section>
