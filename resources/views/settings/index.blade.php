<x-layouts.app title="Settings">
    <h1 class="mb-6 text-lg font-semibold text-gray-900">Settings</h1>

    <div class="grid gap-4 sm:grid-cols-2">
        <a href="{{ route('settings.default-split') }}" class="block rounded-lg border border-gray-200 bg-white p-4 shadow-sm hover:border-indigo-300">
            <p class="font-medium text-gray-900">Default split</p>
            <p class="mt-1 text-sm text-gray-600">The percentages pre-filled whenever you split an expense.</p>
        </a>

        <a href="{{ route('settings.categories') }}" class="block rounded-lg border border-gray-200 bg-white p-4 shadow-sm hover:border-indigo-300">
            <p class="font-medium text-gray-900">Categories</p>
            <p class="mt-1 text-sm text-gray-600">Add and update the categories and subcategories expenses can be tagged with.</p>
        </a>

        <a href="{{ route('settings.resource-naming') }}" class="block rounded-lg border border-gray-200 bg-white p-4 shadow-sm hover:border-indigo-300">
            <p class="font-medium text-gray-900">Naming</p>
            <p class="mt-1 text-sm text-gray-600">What a "{{ strtolower($resourceTermSingular) }}" is called throughout the app.</p>
        </a>
    </div>
</x-layouts.app>
