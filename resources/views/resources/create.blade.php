<x-layouts.app :title="'Add '.strtolower($resourceTermSingular)">
    <div class="mx-auto max-w-2xl">
        <x-hero :back="route('dashboard', $currentResourceType)" back-label="Dashboard" :eyebrow="$currentResourceType->name" :title="'Add '.strtolower($resourceTermSingular)"
            :description="'Add another '.strtolower($resourceTermSingular).' to track costs for.'" />

        <form method="POST" action="{{ route('resources.create.action', $currentResourceType) }}" class="mt-6 space-y-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 sm:p-6">
            @csrf

            <x-helper.form.field.text name="name" title="Name" required placeholder="What should it be called?" />
            <x-helper.form.field.textarea name="description" title="Description" required placeholder="What is this for?" hint="A sentence or two to help you remember." />

            <div class="flex flex-col-reverse gap-2 border-t border-gray-100 pt-5 sm:flex-row sm:justify-end">
                <x-button variant="secondary" :href="route('dashboard', $currentResourceType)">Cancel</x-button>
                <x-button>Add {{ strtolower($resourceTermSingular) }}</x-button>
            </div>
        </form>
    </div>
</x-layouts.app>
