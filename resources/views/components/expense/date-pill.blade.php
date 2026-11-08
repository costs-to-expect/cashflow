@props(['value' => null])

{{--
    The date input for an expense form's hero, styled as a pill. It's as tall as the currency select
    beside the amount above it, with a calendar icon and a brighter fill, so it isn't missed.
--}}
<div>
    <label class="inline-flex h-12 cursor-pointer items-center gap-3 rounded-full bg-white/15 pl-4 pr-5 text-base ring-1 ring-white/40 transition-colors hover:bg-white/20 focus-within:ring-2 focus-within:ring-white">
        <svg class="size-5 shrink-0 text-white/80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
        <span class="text-white/80">Date</span>
        <input id="effective_date" name="effective_date" type="date" required value="{{ old('effective_date', $value) }}"
               class="cursor-pointer bg-transparent text-base font-semibold text-white [color-scheme:dark] focus:outline-none">
    </label>

    @error('effective_date')
        <p class="mt-2 text-sm text-red-200">{{ $message }}</p>
    @enderror
</div>
