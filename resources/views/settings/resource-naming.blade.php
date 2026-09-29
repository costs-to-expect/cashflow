<x-layouts.app title="Naming">
    <h1 class="mb-2 text-lg font-semibold text-gray-900">Naming</h1>
    <p class="mb-6 text-sm text-gray-600">What a "{{ strtolower($resourceTermSingular) }}" is called throughout the app - e.g. "Child"/"Children", or "Product"/"Products".</p>

    <form method="POST" action="{{ route('settings.resource-naming.action', $currentResourceType) }}" class="max-w-md space-y-4 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        @csrf

        <x-helper.form.field.text name="singular" title="Singular" required :value="old('singular', $singular)" />
        <x-helper.form.field.text name="plural" title="Plural" required :value="old('plural', $plural)" />

        <x-button>Save naming</x-button>
    </form>
</x-layouts.app>
