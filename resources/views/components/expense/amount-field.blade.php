@props(['currencies', 'currencyId' => null, 'total' => null])

{{--
    The big currency + amount input that sits in an expense form's hero. Ids are #currency_id and
    #total, which public/js/*/expense-split.js and format-number.js look for. Errors show beneath it.
--}}
<div>
    <div class="flex items-end gap-3">
        <select id="currency_id" name="currency_id" required aria-label="Currency"
                class="h-12 shrink-0 cursor-pointer rounded-xl border-0 bg-white/15 pl-3 text-base font-semibold text-white ring-1 ring-white/30 focus:outline-none focus:ring-2 focus:ring-white [&>option]:text-gray-900">
            @foreach ($currencies as $currency)
                <option value="{{ $currency['id'] }}" @selected((string) old('currency_id', $currencyId) === (string) $currency['id'])>{{ $currency['code'] }}</option>
            @endforeach
        </select>

        <input id="total" name="total" type="number" inputmode="decimal" step="0.01" min="0" required
               value="{{ old('total', $total) }}" placeholder="0.00" aria-label="Total amount" data-format="number" data-points="2"
               class="w-full min-w-0 appearance-none border-0 border-b-2 {{ $errors->has('total') ? 'border-red-300' : 'border-white/30' }} bg-transparent pb-1 text-5xl font-bold tracking-tight tabular-nums text-white placeholder:text-white/30 focus:border-white focus:outline-none sm:text-6xl [&::-webkit-inner-spin-button]:appearance-none">
    </div>

    @foreach (['total', 'currency_id'] as $field)
        @error($field)
            <p class="mt-2 text-sm text-red-200">{{ $message }}</p>
        @enderror
    @endforeach
</div>
