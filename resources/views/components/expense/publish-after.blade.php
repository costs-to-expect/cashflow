@props(['value' => null])

{{--
    The optional "Publish after" card of the expense forms. The API keeps an expense out of its
    lists and totals until this date; blank means it counts straight away. Collapsed until it has a
    date (or an error, or old() input after a failed submit) so it stays out of the way.
--}}
@php
    $current = old('publish_after', $value);
    $open = filled($current) || $errors->has('publish_after');
@endphp

<details class="group rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 sm:p-6" {{ $open ? 'open' : '' }}>
    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-base font-semibold text-gray-900 [&::-webkit-details-marker]:hidden">
        <span>Publish after <span class="text-sm font-normal text-gray-500">(optional)</span></span>
        <svg class="size-4 shrink-0 text-gray-400 transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" /></svg>
    </summary>

    <div class="mt-4">
        <x-helper.form.field.date name="publish_after" title="Date" :value="$value" class="max-w-48"
            hint="Hidden from lists and totals until this date. Leave empty to count it straight away." />
    </div>
</details>
