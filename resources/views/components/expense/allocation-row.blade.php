@props(['index', 'resources', 'colours', 'resourceId', 'percentage' => null, 'split' => false, 'term' => 'Resource'])

{{--
    One row of the add-expense split: which resource, what percentage, and (filled in by
    public/js/*/expense-split.js) how much that comes to. Rendered both for the rows already on
    the page and, with a placeholder index, as the <template> the script clones for new rows.
    The percentage and remove button are only shown while the expense is split.
--}}
@php
    $resourceKey = 'allocations.'.$index.'.resource_id';
    $percentageKey = 'allocations.'.$index.'.percentage';
@endphp

<div class="allocation-row flex flex-wrap items-center gap-x-3 gap-y-2 rounded-xl bg-gray-50 p-3">
    <div class="flex min-w-0 basis-full items-center gap-2.5 sm:basis-0 sm:flex-1">
        <span class="allocation-dot size-2.5 shrink-0 rounded-full {{ $colours[$resourceId]['bar'] ?? 'bg-gray-400' }}"></span>
        <select name="allocations[{{ $index }}][resource_id]" aria-label="{{ $term }}" required
                class="form-control py-2 pr-10 {{ $errors->has($resourceKey) ? 'form-control-error' : '' }}">
            @foreach ($resources as $resource)
                <option value="{{ $resource['id'] }}" @selected((string) $resource['id'] === (string) $resourceId)>{{ $resource['name'] }}</option>
            @endforeach
        </select>
    </div>

    <label data-split-only class="{{ $split ? '' : 'hidden' }} relative w-24">
        <span class="sr-only">Percentage</span>
        <input type="number" name="allocations[{{ $index }}][percentage]" value="{{ $percentage }}" min="1" max="100" required
               class="form-control py-2 pr-7 text-right {{ $errors->has($percentageKey) ? 'form-control-error' : '' }}">
        <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-sm text-gray-500" aria-hidden="true">%</span>
    </label>

    <span data-share class="ml-auto min-w-24 text-right text-sm font-semibold tabular-nums text-gray-900"></span>

    <button type="button" data-remove data-split-only aria-label="Remove" class="{{ $split ? '' : 'hidden' }} grid size-8 cursor-pointer place-items-center rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-600">
        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
    </button>
</div>
