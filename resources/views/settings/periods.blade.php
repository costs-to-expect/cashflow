@php
    $months = [
        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
    ];
@endphp

<x-layouts.app title="Reporting periods">
    <h1 class="mb-2 text-lg font-semibold text-gray-900">Reporting periods</h1>
    <p class="mb-6 text-sm text-gray-600">
        Recurring date windows - e.g. a financial year running 5 April to 4 April - totalled on the dashboard for each {{ strtolower($resourceTermSingular) }}.
    </p>

    <div class="mb-8 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-sm font-semibold text-gray-900">Add period</h2>
        <form method="POST" action="{{ route('settings.periods.store', $currentResourceType) }}" class="grid items-end gap-4 sm:grid-cols-6">
            @csrf
            <div class="sm:col-span-2">
                <x-helper.form.field.text name="name" title="Name" required :value="old('name')" />
            </div>
            <x-helper.form.field.select name="start_month" title="Start month" required :value="old('start_month', 1)" :options="$months" />
            <x-helper.form.field.number name="start_day" title="Start day" required min="1" max="31" :value="old('start_day', 1)" />
            <x-helper.form.field.select name="end_month" title="End month" required :value="old('end_month', 12)" :options="$months" />
            <x-helper.form.field.number name="end_day" title="End day" required min="1" max="31" :value="old('end_day', 31)" />
            <div class="sm:col-span-6">
                <x-button>Add period</x-button>
            </div>
        </form>
    </div>

    @if ($periods->isEmpty())
        <p class="text-sm text-gray-600">No reporting periods yet - add one above.</p>
    @else
        <div class="space-y-6">
            @foreach ($periods as $period)
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                    <form method="POST" action="{{ route('settings.periods.update', [$currentResourceType, $period]) }}" class="grid items-end gap-4 sm:grid-cols-6">
                        @csrf
                        <div class="sm:col-span-2">
                            <x-helper.form.field.text :name="'periods['.$period->id.'][name]'" title="Name" required
                                :value="$period->name" :errorKey="'periods.'.$period->id.'.name'" />
                        </div>
                        <x-helper.form.field.select :name="'periods['.$period->id.'][start_month]'" title="Start month" required
                            :value="$period->start_month" :options="$months" :errorKey="'periods.'.$period->id.'.start_month'" />
                        <x-helper.form.field.number :name="'periods['.$period->id.'][start_day]'" title="Start day" required min="1" max="31"
                            :value="$period->start_day" :errorKey="'periods.'.$period->id.'.start_day'" />
                        <x-helper.form.field.select :name="'periods['.$period->id.'][end_month]'" title="End month" required
                            :value="$period->end_month" :options="$months" :errorKey="'periods.'.$period->id.'.end_month'" />
                        <x-helper.form.field.number :name="'periods['.$period->id.'][end_day]'" title="End day" required min="1" max="31"
                            :value="$period->end_day" :errorKey="'periods.'.$period->id.'.end_day'" />
                        <div class="sm:col-span-6">
                            <x-button variant="secondary">Save</x-button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('settings.periods.delete', [$currentResourceType, $period]) }}" class="mt-3" onsubmit="return confirm('Delete this reporting period?');">
                        @csrf
                        <x-button variant="danger">Delete</x-button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.app>
