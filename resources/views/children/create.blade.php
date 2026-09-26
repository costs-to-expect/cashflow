<x-layouts.app :title="'Add '.strtolower($resourceTermSingular)">
    <h1 class="mb-6 text-lg font-semibold text-gray-900">Add {{ strtolower($resourceTermSingular) }}</h1>

    <form method="POST" action="{{ route('children.create.action') }}" class="max-w-md space-y-4 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        @csrf

        <x-helper.form.field.text name="name" title="Name" required />
        <x-helper.form.field.textarea name="description" title="Description" required />

        <x-button>Add {{ strtolower($resourceTermSingular) }}</x-button>
    </form>
</x-layouts.app>
