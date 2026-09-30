@php
    $months = [
        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
    ];
@endphp

<x-layouts.app title="Reporting periods">
    <div class="mx-auto max-w-3xl">
        <x-hero :compact="true" :back="route('settings.index', $currentResourceType)" back-label="Settings" eyebrow="Settings" title="Reporting periods"
            :description="'Recurring date windows, e.g. a financial year running 5 April to 4 April, totalled on the dashboard for each '.strtolower($resourceTermSingular).'.'" />

        <section class="mt-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 sm:p-6">
            <h2 class="mb-4 text-base font-semibold text-gray-900">Add period</h2>

            <form method="POST" action="{{ route('settings.periods.store', $currentResourceType) }}" class="space-y-4">
                @csrf

                <x-helper.form.field.text name="name" title="Name" required :value="old('name')" placeholder="e.g. Financial year" />

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid grid-cols-[1fr_6rem] gap-3">
                        <x-helper.form.field.select name="start_month" title="Start month" required :value="1" :options="$months" />
                        <x-helper.form.field.number name="start_day" title="Start day" required min="1" max="31" :value="1" />
                    </div>
                    <div class="grid grid-cols-[1fr_6rem] gap-3">
                        <x-helper.form.field.select name="end_month" title="End month" required :value="12" :options="$months" />
                        <x-helper.form.field.number name="end_day" title="End day" required min="1" max="31" :value="31" />
                    </div>
                </div>

                <div class="flex justify-end">
                    <x-button>Add period</x-button>
                </div>
            </form>
        </section>

        @if ($periods->isEmpty())
            <p class="mt-6 text-sm text-gray-600">No reporting periods yet - add one above.</p>
        @else
            <div class="mt-6 space-y-6">
                @foreach ($periods as $period)
                    <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 sm:p-6">
                        <header class="mb-4 flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h2 class="text-lg font-semibold text-gray-900">{{ $period->name }}</h2>
                                <p class="text-sm text-gray-500">{{ $period->start_day }} {{ $months[$period->start_month] ?? '' }} &rarr; {{ $period->end_day }} {{ $months[$period->end_month] ?? '' }}</p>
                            </div>

                            <form method="POST" action="{{ route('settings.periods.delete', [$currentResourceType, $period]) }}" class="shrink-0" onsubmit="return confirm('Delete this reporting period?');">
                                @csrf
                                <button type="submit" class="cursor-pointer rounded-md px-2 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50 hover:underline">Delete</button>
                            </form>
                        </header>

                        <form method="POST" action="{{ route('settings.periods.update', [$currentResourceType, $period]) }}" class="space-y-4">
                            @csrf

                            <x-helper.form.field.text :name="'periods['.$period->id.'][name]'" title="Name" required
                                :value="$period->name" :errorKey="'periods.'.$period->id.'.name'" />

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="grid grid-cols-[1fr_6rem] gap-3">
                                    <x-helper.form.field.select :name="'periods['.$period->id.'][start_month]'" title="Start month" required
                                        :value="$period->start_month" :options="$months" :errorKey="'periods.'.$period->id.'.start_month'" />
                                    <x-helper.form.field.number :name="'periods['.$period->id.'][start_day]'" title="Start day" required min="1" max="31"
                                        :value="$period->start_day" :errorKey="'periods.'.$period->id.'.start_day'" />
                                </div>
                                <div class="grid grid-cols-[1fr_6rem] gap-3">
                                    <x-helper.form.field.select :name="'periods['.$period->id.'][end_month]'" title="End month" required
                                        :value="$period->end_month" :options="$months" :errorKey="'periods.'.$period->id.'.end_month'" />
                                    <x-helper.form.field.number :name="'periods['.$period->id.'][end_day]'" title="End day" required min="1" max="31"
                                        :value="$period->end_day" :errorKey="'periods.'.$period->id.'.end_day'" />
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <x-button variant="secondary">Save</x-button>
                            </div>
                        </form>
                    </section>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app>
