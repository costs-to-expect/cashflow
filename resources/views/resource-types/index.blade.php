<x-layouts.app title="Resource types">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-lg font-semibold text-gray-900">Resource types</h1>
        <a href="{{ route('resource-types.create') }}"><x-button>New resource type</x-button></a>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        @foreach ($resourceTypes as $resourceType)
            <a href="{{ route('dashboard', $resourceType) }}" class="block rounded-lg border border-gray-200 bg-white p-4 shadow-sm hover:border-indigo-300">
                <p class="font-medium text-gray-900">{{ $resourceType->name }}</p>
                <p class="mt-1 text-sm text-gray-600">{{ $resourceType->itemTypeLabel() }}</p>
            </a>
        @endforeach
    </div>
</x-layouts.app>
