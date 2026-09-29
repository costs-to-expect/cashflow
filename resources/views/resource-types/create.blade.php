<x-layouts.app title="New resource type">
    <h1 class="mb-6 text-lg font-semibold text-gray-900">New resource type</h1>

    <form method="POST" action="{{ route('resource-types.store') }}" class="max-w-md space-y-4 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        @csrf

        <x-helper.form.field.text name="name" title="Name" required :value="old('name')" />
        <x-helper.form.field.textarea name="description" title="Description" required :value="old('description')" />
        <x-helper.form.field.select name="item_type" title="Type" required :value="old('item_type', 'allocated-expense')" :options="$itemTypeOptions" />

        <x-button>Create</x-button>
    </form>
</x-layouts.app>
