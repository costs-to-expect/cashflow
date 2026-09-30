@props(['value' => null])

{{-- The date input for an expense form's hero, styled as a pill. --}}
<div>
    <label class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm ring-1 ring-white/30 focus-within:ring-2 focus-within:ring-white">
        <span class="text-white/70">Date</span>
        <input id="effective_date" name="effective_date" type="date" required value="{{ old('effective_date', $value) }}"
               class="bg-transparent text-sm font-medium text-white [color-scheme:dark] focus:outline-none">
    </label>

    @error('effective_date')
        <p class="mt-2 text-sm text-red-200">{{ $message }}</p>
    @enderror
</div>
