<x-layouts.app title="Default split">
    <h1 class="mb-2 text-lg font-semibold text-gray-900">Default split</h1>
    <p class="mb-6 text-sm text-gray-600">
        The percentages pre-filled whenever you split an expense across more than one {{ strtolower($resourceTermSingular) }}.
    </p>

    @if (count($resources) === 0)
        <p class="text-sm text-gray-600">You need to <a href="{{ route('resources.create', $currentResourceType) }}" class="text-indigo-600 hover:underline">add a {{ strtolower($resourceTermSingular) }}</a> first.</p>
    @else
        <form method="POST" action="{{ route('settings.default-split.action', $currentResourceType) }}" class="max-w-xl space-y-4 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            @csrf

            @php
                $oldAllocations = old('allocations', count($allocations) > 0 ? $allocations : [['resource_id' => $resources[0]['id'], 'percentage' => 100]]);
            @endphp

            <div id="default-split-allocations" class="space-y-3"
                 data-resources='@json(collect($resources)->map(fn ($resource) => ['id' => $resource['id'], 'name' => $resource['name']]))'
                 data-term-singular="{{ $resourceTermSingular }}">
                @foreach ($oldAllocations as $index => $allocation)
                    <div class="allocation-row grid grid-cols-[1fr_120px_auto] items-end gap-3">
                        <x-helper.form.field.select :name="'allocations['.$index.'][resource_id]'" :title="$resourceTermSingular" required
                            :value="$allocation['resource_id']"
                            :options="collect($resources)->mapWithKeys(fn ($resource) => [$resource['id'] => $resource['name']])"
                            :errorKey="'allocations.'.$index.'.resource_id'" />
                        <x-helper.form.field.number :name="'allocations['.$index.'][percentage]'" title="Percentage" required min="1" max="100"
                            :value="$allocation['percentage']" :errorKey="'allocations.'.$index.'.percentage'" />
                        <button type="button" class="remove-allocation {{ count($oldAllocations) > 1 ? '' : 'hidden' }} pb-2 text-sm text-red-600 hover:underline">Remove</button>
                    </div>
                @endforeach
            </div>

            <button type="button" id="add-default-split-allocation" class="text-sm text-indigo-600 hover:underline">+ Add another {{ strtolower($resourceTermSingular) }}</button>

            <div>
                <x-button>Save default split</x-button>
            </div>
        </form>

        <script src="{{ asset('js/'.$version['js'].'/default-split-form.js') }}" defer></script>
    @endif
</x-layouts.app>
